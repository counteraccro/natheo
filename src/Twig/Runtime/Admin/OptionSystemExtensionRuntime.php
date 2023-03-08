<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Permet de générer le formulaire de saisie des options systèmes
 */

namespace App\Twig\Runtime\Admin;

use App\Service\Admin\OptionSystemService;
use App\Utils\Debug;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\RuntimeExtensionInterface;

class OptionSystemExtensionRuntime extends AppAdminExtensionRuntime implements RuntimeExtensionInterface
{

    /**
     * @var OptionSystemService
     */
    private OptionSystemService $optionSystemService;

    /**
     * @param RouterInterface $router
     * @param TranslatorInterface $translator
     * @param OptionSystemService $optionSystemService
     */
    public function __construct(RouterInterface $router, TranslatorInterface $translator, OptionSystemService $optionSystemService)
    {
        $this->optionSystemService = $optionSystemService;
        parent::__construct($router,$translator);
    }

    /**
     * Point d'entrée pour la génération du formulaire des options systèmes
     * @return string
     * @throws Exception
     */
    public function getOptionSystem(): string
    {
        $optionsSystemConfig = $this->optionSystemService->getOptionsSystemConfig();
        Debug::print_r($optionsSystemConfig);

        $html = 'OKi';

        return $html;
    }

}
