<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test du controller ApiCommentController
 */

namespace Controller\Api\v1\Content;

use App\Entity\Admin\Content\Comment\Comment;
use App\Tests\Controller\Api\AppApiTestCase;
use App\Utils\Content\Comment\CommentConst;
use Symfony\Contracts\Translation\TranslatorInterface;

class ApiCommentControllerTest extends AppApiTestCase
{
    /**
     * Test méthode getCommentsByPage()
     * @return void
     */
    public function testGetCommentsByPage(): void
    {

        $translator = $this->container->get(TranslatorInterface::class);

        $page = $this->createPageAllDataDefault();
        for ($i = 0; $i < 3; $i++) {
            $this->createComment($page, customData: ['status' => CommentConst::VALIDATE]);
        }

        $this->createComment($page, customData: ['status' => CommentConst::WAIT_VALIDATION]);
        $comment = $this->createComment($page, customData: ['status' => CommentConst::MODERATE, 'moderationComment' => self::getFaker()->text(40)]);

        // Erreur id et slug non présent
        $this->client->request('GET', $this->router->generate('api_comment_by_page', ['api_version' => self::API_VERSION]),
            server: $this->getCustomHeaders(self::HEADER_READ)
        );
        $response = $this->client->getResponse();;
        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->checkStructureApiRetourError($content);
        $this->assertEquals($translator->trans('api_errors.comment.by.page.not.id.slug.together', domain: 'api_errors'), $content['errors'][0]);

        // Erreur id et slug présent
        $this->client->request('GET', $this->router->generate('api_comment_by_page', ['api_version' => self::API_VERSION, 'id' => $page->getId(), 'page_slug' => $page->getPageTranslationByLocale('fr')->getUrl()]),
            server: $this->getCustomHeaders(self::HEADER_READ)
        );
        $response = $this->client->getResponse();;
        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->checkStructureApiRetourError($content);
        $this->assertEquals($translator->trans('api_errors.comment.by.page.id.slug.together', domain: 'api_errors'), $content['errors'][0]);


        $this->client->request('GET', $this->router->generate('api_comment_by_page', ['api_version' => self::API_VERSION, 'page_slug' => $page->getPageTranslationByLocale('fr')->getUrl(), 'limit' => 5, 'order_by' => 'toto']),
            server: array_merge($this->getCustomHeaders(self::HEADER_READ), ['HTTP_User-token' => $this->authUser()])
        );
        $response = $this->client->getResponse();;
        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->checkStructureApiRetourError($content);
        $this->assertEquals('__Choose a orderBy between id or createdAt ', $content['errors'][0]);

        $this->client->request('GET', $this->router->generate('api_comment_by_page', ['api_version' => self::API_VERSION, 'page_slug' => $page->getPageTranslationByLocale('fr')->getUrl(), 'limit' => 5, 'order_by' => 'id', 'order' => 'desc']),
            server: array_merge($this->getCustomHeaders(self::HEADER_READ), ['HTTP_User-token' => $this->authUser()])
        );
        $response = $this->client->getResponse();;
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->checkStructureApiRetour($content);

        $verif = $content['data']['comments'][0];
        $this->assertIsArray($verif);
        $this->assertArrayHasKey('id', $verif);
        $this->assertEquals($comment->getId(), $verif['id']);
        $this->assertArrayHasKey('status', $verif);
        $this->assertEquals($comment->getStatus(), $verif['status']);
        $this->assertArrayHasKey('createdAt', $verif);
        $this->assertEquals($comment->getCreatedAt()->getTimestamp(), $verif['createdAt']);
        $this->assertArrayHasKey('updateAt', $verif);
        $this->assertEquals($comment->getUpdateAt()->getTimestamp(), $verif['updateAt']);
        $this->assertArrayHasKey('comment', $verif);
        $this->assertEquals($comment->getComment(), $verif['comment']);
        $this->assertArrayHasKey('moderate', $verif);
        $this->assertEquals($comment->getModerationComment(), $verif['moderate']);
    }

