<?php

namespace App\Service\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

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

    public function __construct(EntityManagerInterface $entityManager, ContainerBagInterface $containerBag)
    {
        $this->containerBag = $containerBag;
        $this->entityManager = $entityManager;
    }
}