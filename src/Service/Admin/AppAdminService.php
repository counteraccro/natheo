<?php

namespace App\Service\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
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

    public function __construct(EntityManagerInterface $entityManager, ContainerBagInterface $containerBag, TranslatorInterface $translator)
    {
        $this->containerBag = $containerBag;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }
}