    /**
     * Test méthode add()
     * @return void
     */
    public function testAddComment()
    {
        $translator = $this->container->get(TranslatorInterface::class);

        // page id et slug together
        $this->client->request('POST', $this->router->generate('api_comment_add_comment', ['api_version' => self::API_VERSION]),
            server: $this->getCustomHeaders(),
            content: json_encode([
                'page_id' => '1',
                'page_slug' => 'toto',
                'author' => '',
                'email' => 'azerty@gmail.com',
                'comment' => 'test comment',
                'ip' => '127.0.0.1',
                'user_agent' => self::getFaker()->userAgent()
            ])
        );

        $response = $this->client->getResponse();;
        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->checkStructureApiRetourError($content);
        $this->assertEquals($translator->trans('api_errors.comment.add.id.slug.together', domain: 'api_errors'), $content['errors'][0]);

        // page id et slug n'existe pas
        $this->client->request('POST', $this->router->generate('api_comment_add_comment', ['api_version' => self::API_VERSION]),
            server: $this->getCustomHeaders(),
            content: json_encode([
                'author' => '',
                'email' => 'azerty@gmail.com',
                'comment' => 'test comment',
                'ip' => '127.0.0.1',
                'user_agent' => self::getFaker()->userAgent()
            ])
        );

        $response = $this->client->getResponse();;
        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->checkStructureApiRetourError($content);
        $this->assertEquals($translator->trans('api_errors.comment.add.not.id.slug.together', domain: 'api_errors'), $content['errors'][0]);

        // Auteur vide
        $this->client->request('POST', $this->router->generate('api_comment_add_comment', ['api_version' => self::API_VERSION]),
            server: $this->getCustomHeaders(),
            content: json_encode([
                'page_id' => '1',
                'author' => '',
                'email' => 'azerty@gmail.com',
                'comment' => 'test comment',
                'ip' => '127.0.0.1',
                'user_agent' => self::getFaker()->userAgent()
            ])
        );

        $response = $this->client->getResponse();;
        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->checkStructureApiRetourError($content);
        $this->assertEquals('__The author parameter cannot be empty ', $content['errors'][0]);

        // Page id wrong
        $this->client->request('POST', $this->router->generate('api_comment_add_comment', ['api_version' => self::API_VERSION]),
            server: $this->getCustomHeaders(),
            content: json_encode([
                'page_id' => '1',
                'page_slug' => '',
                'author' => 'azerty',
                'email' => 'azerty@gmail.com',
                'comment' => 'test comment',
                'ip' => '127.0.0.1',
                'user_agent' => self::getFaker()->userAgent()
            ])
        );
        $response = $this->client->getResponse();;
        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->checkStructureApiRetourError($content);
        $this->assertEquals($translator->trans('api_errors.find.page.not.found', domain: 'api_errors'), $content['errors'][0]);

        // Page slug wrong
        $this->client->request('POST', $this->router->generate('api_comment_add_comment', ['api_version' => self::API_VERSION]),
            server: $this->getCustomHeaders(),
            content: json_encode([
                'page_id' => '',
                'page_slug' => 'azerty-slug',
                'author' => 'azerty',
                'email' => 'azerty@gmail.com',
                'comment' => 'test comment',
                'ip' => '127.0.0.1',
                'user_agent' => self::getFaker()->userAgent()
            ])
        );
        $response = $this->client->getResponse();;
        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->checkStructureApiRetourError($content);
        $this->assertEquals($translator->trans('api_errors.find.page.not.found', domain: 'api_errors'), $content['errors'][0]);

        // Comment close
        $page = $this->createPage(customData: ['isOpenComment' => false, 'disabled' => false]);
        $this->client->request('POST', $this->router->generate('api_comment_add_comment', ['api_version' => self::API_VERSION]),
            server: $this->getCustomHeaders(),
            content: json_encode([
                'page_id' => $page->getId(),
                'page_slug' => '',
                'author' => 'azerty',
                'email' => 'azerty@gmail.com',
                'comment' => 'test comment',
                'ip' => '127.0.0.1',
                'user_agent' => self::getFaker()->userAgent()
            ])
        );
        $response = $this->client->getResponse();;
        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->checkStructureApiRetourError($content);
        $this->assertEquals($translator->trans('api_errors.comment.not.open', domain: 'api_errors'), $content['errors'][0]);

        // page id
        $page = $this->createPage(customData: ['isOpenComment' => true, 'disabled' => false]);
        $this->client->request('POST', $this->router->generate('api_comment_add_comment', ['api_version' => self::API_VERSION]),
            server: $this->getCustomHeaders(),
            content: json_encode([
                'page_id' => $page->getId(),
                'page_slug' => '',
                'author' => 'azerty',
                'email' => 'azerty@gmail.com',
                'comment' => 'test comment',
                'ip' => '127.0.0.1',
                'user_agent' => self::getFaker()->userAgent()
            ])
        );
        $response = $this->client->getResponse();;
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->checkStructureApiRetour($content);

        $commentRepo = $this->em->getRepository(Comment::class);
        $verif = $commentRepo->findOneBy(['id' => $content['data']['id']]);
        $this->assertInstanceOf(Comment::class, $verif);
        $this->assertEquals('azerty', $verif->getAuthor());

        // page slug
        $page = $this->createPageAllDataDefault();
        $this->client->request('POST', $this->router->generate('api_comment_add_comment', ['api_version' => self::API_VERSION]),
            server: $this->getCustomHeaders(),
            content: json_encode([
                'page_id' => '',
                'page_slug' => $page->getPageTranslationByLocale('fr')->getUrl(),
                'author' => 'azerty2',
                'email' => 'azerty@gmail.com',
                'comment' => 'test comment',
                'ip' => '127.0.0.1',
                'user_agent' => self::getFaker()->userAgent()
            ])
        );
        $response = $this->client->getResponse();;
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->checkStructureApiRetour($content);

        $commentRepo = $this->em->getRepository(Comment::class);
        $verif = $commentRepo->findOneBy(['id' => $content['data']['id']]);
        $this->assertInstanceOf(Comment::class, $verif);
        $this->assertEquals('azerty2', $verif->getAuthor());
    }

