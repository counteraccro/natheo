<?php

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Service global
 */

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

class AppService
{
    /**
     * @var TranslatorInterface
     */
    protected TranslatorInterface $translator;

    /**
     * @var RequestStack
     */
    protected RequestStack $requestStack;

    /**
     * @var Security
     */
    protected Security $security;

    /**
     * @var ContainerBagInterface
     */
    protected ContainerBagInterface $params;

    /**
     * @var EntityManagerInterface
     */
    protected EntityManagerInterface $entityManager;

    public function __construct(
        TranslatorInterface   $translator,
        RequestStack          $requestStack,
        Security              $security,
        ContainerBagInterface $params,
        EntityManagerInterface $entityManager
    )
    {
        $this->translator = $translator;
        $this->requestStack = $requestStack;
        $this->security = $security;
        $this->params = $params;
        $this->entityManager = $entityManager;
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
}
