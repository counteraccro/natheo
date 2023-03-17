<?php

namespace App\Service\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
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

    public function __construct(EntityManagerInterface $entityManager, ContainerBagInterface $containerBag,
                                TranslatorInterface $translator, UrlGeneratorInterface $router)
    {
        $this->containerBag = $containerBag;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
        $this->router = $router;
    }

    /**
     * Retourne le repository en fonction de l'entité
     * @param string $entity
     * @return EntityRepository
     */
    protected function getRepository(string $entity): EntityRepository
    {
        return $this->entityManager->getRepository($entity);
    }

    /**
     * Permet de sauvegarder une entité
     * @param mixed $entity
     * @param bool $flush
     * @return void
     */
    public function save(mixed $entity, bool $flush = true): void
    {
        $repo = $this->getRepository($entity::class);
        $repo->save($entity, $flush);
    }

}