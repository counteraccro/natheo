<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 *
 */

namespace Service\Api\Content;

use App\Dto\Api\Content\Comment\ApiAddCommentDto;
use App\Dto\Api\Content\Comment\ApiCommentByPageDto;
use App\Dto\Api\Content\Comment\ApiModerateCommentDto;
use App\Entity\Admin\Content\Comment\Comment;
use App\Enum\Admin\Comment\CommentStatus;
use App\Enum\Admin\Content\Page\PageStatus;
use App\Enum\Admin\System\Options\OptionSystem;
use App\Service\Admin\System\OptionSystemService;
use App\Service\Api\Content\ApiCommentService;
use App\Tests\AppWebTestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

class ApiCommentServiceTest extends AppWebTestCase
{
    private ApiCommentService $apiCommentService;

    public function setUp(): void
    {
        parent::setUp();
        $this->apiCommentService = $this->container->get(ApiCommentService::class);
    }

    /**
     * Test méthode getCommentByPageIdOrSlug()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetCommentByPageIdOrSlug()
    {
        $translator = $this->container->get(TranslatorInterface::class);
        $page = $this->createPageAllDataDefault();

        for ($i = 0; $i < 4; $i++) {
            $this->createComment($page, customData: ['status' => CommentStatus::VALIDATE->value]);
        }

        $user = $this->createUserContributeur();
        for ($i = 0; $i < 4; $i++) {
            $this->createComment(
                $page,
                $user,
                customData: [
                    'status' => CommentStatus::MODERATE->value,
                    'moderationComment' => self::getFaker()->text(40),
                ],
            );
        }

        for ($i = 0; $i < 4; $i++) {
            $this->createComment($page, customData: ['status' => CommentStatus::WAIT_VALIDATION->value]);
        }

        $user = $this->createUserContributeur();
        // Test ID
        $dto = new ApiCommentByPageDto($page->getId(), '', 'fr', 1, 10, 'id', 'asc', '');
        $result = $this->apiCommentService->getCommentByPageIdOrSlug($dto, $user);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('comments', $result);
        $this->assertArrayHasKey('id', $result['comments'][0]);
        $this->assertArrayHasKey('status', $result['comments'][0]);
        $this->assertArrayHasKey('createdAt', $result['comments'][0]);
        $this->assertArrayHasKey('updateAt', $result['comments'][0]);
        $this->assertArrayHasKey('comment', $result['comments'][0]);
        $this->assertArrayHasKey('current_page', $result);
        $this->assertArrayHasKey('rows', $result);
        $this->assertArrayHasKey('limit', $result);
        $this->assertEquals(1, $result['current_page']);
        $this->assertEquals(12, $result['rows']);
        $this->assertEquals(10, $result['limit']);

        $status1 = $status2 = $status3 = 0;
        foreach ($result['comments'] as $comment) {
            switch ($comment['status']) {
                case CommentStatus::WAIT_VALIDATION->value:
                    $status1++;
                    $this->assertNotEquals(
                        $translator->trans('api_errors.comment.wait.validation', domain: 'api_errors'),
                        $comment['comment'],
                    );
                    break;
                case CommentStatus::MODERATE->value:
                    $status3++;
                    $this->assertNotEquals(
                        $translator->trans('api_errors.comment.moderate', domain: 'api_errors'),
                        $comment['comment'],
                    );
                    $this->assertArrayHasKey('moderate', $comment);
                    break;
                case CommentStatus::VALIDATE->value:
                    $status2++;
                    break;
                default:
                    break;
            }
        }

        $this->assertEquals(2, $status1);
        $this->assertEquals(4, $status2);
        $this->assertEquals(4, $status3);

        $user = $this->createUser();
        // Test slug
        $dto = new ApiCommentByPageDto(
            0,
            $page->getPageTranslationByLocale('fr')->getUrl(),
            'fr',
            1,
            10,
            'id',
            'asc',
            '',
        );
        $result = $this->apiCommentService->getCommentByPageIdOrSlug($dto, $user);
        $status1 = $status2 = $status3 = 0;
        foreach ($result['comments'] as $comment) {
            switch ($comment['status']) {
                case CommentStatus::WAIT_VALIDATION->value:
                    $status1++;
                    $this->assertEquals(
                        $translator->trans('api_errors.comment.wait.validation', domain: 'api_errors'),
                        $comment['comment'],
                    );
                    break;
                case CommentStatus::MODERATE->value:
                    $status3++;
                    $this->assertEquals(
                        $translator->trans('api_errors.comment.moderate', domain: 'api_errors'),
                        $comment['comment'],
                    );
                    $this->assertArrayNotHasKey('moderate', $comment);
                    break;
                case CommentStatus::VALIDATE->value:
                    $status2++;
                    break;
                default:
                    break;
            }
        }

        $this->assertEquals(2, $status1);
        $this->assertEquals(4, $status2);
        $this->assertEquals(4, $status3);
    }

    /**
     * Test méthode addNewComment()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testAddNewComment()
    {
        $page = $this->createPageAllDataDefault();

        $slug = $page->getPageTranslationByLocale('fr')->getUrl();
        $author = self::getFaker()->name();
        $email = self::getFaker()->email();
        $comment = self::getFaker()->text();
        $ip = self::getFaker()->ipv4();
        $userAgent = self::getFaker()->userAgent();
        $dto = new ApiAddCommentDto(0, $slug, 'fr', $author, $email, $comment, $ip, $userAgent);

        /** @var Comment $result */
        $result = $this->apiCommentService->addNewComment($dto);
        $this->assertInstanceOf(Comment::class, $result);
        $this->assertIsInt($result->getId());
        $this->assertEquals($author, $result->getAuthor());
        $this->assertEquals($email, $result->getEmail());
        $this->assertEquals($comment, $result->getComment());
        $this->assertEquals($ip, $result->getIp());
        $this->assertEquals($userAgent, $result->getUserAgent());
        $this->assertEquals($page->getId(), $result->getPage()->getId());
    }

    /**
     * Test méthode moderateComment
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testModerateCommentService()
    {
        $user = $this->createUserContributeur();
        $comment = $this->createComment();

        $moderate = self::getFaker()->text();
        $status = strval(CommentStatus::MODERATE->value);

        $dto = new ApiModerateCommentDto($status, $moderate, self::getFaker()->iosMobileToken());

        $this->apiCommentService->moderateComment($dto, $comment, $user);

        /** @var Comment $commentVerif */
        $commentVerif = $this->apiCommentService->findOneById(Comment::class, $comment->getId());
        $this->assertEquals(CommentStatus::MODERATE->value, $commentVerif->getStatus());
        $this->assertEquals($moderate, $commentVerif->getModerationComment());
        $this->assertEquals($user->getId(), $commentVerif->getUserModeration()->getId());
    }

    /**
     * Test méthode addNewComment() : texte brut conservé, notification échappée
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testAddNewCommentRawTextAndEscapedNotification(): void
    {
        $page = $this->createPageAllDataDefault();
        $translation = $page->getPageTranslationByLocale('fr');
        $translation->setTitre('<img src=x onerror=alert(1)>');
        $this->persistAndFlush($translation);

        $dto = new ApiAddCommentDto(
            0,
            $translation->getUrl(),
            'fr',
            '<b>Tom</b> & "Jerry"',
            self::getFaker()->email(),
            'x<y et `<div>`',
            self::getFaker()->ipv4(),
            self::getFaker()->userAgent(),
        );
        $result = $this->apiCommentService->addNewComment($dto);

        $this->assertEquals('x<y et `<div>`', $result->getComment());
        $this->assertEquals('Tom & "Jerry"', $result->getAuthor());

        $notification = $page->getUser()->getNotifications()->last();
        $parameters = json_decode($notification->getParameters(), true);
        $this->assertEquals('Tom &amp; &quot;Jerry&quot;', $parameters['author']);
        $this->assertEquals('&lt;img src=x onerror=alert(1)&gt;', $parameters['page']);
    }

    /**
     * Test méthode moderateComment() : statut inconnu refusé
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testModerateCommentInvalidStatus(): void
    {
        $user = $this->createUserContributeur();
        $comment = $this->createComment(customData: ['status' => CommentStatus::WAIT_VALIDATION->value]);

        $dto = new ApiModerateCommentDto('99', 'test', self::getFaker()->iosMobileToken());
        try {
            $this->apiCommentService->moderateComment($dto, $comment, $user);
            $this->fail('Le statut 99 aurait dû être refusé');
        } catch (HttpException $e) {
            $this->assertEquals(400, $e->getStatusCode());
        }
        $this->assertEquals(CommentStatus::WAIT_VALIDATION->value, $comment->getStatus());
    }

    /**
     * Test méthode getCommentByPageIdOrSlug() : auteur masqué des commentaires en attente pour les visiteurs
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetCommentByPageAuthorHiddenWhenWaitValidation(): void
    {
        $translator = $this->container->get(TranslatorInterface::class);
        $page = $this->createPageAllDataDefault();
        $comment = $this->createComment($page, customData: ['status' => CommentStatus::WAIT_VALIDATION->value]);

        $dto = new ApiCommentByPageDto($page->getId(), '', 'fr', 1, 10, 'id', 'asc', '');

        $result = $this->apiCommentService->getCommentByPageIdOrSlug($dto);
        $this->assertEquals(
            $translator->trans('api_errors.comment.wait.validation_author', domain: 'api_errors'),
            $result['comments'][0]['author'],
        );

        $result = $this->apiCommentService->getCommentByPageIdOrSlug($dto, $this->createUserContributeur());
        $this->assertEquals($comment->getAuthor(), $result['comments'][0]['author']);
    }

    /**
     * Test méthode isOpenForComments() : réglage de la page et option système
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testIsOpenForComments(): void
    {
        $page = $this->createPageAllDataDefault();
        $closed = $this->createPageAllDataDefault();
        $closed->setIsOpenComment(false);
        $this->persistAndFlush($closed);

        $this->assertTrue($this->apiCommentService->isOpenForComments($page));
        $this->assertFalse($this->apiCommentService->isOpenForComments($closed));

        $this->container->get(OptionSystemService::class)->saveValueByKee(OptionSystem::OS_OPEN_COMMENT->value, '0');
        $this->assertFalse($this->apiCommentService->isOpenForComments($page));
    }

    /**
     * Test méthode createComment() : statut selon la règle de la page et l'option système, auteur notifié
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testCreateComment(): void
    {
        $optionSystemService = $this->container->get(OptionSystemService::class);
        $optionSystemService->saveValueByKee(OptionSystem::OS_NEW_COMMENT_WAIT_VALIDATION->value, '0');

        $page = $this->createPageAllDataDefault();
        $page->setRuleComment(CommentStatus::VALIDATE->value);
        $this->persistAndFlush($page);
        $notifications = $page->getUser()->getNotifications()->count();

        $comment = $this->apiCommentService->createComment(
            $page,
            'fr',
            '<b>Jean</b>',
            'jean@natheo.fr',
            'Texte **brut** avec 1 < 2',
            '10.0.0.1',
            'phpunit-agent',
        );
        $this->assertIsInt($comment->getId());
        $this->assertEquals(CommentStatus::VALIDATE->value, $comment->getStatus());
        $this->assertEquals('Jean', $comment->getAuthor());
        $this->assertEquals('Texte **brut** avec 1 < 2', $comment->getComment());
        $this->assertEquals('10.0.0.1', $comment->getIp());
        $this->assertEquals('phpunit-agent', $comment->getUserAgent());
        $this->assertFalse($comment->isDisabled());
        $this->assertCount($notifications + 1, $page->getUser()->getNotifications());

        $page->setRuleComment(CommentStatus::MODERATE->value);
        $this->persistAndFlush($page);
        $comment = $this->apiCommentService->createComment($page, 'fr', 'Jean', null, 'Texte', '', '');
        $this->assertEquals(CommentStatus::WAIT_VALIDATION->value, $comment->getStatus());

        $page->setRuleComment(CommentStatus::VALIDATE->value);
        $this->persistAndFlush($page);
        $optionSystemService->saveValueByKee(OptionSystem::OS_NEW_COMMENT_WAIT_VALIDATION->value, '1');
        $comment = $this->apiCommentService->createComment($page, 'fr', 'Jean', null, 'Texte', '', '');
        $this->assertEquals(CommentStatus::WAIT_VALIDATION->value, $comment->getStatus());
    }

    /**
     * Test méthode canModerate()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testCanModerate(): void
    {
        $this->assertFalse($this->apiCommentService->canModerate(null));
        $this->assertFalse($this->apiCommentService->canModerate($this->createUser()));
        $this->assertTrue($this->apiCommentService->canModerate($this->createUserContributeur()));
    }

    /**
     * Test méthode findPageComments() : commentaires actifs de la page, paginés et triés
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testFindPageComments(): void
    {
        $page = $this->createPageAllDataDefault();
        $first = $this->createComment($page);
        $second = $this->createComment($page);
        $third = $this->createComment($page);
        $first->setCreatedAt(new \DateTime('-3 days'));
        $second->setCreatedAt(new \DateTime('-2 days'));
        $third->setCreatedAt(new \DateTime('-1 day'));
        $this->persistAndFlush($first);
        $this->persistAndFlush($second);
        $this->persistAndFlush($third);
        $this->createComment($page, customData: ['disabled' => true]);
        $this->createComment();

        $ids = fn(iterable $comments) => array_map(fn(Comment $c) => $c->getId(), iterator_to_array($comments));

        $result = $this->apiCommentService->findPageComments($page, 1, 10, \SortDirection::Ascending);
        $this->assertCount(3, $result);
        $this->assertEquals([$first->getId(), $second->getId(), $third->getId()], $ids($result));

        $result = $this->apiCommentService->findPageComments($page, 1, 2, \SortDirection::Descending);
        $this->assertCount(3, $result);
        $this->assertEquals([$third->getId(), $second->getId()], $ids($result));

        $result = $this->apiCommentService->findPageComments($page, 2, 2, \SortDirection::Descending);
        $this->assertEquals([$first->getId()], $ids($result));
    }

    /**
     * Test méthode findVisibleComment() : commentaire actif d'une page publiée uniquement
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testFindVisibleComment(): void
    {
        $page = $this->createPageAllDataDefault();
        $comment = $this->createComment($page);
        $disabled = $this->createComment($page, customData: ['disabled' => true]);

        $draft = $this->createPageAllDataDefault();
        $draft->setStatus(PageStatus::DRAFT->value);
        $this->persistAndFlush($draft);
        $onDraft = $this->createComment($draft);

        $disabledPage = $this->createPageAllDataDefault();
        $disabledPage->setDisabled(true);
        $this->persistAndFlush($disabledPage);
        $onDisabledPage = $this->createComment($disabledPage);

        $this->assertEquals(
            $comment->getId(),
            $this->apiCommentService->findVisibleComment($comment->getId())?->getId(),
        );
        $this->assertNull($this->apiCommentService->findVisibleComment($disabled->getId()));
        $this->assertNull($this->apiCommentService->findVisibleComment($onDraft->getId()));
        $this->assertNull($this->apiCommentService->findVisibleComment($onDisabledPage->getId()));
        $this->assertNull($this->apiCommentService->findVisibleComment(999999999));
    }

    /**
     * Test méthode applyModeration() : statut, message sans balise et modérateur enregistrés
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testApplyModeration(): void
    {
        $user = $this->createUserContributeur();
        $comment = $this->createComment(customData: ['status' => CommentStatus::WAIT_VALIDATION->value]);

        $this->apiCommentService->applyModeration($comment, CommentStatus::MODERATE, '<i>Spam</i>', $user);

        /** @var Comment $saved */
        $saved = $this->apiCommentService->findOneById(Comment::class, $comment->getId());
        $this->assertEquals(CommentStatus::MODERATE->value, $saved->getStatus());
        $this->assertEquals('Spam', $saved->getModerationComment());
        $this->assertEquals($user->getId(), $saved->getUserModeration()->getId());
    }
}
