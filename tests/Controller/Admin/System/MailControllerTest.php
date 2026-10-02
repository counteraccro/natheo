<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * test controller mail
 */

namespace App\Tests\Controller\Admin\System;

use App\Entity\Admin\System\Mail;
use App\Repository\Admin\System\MailRepository;
use App\Service\Admin\System\MailService;
use App\Tests\AppWebTestCase;
use App\Utils\System\Mail\MailKey;
use Symfony\Component\HttpFoundation\Response;

class MailControllerTest extends AppWebTestCase
{
    /**
     * Test index
     * @return void
     */
    public function testIndex()
    {
        $this->checkNoAccess('admin_mail_index');

        $userSuperAdm = $this->createUserSuperAdmin();

        $this->client->loginUser($userSuperAdm, 'admin');
        $this->client->request('GET', $this->router->generate('admin_mail_index'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $this->translator->trans('mail.page_title_h1', domain: 'mail'));
    }

    /**
     * Test chargement des données du grid
     * @return void
     */
    public function testLoadGridData()
    {
        $this->generateDefaultMails();

        $this->checkNoAccess('admin_mail_load_grid_data');
        $userSuperAdm = $this->createUserSuperAdmin();
        $this->client->loginUser($userSuperAdm, 'admin');
        $this->client->request(
            'GET',
            $this->router->generate('admin_mail_load_grid_data', ['page' => 1, 'limit' => 5]),
        );
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);

        $this->assertEquals(8, $content['nb']);
        $this->assertCount(5, $content['data']);
    }

