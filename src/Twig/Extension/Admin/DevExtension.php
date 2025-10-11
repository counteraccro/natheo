<?php
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Retourne des infos pour le développement
 */

namespace App\Twig\Extension\Admin;

use App\Service\Admin\Dev\GitService;
use App\Utils\Installation\InstallationConst;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Attribute\AsTwigFunction;

class DevExtension extends AppAdminExtension
{
    /**
     * @var ParameterBagInterface
     */
    private ParameterBagInterface $parameterBag;

    /**
     * @var GitService
     */
    private GitService $gitService;

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
                'parameterBag' => ParameterBagInterface::class,
                'gitService' => GitService::class,
            ]),
        ]
        private readonly ContainerInterface $handlers,
    ) {
        $this->parameterBag = $this->handlers->get('parameterBag');
        $this->gitService = $this->handlers->get('gitService');
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
        $version = $this->parameterBag->get('app.version');
        $env = $this->parameterBag->get('kernel.environment');
        $infoGit = $this->gitService->getInfoGit();
        $database = match (InstallationConst::STRATEGY) {
            InstallationConst::STRATEGY_MYSQL => 'Mysql',
            InstallationConst::STRATEGY_POSTGRESQL => 'PostgreSQL',
        };

        return [
            'php' => phpversion(),
            'git_branche' => $infoGit[GitService::KEY_BRANCHE],
            'last_commit' =>
                '<abbr title="' .
                $infoGit[GitService::KEY_HASH] .
                '">' .
                substr($infoGit[GitService::KEY_HASH], 0, 7) .
                '</abbr>',
            'last_commit_date' => $infoGit[GitService::KEY_LAST_COMMIT],
            'version' => $version,
            'env' => $env,
            'database' => $database,
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
}
