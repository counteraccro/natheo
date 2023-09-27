<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Retourne des infos pour le développement
 */
namespace App\Twig\Runtime\Admin;

use App\Twig\Runtime\AppExtensionRuntime;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\RuntimeExtensionInterface;

class DevExtensionRuntime extends AppExtensionRuntime implements RuntimeExtensionInterface
{
    /**
     * @var ParameterBagInterface
     */
    private ParameterBagInterface $parameterBag;


    /**
     * @param ParameterBagInterface $parameterBag
     * @param RouterInterface $router
     * @param TranslatorInterface $translator
     */
    public function __construct(
        ParameterBagInterface $parameterBag,
        RouterInterface $router,
        TranslatorInterface $translator)
    {
        $this->parameterBag = $parameterBag;
        parent::__construct($router, $translator);
    }

    /**
     * Retourne la version en fonction de l'environnement
     * @return string
     */
    public function getVersion(): string
    {
        $version = $this->parameterBag->get('app.version');
        $debug = $this->parameterBag->get('kernel.debug');

        $return = '';

        if($debug)
        {
            $return = $this->getDevInfo();
        }
        else {
            $return = '<i class="bi bi-bug-fill"></i> <i>
            ' . $this->translator->trans('dev.info.version', domain: 'dev') . ' <b>' . $version . '</b></i>';
        }

        return $return;
    }

    /**
     * Retourne les informations pour les dev
     * @return string
     */
    private function getDevInfo(): string
    {
        $version = $this->parameterBag->get('app.version');
        $branche = $this->parameterBag->get('app.current_branche');
        return '<fieldset>
        <legend class="text-white">' . $this->translator->trans('dev.info', domain: 'dev') . '</legend>
            <i class="bi bi-github"></i> <i>
            ' . $this->translator->trans('dev.info.branche', domain: 'dev') . ' <b>' . $branche . '</b></i> <br />
            <i class="bi bi-bug-fill"></i> <i>
            ' . $this->translator->trans('dev.info.version', domain: 'dev') . ' <b>' . $version . '</b></i>
        </fieldset>';
    }
}