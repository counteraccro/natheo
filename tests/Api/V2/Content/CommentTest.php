<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests de la ressource Comment de l'API v2
 */

namespace App\Tests\Api\V2\Content;

use App\Api\V2\Input\Content\ModerateCommentInput;
use App\Api\V2\Resource\Content\Comment as CommentResource;
use App\Entity\Admin\Content\Comment\Comment;
use App\Entity\Admin\System\User;
use App\Enum\Admin\Comment\CommentStatus;
use App\Enum\Admin\Content\Page\PageStatus;
use App\Enum\Admin\System\Options\OptionSystem;
use App\Service\Admin\System\OptionSystemService;
use App\Tests\Api\V2\AppApiV2TestCase;

class CommentTest extends AppApiV2TestCase
{
    /**
     * Liste d'une page : commentaires actifs, contenus non validés masqués pour un visiteur
     * @return void
     */
    public function testGetCollection(): void
    {
        $page = $this->createPageAllDataDefault();
        $validated = $this->createComment($page, customData: ['status' => CommentStatus::VALIDATE->value]);
        $pending = $this->createComment($page, customData: ['status' => CommentStatus::WAIT_VALIDATION->value]);
        $moderated = $this->createComment(
            $page,
            customData: [
                'status' => CommentStatus::MODERATE->value,
                'moderationComment' => 'Hors sujet',
            ],
        );
        $this->createComment($page, customData: ['status' => CommentStatus::VALIDATE->value, 'disabled' => true]);
        $this->createComment(customData: ['status' => CommentStatus::VALIDATE->value]);

        $content = $this->decode($this->requestV2('GET', '/pages/' . $page->getId() . '/comments?order=asc'));
        $this->assertMatchesResourceCollectionJsonSchema(CommentResource::class);
        $this->assertEquals(3, $content['totalItems']);
        $members = array_column($content['member'], null, 'id');
        $this->assertEquals([$validated->getId(), $pending->getId(), $moderated->getId()], array_keys($members));

        $item = $members[$validated->getId()];
        $this->assertEquals('/api/v2/comments/' . $validated->getId(), $item['@id']);
        $this->assertEquals('/api/v2/pages/' . $page->getId(), $item['page']);
        $this->assertEquals('validate', $item['status']);
        $this->assertEquals($validated->getAuthor(), $item['author']);
        $this->assertEquals($validated->getComment(), $item['comment']);
        $this->assertNull($item['moderationComment']);
        $this->assertNotFalse(\DateTimeImmutable::createFromFormat(\DATE_ATOM, $item['createdAt']));
        $this->assertArrayNotHasKey('email', $item);
        $this->assertArrayNotHasKey('ip', $item);

        $item = $members[$pending->getId()];
        $this->assertEquals('wait_validation', $item['status']);
        $this->assertNull($item['author']);
        $this->assertNull($item['comment']);

        $item = $members[$moderated->getId()];
        $this->assertEquals('moderate', $item['status']);
        $this->assertEquals($moderated->getAuthor(), $item['author']);
        $this->assertNull($item['comment']);
        $this->assertNull($item['moderationComment']);

        $content = $this->decode($this->requestV2('GET', '/pages/' . $page->getId() . '/comments?itemsPerPage=2'));
        $this->assertCount(2, $content['member']);
        $this->assertEquals($moderated->getId(), $content['member'][0]['id']);
    }

    /**
     * Liste vue par un utilisateur autorisé à modérer : tous les contenus et le message de modération
     * @return void
     */
    public function testGetCollectionAsModerator(): void
    {
        $page = $this->createPageAllDataDefault();
        $pending = $this->createComment($page, customData: ['status' => CommentStatus::WAIT_VALIDATION->value]);
        $moderated = $this->createComment(
            $page,
            customData: [
                'status' => CommentStatus::MODERATE->value,
                'moderationComment' => 'Hors sujet',
            ],
        );
        $headers = ['HTTP_X_USER_TOKEN' => $this->createUserToken($this->createUserContributeur())];

        $content = $this->decode($this->requestV2('GET', '/pages/' . $page->getId() . '/comments', headers: $headers));
        $members = array_column($content['member'], null, 'id');
        $this->assertEquals($pending->getAuthor(), $members[$pending->getId()]['author']);
        $this->assertEquals($pending->getComment(), $members[$pending->getId()]['comment']);
        $this->assertEquals($moderated->getComment(), $members[$moderated->getId()]['comment']);
        $this->assertEquals('Hors sujet', $members[$moderated->getId()]['moderationComment']);

        // Un utilisateur sans droit de modération voit la liste publique
        $headers = ['HTTP_X_USER_TOKEN' => $this->createUserToken($this->createUser())];
        $content = $this->decode($this->requestV2('GET', '/pages/' . $page->getId() . '/comments', headers: $headers));
        $this->assertNull(array_column($content['member'], null, 'id')[$pending->getId()]['comment']);
    }

