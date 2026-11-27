<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 2.1
 * Retourne des infos pour le développement
 */

namespace App\Twig\Extension\Admin;

use App\Service\Admin\Dev\DevInfoService;
use App\Service\Admin\GridService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Attribute\AsTwigFunction;

class DevExtension extends AppAdminExtension
{
    /**
     * @param ContainerInterface $handlers
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        #[
            AutowireLocator([
                'translator' => TranslatorInterface::class,
                'router' => RouterInterface::class,
                'devInfoService' => DevInfoService::class,
                'gridService' => GridService::class,
            ]),
        ]
        private readonly ContainerInterface $handlers,
    ) {
        parent::__construct($this->handlers);
    }

    /**
     * Retourne la version en fonction de l'environnement
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[AsTwigFunction('devInfo', isSafe: ['html'])]
    public function getDevInfo(): array
    {
        $info = $this->getDevInfoService()->getInfo();

        return [
            'php' => $info['php'],
            'git_branche' => $info['branch'],
            'last_commit' => '<abbr title="' . $info['commit'] . '">' . substr($info['commit'], 0, 7) . '</abbr>',
            'last_commit_date' => $info['commitDate'],
            'version' => $info['version'],
            'env' => $info['env'],
            'database' => $info['database'],
        ];
    }

    /**
     * Retourne les informations PHP
     * @return void
     */
    #[AsTwigFunction('getPhpInfo', isSafe: ['html'])]
    public function getPhpInfo(): void
    {
        phpinfo();
    }

    #[AsTwigFunction('listeUsersToLogin', isSafe: ['html'])]
    public function getListeUsers(): array
    {
        /** @var GridService $gridService */
        $gridService = $this->handlers->get('gridService');

        return array_map(
            fn(array $user): array => [
                'url' => $user['url'],
                'firstLetter' => ucfirst($user['login'][0]),
                'login' => $user['login'],
                'avatar' => $user['avatar'],
                'role' => $gridService->renderRole($user['role']),
            ],
            $this->getDevInfoService()->getLoginUsers(),
        );
    }

    /**
     * @return DevInfoService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getDevInfoService(): DevInfoService
    {
        return $this->handlers->get('devInfoService');
    }
}
