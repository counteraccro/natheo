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
     * Retourne un Json contenant la liste de choix d'affichage du nombre d'éléments dans le grid
     * @return string
     */
    protected function getOptionsSelectLimit(): string
    {
        $optionLimitGrid = [5 => 5, 10 => 10, 20 => 20, 50 => 50, 100 => 100];
        return json_encode($optionLimitGrid);
    }
}