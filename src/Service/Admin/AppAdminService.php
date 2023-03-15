<?php

namespace App\Service\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AppAdminService
{

    /**
     * @var EntityManagerInterface
     */
    protected EntityManagerInterface $entityManager;

    /**
     * Paramètre globaux de Symfony
     * @var ContainerBagInterface
     */
    protected ContainerBagInterface $containerBag;

    /**
     * @var TranslatorInterface
     */
    protected TranslatorInterface $translator;

    /**
     * @var UrlGeneratorInterface
     */
    protected UrlGeneratorInterface $router;

    public function __construct(EntityManagerInterface $entityManager, ContainerBagInterface $containerBag, TranslatorInterface $translator, UrlGeneratorInterface $router)
    {
        $this->containerBag = $containerBag;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
        $this->router = $router;
    }

    /**
     * Ajoute le choix des limits dans le tableau de donnée du GRID
     * @param array $tab
     * @return array
     */
    protected function addOptionsSelectLimit(array $tab): array
    {
        $optionLimitGrid = [5 => 5, 10 => 10, 20 => 20, 50 => 50, 100 => 100];
        $tab['listLimit'] = json_encode($optionLimitGrid);
        return $tab;
    }
}