    /**
     * Liste : page invisible, tri invalide, token utilisateur invalide, token API absent
     * @return void
     */
    public function testGetCollectionErrors(): void
    {
        $page = $this->createPageAllDataDefault();
        $draft = $this->createPageAllDataDefault();
        $draft->setStatus(PageStatus::DRAFT->value);
        $this->persistAndFlush($draft);
        $path = '/pages/' . $page->getId() . '/comments';

        $this->assertProblem($this->requestV2('GET', '/pages/' . $draft->getId() . '/comments'), 404);
        $this->assertProblem($this->requestV2('GET', '/pages/999999999/comments'), 404);
        $this->assertProblem($this->requestV2('GET', $path . '?order=random'), 422);
        $this->assertProblem($this->requestV2('GET', $path, headers: ['HTTP_X_USER_TOKEN' => 'inconnu']), 403);
        $this->assertProblem($this->requestV2('GET', $path, self::HEADER_NONE), 401);
    }

    /**
     * Commentaire par son id : actif et sur une page publiée uniquement
     * @return void
     */
    public function testGet(): void
    {
        $page = $this->createPageAllDataDefault();
        $comment = $this->createComment($page, customData: ['status' => CommentStatus::VALIDATE->value]);
        $disabled = $this->createComment($page, customData: ['disabled' => true]);
        $draft = $this->createPageAllDataDefault();
        $draft->setStatus(PageStatus::DRAFT->value);
        $onDraft = $this->createComment($draft);

        $content = $this->decode($this->requestV2('GET', '/comments/' . $comment->getId()));
        $this->assertMatchesResourceItemJsonSchema(CommentResource::class);
        $this->assertEquals('Comment', $content['@type']);
        $this->assertEquals($comment->getComment(), $content['comment']);

        $this->assertProblem($this->requestV2('GET', '/comments/' . $disabled->getId()), 404);
        $this->assertProblem($this->requestV2('GET', '/comments/' . $onDraft->getId()), 404);
        $this->assertProblem($this->requestV2('GET', '/comments/999999999'), 404);
    }

    /**
     * Ajout : statut selon la règle de la page, IP et User-Agent de la requête, auteur notifié
     * @return void
     */
    public function testPost(): void
    {
        $page = $this->createPageAllDataDefault();
        $notifications = $page->getUser()->getNotifications()->count();

        $response = $this->requestV2(
            'POST',
            '/comments',
            self::HEADER_WRITE,
            [
                'page' => self::PREFIX . '/pages/' . $page->getId(),
                'author' => '<b>Jean</b>',
                'email' => 'jean@example.com',
                'comment' => 'Un avis **en Markdown** avec 1 < 2',
            ],
            headers: ['HTTP_USER_AGENT' => 'phpunit-agent'],
        );
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertMatchesResourceItemJsonSchema(CommentResource::class, '_api_/comments_post');
        $content = $this->decode($response);
        $this->assertEquals('wait_validation', $content['status']);
        $this->assertNull($content['comment']);

        $this->em->clear();
        $comment = $this->em->getRepository(Comment::class)->find($content['id']);
        $this->assertEquals('Jean', $comment->getAuthor());
        $this->assertEquals('Un avis **en Markdown** avec 1 < 2', $comment->getComment());
        $this->assertEquals('127.0.0.1', $comment->getIp());
        $this->assertEquals('phpunit-agent', $comment->getUserAgent());
        $this->assertEquals($page->getId(), $comment->getPage()->getId());
        $user = $this->em->getRepository(User::class)->find($page->getUser()->getId());
        $this->assertCount($notifications + 1, $user->getNotifications());

        // Page sans validation préalable : commentaire publié, User-Agent du corps prioritaire
        $open = $this->createPageAllDataDefault();
        $open->setRuleComment(CommentStatus::VALIDATE->value);
        $this->persistAndFlush($open);
        $content = $this->decode(
            $this->requestV2('POST', '/comments', self::HEADER_WRITE, [
                'page' => self::PREFIX . '/pages/' . $open->getId(),
                'author' => 'Marie',
                'comment' => 'Publié directement',
                'userAgent' => 'visiteur-agent',
            ]),
        );
        $this->assertEquals('validate', $content['status']);
        $this->assertEquals('Publié directement', $content['comment']);
        $this->assertEquals(
            'visiteur-agent',
            $this->em->getRepository(Comment::class)->find($content['id'])->getUserAgent(),
        );
    }

