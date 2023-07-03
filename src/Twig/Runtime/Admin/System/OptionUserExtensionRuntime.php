<?php

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Permet de générer le formulaire de saisie des options users
 * Permet de récupérer la valeur d'une option
 */

namespace App\Twig\Runtime\Admin\System;

use App\Service\Admin\System\OptionUserService;
use App\Twig\Runtime\Admin\AppAdminExtensionRuntime;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\RuntimeExtensionInterface;

class OptionUserExtensionRuntime extends AppAdminExtensionRuntime implements RuntimeExtensionInterface
{
    /**
     * @var OptionUserService
     */
    private OptionUserService $optionUserService;

    public function __construct(RouterInterface   $router, TranslatorInterface $translator,
                                OptionUserService $optionUserService
    )
    {
        $this->optionUserService = $optionUserService;
        parent::__construct($router, $translator);
    }

    /**
     * Retourne la valeur de l'option en fonction de sa clé
     * @param string $key
     * @return string
     */
    public function getOptionValueByKey(string $key): string
    {
        return $this->optionUserService->getValueByKey($key);
    }
}