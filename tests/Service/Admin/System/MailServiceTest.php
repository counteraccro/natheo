<?php

namespace App\Tests\Service\Admin\System;

use App\Entity\Admin\System\Mail;
use App\Repository\Admin\System\MailRepository;
use App\Service\Admin\System\MailService;
use App\Service\Admin\System\OptionSystemService;
use App\Tests\AppWebTestCase;
use App\Utils\System\Mail\KeyWord;
use App\Utils\System\Mail\MailKey;
use App\Utils\System\Options\OptionSystemKey;
use League\CommonMark\Exception\CommonMarkException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
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
        $this->assertEquals($this->translator->trans($mail->getTitle()), $result[$mail->getId()]['title']);
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
    }
}
