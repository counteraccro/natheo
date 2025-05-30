<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test du controller ApiCommentController
 */

namespace Controller\Api\v1\Content;

use App\Tests\Controller\Api\AppApiTestCase;
use App\Utils\Content\Comment\CommentConst;
use Symfony\Component\Validator\Constraints\Date;
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
        $this->assertEquals('Choose a orderBy between id or createdAt ', $content['errors'][0]);

        $this->client->request('GET', $this->router->generate('api_comment_by_page', ['api_version' => self::API_VERSION, 'page_slug' => $page->getPageTranslationByLocale('fr')->getUrl(), 'limit' => 5, 'order_by' => 'id', 'order' => 'desc']),
            server: array_merge($this->getCustomHeaders(self::HEADER_READ), ['HTTP_User-token' => $this->authUser()])
        );
        $response = $this->client->getResponse();;
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->checkStructureApiRetour($content);

        $verif = $content['data'][0];
        $this->assertIsArray($verif);
        $this->assertArrayHasKey('id', $verif);
        $this->assertEquals($comment->getId(), $verif['id']);
        $this->assertArrayHasKey('status', $verif);
        $this->assertEquals($comment->getStatus(), $verif['status']);
        $this->assertArrayHasKey('createdAt', $verif);
        $this->assertEquals($comment->getCreatedAt()->format(DATE_ATOM), $verif['createdAt']);
        $this->assertArrayHasKey('updateAt', $verif);
        $this->assertEquals($comment->getUpdateAt()->format(DATE_ATOM), $verif['updateAt']);
        $this->assertArrayHasKey('comment', $verif);
        $this->assertEquals($comment->getComment(), $verif['comment']);
        $this->assertArrayHasKey('moderate', $verif);
        $this->assertEquals($comment->getModerationComment(), $verif['moderate']);
    }

    /**
     * Authentifie un compte Admin et retourne le token
     * @return string
     */
    private function authUser() :string
    {
        // Admin
        $password = self::getFaker()->password();
        $this->client->request('POST', $this->router->generate('api_authentication_auth_user', ['api_version' => self::API_VERSION]),
            server:  $this->getCustomHeaders(),
            content:  json_encode($this->getUserAuthParams([], $this->createUserAdmin(['password' => $password, 'disabled' => false, 'anonymous' => false]), $password))
        );
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->checkStructureApiRetour($content);

        return $content['data']['token'];
    }
}