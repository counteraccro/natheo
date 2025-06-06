<?php

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Permet de générer le formulaire de saisie des options users
 * Permet de récupérer la valeur d'une option
 */

namespace App\Twig\Extension\Admin\System;

use App\Service\Admin\System\OptionUserService;
use App\Twig\Extension\Admin\AppAdminExtension;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Attribute\AsTwigFunction;

class OptionUserExtension extends AppAdminExtension
{
    /**
     * @var OptionUserService
     */
    private OptionUserService $optionUserService;

    /**
     * @param ContainerInterface $handlers
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        #[AutowireLocator([
            'translator' => TranslatorInterface::class,
            'router' => RouterInterface::class,
            'optionUserService' => OptionUserService::class
        ])]
        private readonly ContainerInterface $handlers
    )
    {
        $this->optionUserService = $this->handlers->get('optionUserService');
        parent::__construct($this->handlers);
    }

    /**
     * Retourne la valeur de l'option en fonction de sa clé
     * @param string $key
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[AsTwigFunction('get_option_user_value_by_key')]
    public function getOptionValueByKey(string $key): string
    {
        return $this->optionUserService->getValueByKey($key);
    }
}
