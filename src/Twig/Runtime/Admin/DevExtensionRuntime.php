<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Retourne des infos pour le développement
 */

namespace App\Twig\Runtime\Admin;

use App\Service\Admin\Dev\GitService;
use App\Service\Admin\System\SidebarElementService;
use App\Twig\Runtime\AppExtensionRuntime;
use App\Utils\Installation\InstallationConst;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Attribute\AsTwigFilter;
use Twig\Attribute\AsTwigFunction;
use Twig\Extension\RuntimeExtensionInterface;

class DevExtensionRuntime extends AppAdminExtensionRuntime
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
        #[AutowireLocator([
            'translator' => TranslatorInterface::class,
            'router' => RouterInterface::class,
            'parameterBag' => ParameterBagInterface::class,
            'gitService' => GitService::class
        ])]
        private readonly ContainerInterface $handlers)
    {
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
    #[AsTwigFunction('getVersion', isSafe: ['html'])]
    public function getVersion(): string
    {
        $version = $this->parameterBag->get('app.version');
        $debug = $this->parameterBag->get('app.debug_mode');

        if ($debug) {
            $return = $this->getDevInfo();
        } else {
            $return = '<i class="bi bi-bug-fill"></i> <i>
            ' . $this->translator->trans('dev.info.version', domain: 'dev') . ' <b>' . $version . '</b></i>';
        }

        return $return;
    }

    /**
     * Retourne les informations pour les dev
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getDevInfo(): string
    {
        $version = $this->parameterBag->get('app.version');
        $env = $this->parameterBag->get('kernel.environment');
        $infoGit = $this->gitService->getInfoGit();
        $database = match(InstallationConst::STRATEGY) {
            InstallationConst::STRATEGY_MYSQL => 'Mysql',
            InstallationConst::STRATEGY_POSTGRESQL => 'PostgreSQL',
        };
        
        return '<fieldset>
        <legend class="text-white">' . $this->translator->trans('dev.info', domain: 'dev') . '</legend>
            <i class="bi bi-git"></i> <i>
            ' . $this->translator->trans('dev.info.branche', domain: 'dev') .
            ' <b>' . $infoGit[GitService::KEY_BRANCHE] . '</b></i> <br />
            <i class="bi bi-github"></i> <i>
            ' . $this->translator->trans('dev.info.last.commit', domain: 'dev') .
            ' <b><abbr title="' . $infoGit[GitService::KEY_HASH] . '">'
            . substr($infoGit[GitService::KEY_HASH], 0, 7) . '</abbr></b></i>
            <br />
             <i class="bi bi-calendar3"></i> <i>
            ' . $this->translator->trans('dev.info.date.last.commit', domain: 'dev') .
            ' <b><abbr title="' . $infoGit[GitService::KEY_LAST_COMMIT] . '">'
            . $infoGit[GitService::KEY_LAST_COMMIT_SHORT] . '</abbr></b></i>
            <br />
            <i class="bi bi-bug-fill"></i> <i>
            ' . $this->translator->trans('dev.info.version', domain: 'dev') . ' <b>' . $version . '</b></i>
             <br />
            <i class="bi bi-hdd-fill"></i> <i>
            ' . $this->translator->trans('dev.info.env', domain: 'dev') . ' <b>' . $env . '</b></i>
            <br />
            <i class="bi bi-database-fill"></i> <i>
            ' . $this->translator->trans('dev.info.database', domain: 'dev') .' <b> ' . $database . '</b>
            </i>
        </fieldset>';
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
