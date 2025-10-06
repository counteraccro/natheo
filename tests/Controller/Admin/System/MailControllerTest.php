<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * test controller mail
 */

namespace App\Tests\Controller\Admin\System;

use App\Entity\Admin\System\Mail;
use App\Repository\Admin\System\MailRepository;
use App\Service\Admin\System\MailService;
use App\Tests\AppWebTestCase;
use App\Utils\System\Mail\MailKey;

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
        $this->client->request(
            'POST',
            $this->router->generate('admin_mail_save', ['id' => $mail->getId()]),
            content: json_encode($parameters),
        );

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
        $this->assertEquals($contentMail, $mail->geMailTranslationByLocale('fr')->getContent());
        $this->assertEquals($title, $mail->geMailTranslationByLocale('fr')->getTitle());
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
        $this->checkNoAccess('admin_mail_send_demo_mail', ['id' => $mail->getId()]);

        $userSuperAdm = $this->createUserSuperAdmin();
        $this->client->loginUser($userSuperAdm, 'admin');

        $this->testEmail(MailKey::MAIL_CHANGE_PASSWORD, $userSuperAdm->getLogin());
        $this->testEmail(MailKey::MAIL_ACCOUNT_ADM_DISABLE, $userSuperAdm->getLogin());
        $this->testEmail(MailKey::MAIL_ACCOUNT_ADM_ENABLE, $userSuperAdm->getLogin());
        $this->testEmail(MailKey::MAIL_CREATE_ACCOUNT_ADM, $userSuperAdm->getLogin());
        $this->testEmail(MailKey::MAIL_SELF_DISABLED_ACCOUNT, $userSuperAdm->getLogin());
        $this->testEmail(MailKey::MAIL_SELF_DELETE_ACCOUNT, $userSuperAdm->getLogin());
        $this->testEmail(MailKey::MAIL_SELF_ANONYMOUS_ACCOUNT, $userSuperAdm->getLogin());
        $this->testEmail(MailKey::MAIL_RESET_PASSWORD, $userSuperAdm->getLogin());
    }

    /**
     * Test envoi email en fonction de son code
     * @param String $mailKey
     * @param string $contains
     * @return void
     */
    private function testEmail(string $mailKey, string $contains): void
    {
        $mailService = $this->container->get(MailService::class);
        /** @var Mail $mail */
        $mail = $mailService->getByKey($mailKey);
        $this->client->request('GET', $this->router->generate('admin_mail_send_demo_mail', ['id' => $mail->getId()]));
        $this->assertResponseIsSuccessful();
        $email = $this->getMailerMessage();
        $this->assertEmailHtmlBodyContains($email, $contains);

        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->assertTrue($content['success']);
        $this->assertStringContainsString($this->translator->trans($mail->getTitle()), $content['msg']);
    }
}