    /**
     * Test méthode moderateComment()
     * @return void
     */
    public function testModerateComment()
    {
        $translator = $this->container->get(TranslatorInterface::class);
        $comment = $this->createComment();

        /* Status invalide */
        $this->client->request('PUT', $this->router->generate('api_comment_moderate_comment', ['api_version' => self::API_VERSION, 'id' => $comment->getId()]),
            server: array_merge($this->getCustomHeaders(self::HEADER_READ), ['HTTP_User-token' => $this->authUser()]),
            content: json_encode([
                'status' => 100,
                'moderation_comment' => self::getFaker()->text(),
            ])
        );
        $response = $this->client->getResponse();;
        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->checkStructureApiRetourError($content);
        $this->assertEquals($translator->trans('api_errors.comment.status.no.valid', domain: 'api_errors'), $content['errors'][0]);

        /* User invalide */
        $this->client->request('PUT', $this->router->generate('api_comment_moderate_comment', ['api_version' => self::API_VERSION, 'id' => $comment->getId()]),
            server: array_merge($this->getCustomHeaders(self::HEADER_READ), ['HTTP_User-token' => self::getFaker()->randomKey()]),
            content: json_encode([
                'status' => CommentConst::MODERATE,
                'moderation_comment' => self::getFaker()->text(),
            ])
        );
        $response = $this->client->getResponse();;
        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->checkStructureApiRetourError($content);
        $this->assertEquals($translator->trans('api_errors.user.token.not.found', domain: 'api_errors'), $content['errors'][0]);

        // No user
        $this->client->request('PUT', $this->router->generate('api_comment_moderate_comment', ['api_version' => self::API_VERSION, 'id' => $comment->getId()]),
            server:$this->getCustomHeaders(self::HEADER_READ),
            content: json_encode([
                'status' => CommentConst::MODERATE,
                'moderation_comment' => self::getFaker()->text(),
            ])
        );
        $response = $this->client->getResponse();;
        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->checkStructureApiRetourError($content);
        $this->assertEquals($translator->trans('api_errors.user.token.not.found', domain: 'api_errors'), $content['errors'][0]);


        /* Bad comment invalide */
        $this->client->request('PUT', $this->router->generate('api_comment_moderate_comment', ['api_version' => self::API_VERSION, 'id' => 1]),
            server: array_merge($this->getCustomHeaders(self::HEADER_READ), ['HTTP_User-token' => self::getFaker()->randomKey()]),
            content: json_encode([
                'status' => CommentConst::MODERATE,
                'moderation_comment' => self::getFaker()->text(),
            ])
        );
        $response = $this->client->getResponse();;
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->checkStructureApiRetourError($content);
        $this->assertEquals('Commentaire non disponible', $content['errors'][0]);

        // All is good
        $token = $this->authUser();
        $this->client->request('PUT', $this->router->generate('api_comment_moderate_comment', ['api_version' => self::API_VERSION, 'id' => $comment->getId()]),
            server: array_merge($this->getCustomHeaders(self::HEADER_READ), ['HTTP_User-token' => $token]),
            content: json_encode([
                'status' => CommentConst::MODERATE,
                'moderation_comment' => self::getFaker()->text(),
            ])
        );
        $response = $this->client->getResponse();;
        $this->assertEquals(202, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->checkStructureApiRetour($content);
        $this->assertEquals($translator->trans('api_errors.comment.moderate', domain: 'api_errors'), $content['data'][0]);
    }

    /**
     * Authentifie un compte Admin et retourne le token
     * @return string
     */
    private function authUser(): string
    {
        // Admin
        $password = self::getFaker()->password();
        $this->client->request('POST', $this->router->generate('api_authentication_auth_user', ['api_version' => self::API_VERSION]),
            server: $this->getCustomHeaders(),
            content: json_encode($this->getUserAuthParams([], $this->createUserAdmin(['password' => $password, 'disabled' => false, 'anonymous' => false]), $password))
        );
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->checkStructureApiRetour($content);


        return $content['data']['token'];
    }
}