    /**
     * Ajout refusé : commentaires fermés, page invisible, données invalides, droits du token API
     * @return void
     */
    public function testPostErrors(): void
    {
        $page = $this->createPageAllDataDefault();
        $closed = $this->createPageAllDataDefault();
        $closed->setIsOpenComment(false);
        $draft = $this->createPageAllDataDefault();
        $draft->setStatus(PageStatus::DRAFT->value);
        $this->persistAndFlush($closed);
        $this->persistAndFlush($draft);
        $body = fn(int $id) => ['page' => self::PREFIX . '/pages/' . $id, 'author' => 'Jean', 'comment' => 'Bonjour'];

        $this->assertProblem($this->requestV2('POST', '/comments', self::HEADER_WRITE, $body($closed->getId())), 403);
        $this->assertProblem($this->requestV2('POST', '/comments', self::HEADER_WRITE, $body($draft->getId())), 404);

        $content = $this->assertProblem(
            $this->requestV2('POST', '/comments', self::HEADER_WRITE, [
                'page' => self::PREFIX . '/pages/' . $page->getId(),
                'author' => '',
                'email' => 'pas-un-email',
                'comment' => str_repeat('a', 10001),
            ]),
            422,
        );
        $this->assertEqualsCanonicalizing(
            ['author', 'email', 'comment'],
            array_column($content['violations'], 'propertyPath'),
        );
        $this->assertProblem(
            $this->requestV2('POST', '/comments', self::HEADER_WRITE, ['author' => 'Jean', 'comment' => 'Bonjour']),
            422,
        );

        $this->assertProblem($this->requestV2('POST', '/comments', self::HEADER_READ, $body($page->getId())), 403);
        $this->assertProblem($this->requestV2('POST', '/comments', self::HEADER_NONE, $body($page->getId())), 401);

        $this->container->get(OptionSystemService::class)->saveValueByKee(OptionSystem::OS_OPEN_COMMENT->value, '0');
        $this->assertProblem($this->requestV2('POST', '/comments', self::HEADER_WRITE, $body($page->getId())), 403);
    }

    /**
     * Ajout limité par IP cliente, sans effet sur les autres visiteurs
     * @return void
     */
    public function testPostRateLimit(): void
    {
        $page = $this->createPageAllDataDefault();
        $body = ['page' => self::PREFIX . '/pages/' . $page->getId(), 'author' => 'Jean', 'comment' => 'Bonjour'];
        $ip = fn(string $ip) => ['REMOTE_ADDR' => $ip];

        for ($i = 0; $i < 5; $i++) {
            $this->assertEquals(
                201,
                $this->requestV2(
                    'POST',
                    '/comments',
                    self::HEADER_WRITE,
                    $body,
                    headers: $ip('10.0.0.1'),
                )->getStatusCode(),
            );
        }
        $response = $this->requestV2('POST', '/comments', self::HEADER_WRITE, $body, headers: $ip('10.0.0.1'));
        $this->assertProblem($response, 429);
        $this->assertGreaterThan(0, (int) $response->headers->get('Retry-After'));

        $this->assertEquals(
            201,
            $this->requestV2('POST', '/comments', self::HEADER_WRITE, $body, headers: $ip('10.0.0.2'))->getStatusCode(),
        );
    }

