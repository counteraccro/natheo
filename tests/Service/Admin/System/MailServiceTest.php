<?php

declare(strict_types=1);

namespace App\Tests\Service\Admin\System;

use App\Entity\Admin\System\Mail;
use App\Enum\Admin\Content\Page\PageCategory;
use App\Enum\Admin\Content\Page\PageStatus;
use App\Enum\Admin\System\Options\OptionSystem;
use App\Repository\Admin\System\MailRepository;
use App\Service\Admin\System\MailService;
use App\Service\Admin\System\OptionSystemService;
use App\Tests\AppWebTestCase;
use App\Utils\System\Mail\KeyWord;
use App\Utils\System\Mail\MailKey;
use App\Utils\System\Mail\MailTemplate;
use League\CommonMark\Exception\CommonMarkException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\String\ByteString;

class MailServiceTest extends AppWebTestCase
{
    /**
     * @var MailService|mixed|object|Container|null
     */
    private MailService $mailService;

    private MailRepository $mailRepository;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->mailService = $this->container->get(MailService::class);
        $this->mailRepository = $this->em->getRepository(Mail::class);

        // Le jeton CSRF des actions du grid est stocké en session
        $request = new Request();
        $request->setSession(new Session(new MockArraySessionStorage()));
        $this->container->get(RequestStack::class)->push($request);
    }

    /**
     * Test le formatage d'un email
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetMailFormat(): void
    {
        $this->generateDefaultMails();
        $mail = $this->mailRepository->findOneBy(['key' => MailKey::MAIL_CHANGE_PASSWORD]);
        $result = $this->mailService->getMailFormat('fr', $mail);
        $this->assertNotEmpty($result);
        $this->assertEquals($this->translator->trans($mail->getTitle()), $result['title']);
    }

    /**
     * Test retour grid
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetAllFormatToGrid(): void
    {
        $this->generateDefaultMails();

        $queryParams = [
            'search' => '',
            'orderField' => 'id',
            'order' => 'DESC',
            'locale' => 'fr',
        ];

        $result = $this->mailService->getAllFormatToGrid(1, 5, $queryParams);
        $this->assertArrayHasKey('nb', $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('column', $result);
        $this->assertArrayHasKey('sql', $result);
        $this->assertArrayHasKey('translate', $result);
        $this->assertEquals(8, $result['nb']);
        $this->assertCount(5, $result['data']);
    }

    /**
     * Test envoi mail
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws CommonMarkException
     * @throws TransportExceptionInterface
     */
    public function testSendMail(): void
    {
        $this->generateDefaultMails();
        $user = $this->createUser();
        $userSuperAdmin = $this->createUserSuperAdmin();
        $key = ByteString::fromRandom(48)->toString();

        $mail = $this->mailService->getByKey(MailKey::MAIL_RESET_PASSWORD);
        $keyWord = new KeyWord($mail->getKey());
        $tabKeyWord = $keyWord->getTabMailResetPassword(
            $user,
            $userSuperAdmin,
            $this->router->generate('auth_change_password_user', ['key' => $key]),
            $this->container->get(OptionSystemService::class),
        );
        $params = $this->mailService->getDefaultParams($mail, $tabKeyWord);
        $params[MailService::TO] = $user->getEmail();

        $this->mailService->sendMail($params);
        $messages = $this->getMailerMessages();

        $this->assertCount(1, $messages);

        $email = $messages[0];
        $this->assertEmailHtmlBodyContains($email, $user->getLogin());
        // Le CSS de l'entry Vite "email" doit être inliné dans le HTML
        $this->assertEmailHtmlBodyContains($email, 'background-color: #fff');
    }

    /**
     * Test méthode getByKey
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetByKey(): void
    {
        $this->generateDefaultMails();
        $mail = $this->mailService->getByKey(MailKey::MAIL_RESET_PASSWORD);
        $this->assertNotNull($mail);
    }

    /**
     * Test méthode testGetDefaultParams()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetDefaultParams(): void
    {
        $this->generateDefaultMails();
        $user = $this->createUser();
        $userSuperAdmin = $this->createUserSuperAdmin();
        $key = ByteString::fromRandom(48)->toString();

        $mail = $this->mailService->getByKey(MailKey::MAIL_RESET_PASSWORD);
        $keyWord = new KeyWord($mail->getKey());
        $tabKeyWord = $keyWord->getTabMailResetPassword(
            $user,
            $userSuperAdmin,
            $this->router->generate('auth_change_password_user', ['key' => $key]),
            $this->container->get(OptionSystemService::class),
        );
        $params = $this->mailService->getDefaultParams($mail, $tabKeyWord);

        $this->assertArrayHasKey(MailService::TITLE, $params);
        $this->assertArrayHasKey(MailService::CONTENT, $params);
        $this->assertArrayHasKey(MailService::TO, $params);
        $this->assertArrayHasKey(MailService::TEMPLATE, $params);
        $this->assertStringContainsString($user->getLogin(), $params[MailService::CONTENT]);
        $this->assertStringNotContainsString('[[user.login]]', $params[MailService::CONTENT]);

        $params = $this->mailService->getDefaultParams($mail, $tabKeyWord, 'en');
        $this->assertEquals($mail->getMailTranslationByLocale('en')->getTitle(), $params[MailService::TITLE]);
        $this->assertEquals('en', $params[MailService::LOCALE]);

        // Langue sans traduction : repli sur la première traduction disponible
        $params = $this->mailService->getDefaultParams($mail, $tabKeyWord, 'xx');
        $this->assertEquals($mail->getMailTranslations()->first()->getTitle(), $params[MailService::TITLE]);
    }

    /**
     * Test envoi mail avec cc et bcc : le bcc ne doit pas apparaitre en cc
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws CommonMarkException
     * @throws TransportExceptionInterface
     */
    public function testSendMailCcBcc(): void
    {
        $this->mailService->sendMail([
            MailService::TITLE => 'titre',
            MailService::CONTENT => 'contenu',
            MailService::TO => 'to@natheo.test',
            MailService::CC => ['cc1@natheo.test', 'cc2@natheo.test'],
            MailService::BCC => 'bcc@natheo.test',
            MailService::FROM => '',
            MailService::TEMPLATE => MailTemplate::EMAIL_SIMPLE_TEMPLATE,
        ]);

        /** @var Email $email */
        $email = $this->getMailerMessages()[0];
        $this->assertEquals(
            ['cc1@natheo.test', 'cc2@natheo.test'],
            array_map(fn($address) => $address->getAddress(), $email->getCc()),
        );
        $this->assertEquals('bcc@natheo.test', $email->getBcc()[0]->getAddress());
        // FROM vide : valeur de l'option système utilisée
        $this->assertNotEmpty($email->getFrom());
    }

    /**
     * Test envoi mail : liens internes convertis dans la langue de l'email et urls relatives rendues absolues
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws CommonMarkException
     * @throws TransportExceptionInterface
     */
    public function testSendMailInternalLinkAndAbsoluteUrls(): void
    {
        $page = $this->createPage(
            customData: [
                'status' => PageStatus::PUBLISH->value,
                'disabled' => false,
                'category' => PageCategory::PAGE->value,
            ],
        );
        $this->createPageTranslation($page, ['locale' => 'fr', 'url' => 'page-fr']);
        $this->createPageTranslation($page, ['locale' => 'en', 'url' => 'page-en']);
        $draft = $this->createPage(customData: ['status' => PageStatus::DRAFT->value, 'disabled' => false]);
        $this->createPageTranslation($draft, ['locale' => 'en']);

        $this->mailService->sendMail([
            MailService::TITLE => 'titre',
            MailService::CONTENT => sprintf(
                '[page](P#%d) [brouillon](P#%d) ![img](/assets/image.png)',
                $page->getId(),
                $draft->getId(),
            ),
            MailService::TO => 'to@natheo.test',
            MailService::TEMPLATE => MailTemplate::EMAIL_SIMPLE_TEMPLATE,
            MailService::LOCALE => 'en',
        ]);

        $siteUrl = rtrim(
            (string) $this->container
                ->get(OptionSystemService::class)
                ->getValueByKey(OptionSystem::OS_ADRESSE_SITE->value),
            '/',
        );
        $email = $this->getMailerMessages()[0];
        $this->assertEmailHtmlBodyContains($email, 'href="' . $siteUrl . '/en/');
        $this->assertEmailHtmlBodyContains($email, '/page-en"');
        $this->assertEmailHtmlBodyContains($email, '<a href="#"');
        $this->assertEmailHtmlBodyContains($email, 'src="' . $siteUrl . '/assets/image.png"');
        $this->assertEmailHtmlBodyNotContains($email, 'P#');
    }

    /**
     * Test remplacement des mots clés
     * @return void
     */
    public function testReplaceKeyWords(): void
    {
        $tabKeyWord = [KeyWord::KEY_SEARCH => ['[[user.login]]'], KeyWord::KEY_REPLACE => ['natheo']];
        $this->assertEquals(
            'Bonjour natheo',
            $this->mailService->replaceKeyWords('Bonjour [[user.login]]', $tabKeyWord),
        );
    }

    /**
     * Test des mots clés de démo pour chaque email
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetDemoKeyWords(): void
    {
        $this->generateDefaultMails();
        $user = $this->createUserSuperAdmin();

        $mail = $this->mailService->getByKey(MailKey::MAIL_RESET_PASSWORD);
        $tab = $this->mailService->getDemoKeyWords($mail, $user);
        $this->assertContains('[[admin.login]]', $tab[KeyWord::KEY_SEARCH]);
        $this->assertContains($user->getLogin(), $tab[KeyWord::KEY_REPLACE]);

        $mail = $this->createMail(['key' => 'MAIL_INCONNU']);
        $tab = $this->mailService->getDemoKeyWords($mail, $user);
        $this->assertEmpty($tab[KeyWord::KEY_SEARCH]);
    }

    /**
     * Test envoi d'un email de démo
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws CommonMarkException
     * @throws TransportExceptionInterface
     */
    public function testSendDemoMail(): void
    {
        $this->generateDefaultMails();
        $user = $this->createUserSuperAdmin();
        $mail = $this->mailService->getByKey(MailKey::MAIL_CHANGE_PASSWORD);

        $this->mailService->sendDemoMail($mail, $user, 'fr', 'Mon titre', 'Mon contenu [[user.email]]');

        $email = $this->getMailerMessages()[0];
        $this->assertEmailHeaderSame($email, 'Subject', 'Mon titre');
        $this->assertEmailHeaderSame($email, 'To', $user->getEmail());
        $this->assertEmailHtmlBodyContains($email, 'Mon contenu ' . $user->getEmail());
    }
}