    /**
     * Test edit
     * @return void
     */
    public function testEdit()
    {
        $mail = $this->createMail();
        $this->createMailTranslation($mail, ['locale' => 'fr']);

        $this->checkNoAccess('admin_mail_edit', ['id' => $mail->getId()]);

        $userSuperAdm = $this->createUserSuperAdmin();

        $this->client->loginUser($userSuperAdm, 'admin');
        $this->client->request('GET', $this->router->generate('admin_mail_edit', ['id' => $mail->getId()]));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $this->translator->trans('mail.edit_page_title_h1', domain: 'mail'));
    }

    /**
     * Test edit sur un email inexistant
     * @return void
     */
    public function testEditNotFound()
    {
        $userSuperAdm = $this->createUserSuperAdmin();
        $this->client->loginUser($userSuperAdm, 'admin');
        $this->client->request('GET', $this->router->generate('admin_mail_edit', ['id' => 999999]));
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString(
            $this->translator->trans('mail.edit_page.no.mail.title', domain: 'mail'),
            $this->client->getResponse()->getContent(),
        );
    }

    /**
     * Charge un email en fonction de son id
     * @return void
     */
    public function testLoadData()
    {
        $mail = $this->createMail();
        $this->createMailTranslation($mail, ['locale' => 'fr']);

        $this->checkNoAccess('admin_mail_load_data', ['id' => $mail->getId(), 'locale' => 'fr']);

        $userSuperAdm = $this->createUserSuperAdmin();

        $this->client->loginUser($userSuperAdm, 'admin');
        $this->client->request(
            'GET',
            $this->router->generate('admin_mail_load_data', ['id' => $mail->getId(), 'locale' => 'fr']),
        );

        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('translateEditor', $content);
        $this->assertArrayHasKey('mail', $content);
        $this->assertNotEmpty($content['mail']);
        $this->assertEquals('fr', $content['locale']);

        // Aucune traduction "en" pour cet email
        $this->client->request(
            'GET',
            $this->router->generate('admin_mail_load_data', ['id' => $mail->getId(), 'locale' => 'en']),
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertFalse($content['success']);
    }

    /**
     * Test sauvegarde un email
     * @return void
     */
    public function testSave()
    {
        $mail = $this->createMail();
        $this->createMailTranslation($mail, ['locale' => 'fr']);

        $contentMail = self::getFaker()->text();
        $title = self::getFaker()->text();

        $parameters = [
            'content' => $contentMail,
            'locale' => 'fr',
            'title' => $title,
        ];

        $this->checkNoAccess('admin_mail_save', ['id' => $mail->getId()], 'POST');

        $userSuperAdm = $this->createUserSuperAdmin();
        $this->client->loginUser($userSuperAdm, 'admin');
        $url = $this->router->generate('admin_mail_save', ['id' => $mail->getId()]);

        // Jeton CSRF invalide
        $this->client->request(
            'POST',
            $url,
            server: ['HTTP_X-CSRF-TOKEN' => 'jeton-invalide'],
            content: json_encode($parameters),
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        $server = ['HTTP_X-CSRF-TOKEN' => $this->getCsrfTokens($mail)['csrf_token_save']];

        // Données invalides : titre vide puis locale sans traduction
        $this->client->request('POST', $url, server: $server, content: json_encode([...$parameters, 'title' => '']));
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->client->request('POST', $url, server: $server, content: json_encode([...$parameters, 'locale' => 'en']));
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->client->request('POST', $url, server: $server, content: 'pas du json');
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $this->client->request('POST', $url, server: $server, content: json_encode($parameters));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->assertTrue($content['success']);

        /** @var MailRepository $mailRepository */
        $mailRepository = $this->em->getRepository(Mail::class);

        /** @var Mail $mail */
        $mail = $mailRepository->find($mail->getId());

        $this->assertNotNull($mail);
        $this->assertEquals($contentMail, $mail->getMailTranslationByLocale('fr')->getContent());
        $this->assertEquals($title, $mail->getMailTranslationByLocale('fr')->getTitle());
    }

    /**
     * Test envoi email démo
     * @return void
     */
    public function testSendDemoMail()
    {
        $this->generateDefaultMails();

        /** @var MailRepository $mailRepository */
        $mailRepository = $this->em->getRepository(Mail::class);

        /** @var Mail $mail */
        $mail = $mailRepository->findOneBy(['key' => MailKey::MAIL_CHANGE_PASSWORD]);
        $this->checkNoAccess('admin_mail_send_demo_mail', ['id' => $mail->getId()], 'POST');

        $userSuperAdm = $this->createUserSuperAdmin();
        $this->client->loginUser($userSuperAdm, 'admin');

        $url = $this->router->generate('admin_mail_send_demo_mail', ['id' => $mail->getId()]);
        $this->client->request('GET', $url);
        $this->assertResponseStatusCodeSame(Response::HTTP_METHOD_NOT_ALLOWED);

        $this->client->request('POST', $url, server: ['HTTP_X-CSRF-TOKEN' => 'jeton-invalide']);
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        $token = $this->getCsrfTokens($mail)['csrf_token_send_demo'];

        $this->testEmail(MailKey::MAIL_CHANGE_PASSWORD, $userSuperAdm->getLogin(), $token);
        $this->testEmail(MailKey::MAIL_ACCOUNT_ADM_DISABLE, $userSuperAdm->getLogin(), $token);
        $this->testEmail(MailKey::MAIL_ACCOUNT_ADM_ENABLE, $userSuperAdm->getLogin(), $token);
        $this->testEmail(MailKey::MAIL_CREATE_ACCOUNT_ADM, $userSuperAdm->getLogin(), $token);
        $this->testEmail(MailKey::MAIL_SELF_DISABLED_ACCOUNT, $userSuperAdm->getLogin(), $token);
        $this->testEmail(MailKey::MAIL_SELF_DELETE_ACCOUNT, $userSuperAdm->getLogin(), $token);
        $this->testEmail(MailKey::MAIL_SELF_ANONYMOUS_ACCOUNT, $userSuperAdm->getLogin(), $token);
        $this->testEmail(MailKey::MAIL_RESET_PASSWORD, $userSuperAdm->getLogin(), $token);
    }

    /**
     * Test envoi email démo avec un contenu non sauvegardé, dans une langue donnée
     * @return void
     */
    public function testSendDemoMailWithContent()
    {
        $this->generateDefaultMails();
        $mailService = $this->container->get(MailService::class);
        $mail = $mailService->getByKey(MailKey::MAIL_CHANGE_PASSWORD);

        $userSuperAdm = $this->createUserSuperAdmin();
        $this->client->loginUser($userSuperAdm, 'admin');
        $token = $this->getCsrfTokens($mail)['csrf_token_send_demo'];
        $url = $this->router->generate('admin_mail_send_demo_mail', ['id' => $mail->getId()]);

        $this->client->request(
            'POST',
            $url,
            server: ['HTTP_X-CSRF-TOKEN' => $token],
            content: json_encode(['locale' => 'xx', 'title' => 'titre', 'content' => 'contenu']),
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $this->client->request(
            'POST',
            $url,
            server: ['HTTP_X-CSRF-TOKEN' => $token],
            content: json_encode([
                'locale' => 'en',
                'title' => 'Titre non sauvegardé',
                'content' => 'Contenu non sauvegardé pour [[user.login]]',
            ]),
        );
        $this->assertResponseIsSuccessful();

        $email = $this->getMailerMessage();
        $this->assertEmailHeaderSame($email, 'Subject', 'Titre non sauvegardé');
        $this->assertEmailHtmlBodyContains($email, 'Contenu non sauvegardé pour ' . $userSuperAdm->getLogin());
    }

    /**
     * Test envoi email en fonction de son code
     * @param String $mailKey
     * @param string $contains
     * @param string $token jeton CSRF
     * @return void
     */
    private function testEmail(string $mailKey, string $contains, string $token): void
    {
        $mailService = $this->container->get(MailService::class);
        /** @var Mail $mail */
        $mail = $mailService->getByKey($mailKey);
        $this->client->request(
            'POST',
            $this->router->generate('admin_mail_send_demo_mail', ['id' => $mail->getId()]),
            server: ['HTTP_X-CSRF-TOKEN' => $token],
        );
        $this->assertResponseIsSuccessful();
        $email = $this->getMailerMessage();
        $this->assertEmailHtmlBodyContains($email, $contains);

        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->assertTrue($content['success']);
        $this->assertStringContainsString($this->translator->trans($mail->getTitle()), $content['msg']);
    }

    /**
     * Retourne les props du composant Vue Mail (dont les jetons CSRF) depuis la page d'édition
     * @param Mail $mail
     * @return array
     */
    private function getCsrfTokens(Mail $mail): array
    {
        $crawler = $this->client->request('GET', $this->router->generate('admin_mail_edit', ['id' => $mail->getId()]));
        $props = $crawler
            ->filter('[data-symfony--ux-vue--vue-component-value="Admin/System/Mail"]')
            ->attr('data-symfony--ux-vue--vue-props-value');

        return json_decode($props, true);
    }
}