    /**
     * Modération par un utilisateur autorisé : champs absents inchangés, modérateur enregistré
     * @return void
     */
    public function testPatch(): void
    {
        $comment = $this->createComment(
            $this->createPageAllDataDefault(),
            customData: ['status' => CommentStatus::WAIT_VALIDATION->value],
        );
        $moderator = $this->createUserContributeur();
        $headers = ['HTTP_X_USER_TOKEN' => $this->createUserToken($moderator)];
        $path = '/comments/' . $comment->getId();

        $response = $this->requestV2(
            'PATCH',
            $path,
            self::HEADER_WRITE,
            ['status' => 'moderate', 'moderationComment' => '<i>Spam</i>'],
            headers: $headers,
        );
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertMatchesResourceItemJsonSchema(CommentResource::class, '_api_/comments/{id}_patch');
        $content = $this->decode($response);
        $this->assertEquals('moderate', $content['status']);
        $this->assertEquals('Spam', $content['moderationComment']);
        $this->assertEquals($comment->getComment(), $content['comment']);

        $content = $this->decode(
            $this->requestV2(
                'PATCH',
                $path,
                self::HEADER_WRITE,
                ['moderationComment' => 'Publicité'],
                headers: $headers,
            ),
        );
        $this->assertEquals('moderate', $content['status']);
        $this->assertEquals('Publicité', $content['moderationComment']);

        $content = $this->decode(
            $this->requestV2('PATCH', $path, self::HEADER_WRITE, ['status' => 'validate'], headers: $headers),
        );
        $this->assertEquals('validate', $content['status']);
        $this->assertEquals('Publicité', $content['moderationComment']);

        $this->em->clear();
        $saved = $this->em->getRepository(Comment::class)->find($comment->getId());
        $this->assertEquals(CommentStatus::VALIDATE->value, $saved->getStatus());
        $this->assertEquals($moderator->getId(), $saved->getUserModeration()->getId());
    }

    /**
     * Modération refusée : sans utilisateur autorisé, statut invalide, commentaire invisible, token API en lecture
     * @return void
     */
    public function testPatchErrors(): void
    {
        $comment = $this->createComment($this->createPageAllDataDefault());
        $disabled = $this->createComment($this->createPageAllDataDefault(), customData: ['disabled' => true]);
        $path = '/comments/' . $comment->getId();
        $body = ['status' => 'validate'];
        $moderator = ['HTTP_X_USER_TOKEN' => $this->createUserToken($this->createUserContributeur())];

        $this->assertProblem($this->requestV2('PATCH', $path, self::HEADER_WRITE, $body), 403);
        $this->assertProblem($this->requestV2('PATCH', '/comments/999999', self::HEADER_WRITE, $body), 403);
        $this->assertProblem(
            $this->requestV2('PATCH', $path, self::HEADER_WRITE, $body, headers: ['HTTP_X_USER_TOKEN' => 'inconnu']),
            403,
        );
        $user = ['HTTP_X_USER_TOKEN' => $this->createUserToken($this->createUser())];
        $this->assertProblem($this->requestV2('PATCH', $path, self::HEADER_WRITE, $body, headers: $user), 403);

        $content = $this->assertProblem(
            $this->requestV2('PATCH', $path, self::HEADER_WRITE, ['status' => 'publie'], headers: $moderator),
            422,
        );
        $this->assertEquals('status', $content['violations'][0]['propertyPath']);
        $this->assertProblem(
            $this->requestV2(
                'PATCH',
                '/comments/' . $disabled->getId(),
                self::HEADER_WRITE,
                $body,
                headers: $moderator,
            ),
            404,
        );
        $this->assertProblem($this->requestV2('PATCH', $path, self::HEADER_READ, $body, headers: $moderator), 403);

        $this->em->clear();
        $this->assertEquals(
            CommentStatus::WAIT_VALIDATION->value,
            $this->em->getRepository(Comment::class)->find($comment->getId())->getStatus(),
        );
    }

    /**
     * Les statuts documentés dans l'OpenAPI (modération et lecture) suivent l'enum CommentStatus
     * @return void
     */
    public function testStatusSchema(): void
    {
        $factory = $this->container->get('api_platform.metadata.property.metadata_factory');
        foreach ([ModerateCommentInput::class, CommentResource::class] as $class) {
            $this->assertEquals(CommentStatus::getSlugs(), $factory->create($class, 'status')->getSchema()['enum']);
        }
    }
}
