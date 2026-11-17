<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test des droits par élément calculés pour l'utilisateur de la session API
 */

namespace App\Tests\Api\V2\Security;

use App\Api\V2\Security\AdminPermissionResolver;
use App\Entity\Admin\System\User;
use App\Enum\Admin\System\ApiToken\ApiTokenScope;
use App\Enum\Admin\System\Options\OptionSystem;
use App\Security\ApiUser;
use App\Service\Admin\System\OptionSystemService;
use App\Tests\AppWebTestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AdminPermissionResolverTest extends AppWebTestCase
{
    /**
     * Propriétaire : tous les droits, suppression selon l'option ; autre contributeur : aucun droit
     * @return void
     */
    public function testForContent(): void
    {
        $owner = $this->createUserContributeur();
        $tag = $this->createTag($owner);
        $resolver = new AdminPermissionResolver($this->container->get(AuthorizationCheckerInterface::class));
        $optionSystemService = $this->container->get(OptionSystemService::class);

        $optionSystemService->saveValueByKee(OptionSystem::OS_ALLOW_DELETE_DATA->value, '1');
        $this->authenticate($owner);
        $permissions = $resolver->forContent($tag);
        $this->assertTrue($permissions->edit);
        $this->assertTrue($permissions->disable);
        $this->assertTrue($permissions->delete);

        $optionSystemService->saveValueByKee(OptionSystem::OS_ALLOW_DELETE_DATA->value, '0');
        $this->assertFalse($resolver->forContent($tag)->delete);

        $this->authenticate($this->createUserContributeur());
        $permissions = $resolver->forContent($tag);
        $this->assertFalse($permissions->edit);
        $this->assertFalse($permissions->disable);
    }

    /**
     * Place une session API de l'utilisateur dans le token de sécurité
     * @param User $user
     * @return void
     */
    private function authenticate(User $user): void
    {
        $apiToken = $this->createApiToken(['scope' => ApiTokenScope::BACK_OFFICE, 'disabled' => false]);
        $apiUser = new ApiUser($apiToken, $this->createUserSession(['user' => $user, 'apiToken' => $apiToken]));
        $this->container
            ->get(TokenStorageInterface::class)
            ->setToken(new UsernamePasswordToken($apiUser, 'api', $apiUser->getRoles()));
    }
}
