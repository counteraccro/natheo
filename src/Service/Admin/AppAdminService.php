<?php

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Service global pour l'administration
 */

namespace App\Service\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
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

    /**
     * @var Security
     */
    protected Security $security;

    /**
     * @var RequestStack
     */
    protected RequestStack $requestStack;

    public function __construct(
        EntityManagerInterface $entityManager,
        ContainerBagInterface  $containerBag,
        TranslatorInterface    $translator,
        UrlGeneratorInterface  $router,
        Security               $security,
        RequestStack           $requestStack
    )
    {
        $this->requestStack = $requestStack;
        $this->containerBag = $containerBag;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
        $this->router = $router;
        $this->security = $security;
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

    /**
     * Permet de supprimer une entité
     * @param mixed $entity
     * @param bool $flush
     * @return void
     */
    public function remove(mixed $entity, bool $flush = true): void
    {
        $repo = $this->getRepository($entity::class);
        $repo->remove($entity, $flush);
    }


    /**
     * Permet de vérifier les droits
     * @param mixed $attribute
     * @param mixed|null $subject
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function isGranted(mixed $attribute, mixed $subject = null): bool
    {
        return $this->containerBag->get('security.authorization_checker')->isGranted($attribute, $subject);
    }


    /**
     * Retourne une entité en fonction de son id
     * @param string $entity
     * @param int $id
     * @return object|null
     */
    public function findOneById(string $entity, int $id): ?object
    {
        $repo = $this->getRepository($entity);
        return $repo->findOneBy(['id' => $id]);
    }

}
