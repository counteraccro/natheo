<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Informations de la barre de debug : environnement, dépôt git et comptes de connexion rapide
 */

namespace App\Service\Admin\Dev;

use App\Entity\Admin\System\User;
use App\Enum\Installation\DoctrineStrategy;
use App\Service\Admin\AppAdminHandlerService;
use App\Service\Admin\AppAdminService;
use App\Service\Global\User\AvatarUrl;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;

class DevInfoService extends AppAdminService
{
    public function __construct(
        #[AutowireLocator(AppAdminHandlerService::HANDLERS)] ContainerInterface $handlers,
        private readonly GitService $gitService,
        private readonly LoginLinkHandlerInterface $loginLinkHandler,
    ) {
        parent::__construct($handlers);
    }

    /**
     * Version de PHP, environnement, base de données, branche et dernier commit, version de Nathéo
     * @return array{php: string, env: string, database: string, branch: string, commit: string, commitDate: string, version: string}
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getInfo(): array
    {
        $parameterBag = $this->getParameterBag();
        $infoGit = $this->gitService->getInfoGit();

        return [
            'php' => phpversion(),
            'env' => $parameterBag->get('kernel.environment'),
            'database' => match (DoctrineStrategy::current()) {
                DoctrineStrategy::MYSQL->value => 'Mysql',
                DoctrineStrategy::POSTGRESQL->value => 'PostgreSQL',
            },
            'branch' => $infoGit[GitService::KEY_BRANCHE],
            'commit' => $infoGit[GitService::KEY_HASH],
            'commitDate' => $infoGit[GitService::KEY_LAST_COMMIT],
            'version' => $parameterBag->get('app.version'),
        ];
    }

    /**
     * Comptes actifs avec leur lien de connexion
     * @return array<int, array{id: int, login: string, role: string, avatar: string|null, url: string}>
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getLoginUsers(): array
    {
        $avatarPath = $this->getParameterBag()->get('app.path.avatar');
        $users = $this->getEntityManager()
            ->getRepository(User::class)
            ->findBy(['disabled' => false]);

        return array_map(
            fn(User $user): array => [
                'id' => $user->getId(),
                'login' => $user->getLogin(),
                'role' => $user->getRoles()[0],
                'avatar' => AvatarUrl::file($user->getAvatar(), $avatarPath),
                'url' => $this->loginLinkHandler->createLoginLink($user)->getUrl(),
            ],
            $users,
        );
    }
}
