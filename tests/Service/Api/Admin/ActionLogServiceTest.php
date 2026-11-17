<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test du journal des actions du back-office
 */

namespace App\Tests\Service\Api\Admin;

use App\Enum\Admin\System\ActionLog\ActionLogAction;
use App\Enum\Admin\System\ApiToken\ApiTokenScope;
use App\Security\ApiUser;
use App\Service\Api\Admin\ActionLogService;
use App\Tests\AppWebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class ActionLogServiceTest extends AppWebTestCase
{
    private ActionLogService $actionLogService;

    public function setUp(): void
    {
        parent::setUp();
        $request = Request::create('/api/v2/admin/tags/1', server: ['REMOTE_ADDR' => '10.0.0.1']);
        $request->setSession(new Session(new MockArraySessionStorage()));
        $this->container->get(RequestStack::class)->push($request);
        $this->actionLogService = $this->container->get(ActionLogService::class);
    }

    /**
     * Auteur et utilisateur d'origine lus dans la session API de la requête, IP de la requête
     * @return void
     */
    public function testLog(): void
    {
        $user = $this->createUserContributeur();
        $impersonator = $this->createUserSuperAdmin();
        $apiToken = $this->createApiToken(['scope' => ApiTokenScope::BACK_OFFICE, 'disabled' => false]);
        $session = $this->createUserSession([
            'user' => $user,
            'apiToken' => $apiToken,
            'impersonator' => $impersonator,
        ]);
        $apiUser = new ApiUser($apiToken, $session);
        $this->container
            ->get(TokenStorageInterface::class)
            ->setToken(new UsernamePasswordToken($apiUser, 'api', $apiUser->getRoles()));

        $log = $this->actionLogService->log(
            'tag',
            ActionLogAction::UPDATE->value,
            'AdminTag',
            '12',
            str_repeat('a', 300),
        );

        $this->assertNotNull($log->getId());
        $this->assertSame($user, $log->getUser());
        $this->assertSame($impersonator, $log->getImpersonator());
        $this->assertSame('tag', $log->getModule());
        $this->assertSame('update', $log->getAction());
        $this->assertSame('AdminTag', $log->getResource());
        $this->assertSame('12', $log->getResourceId());
        $this->assertSame(255, mb_strlen((string) $log->getLabel()));
        $this->assertSame('10.0.0.1', $log->getIp());
        $this->assertInstanceOf(\DateTimeImmutable::class, $log->getCreatedAt());
    }

    /**
     * Sans session API : action tracée sans auteur
     * @return void
     */
    public function testLogWithoutSession(): void
    {
        $log = $this->actionLogService->log('tag', ActionLogAction::DELETE->value, 'AdminTag');
        $this->assertNull($log->getUser());
        $this->assertNull($log->getImpersonator());
        $this->assertNull($log->getResourceId());
    }
}
