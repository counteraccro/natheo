<?php

/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Service global
 */

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
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

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function __construct(
        #[
            AutowireLocator([
                'entityManager' => EntityManagerInterface::class,
                'containerBag' => ContainerBagInterface::class,
                'translator' => TranslatorInterface::class,
                'security' => Security::class,
                'requestStack' => RequestStack::class,
            ]),
        ]
        private readonly ContainerInterface $handlers,
    ) {
        $this->translator = $this->handlers->get('translator');
        $this->requestStack = $this->handlers->get('requestStack');
        $this->security = $this->handlers->get('security');
        $this->params = $this->handlers->get('containerBag');
        $this->entityManager = $this->handlers->get('entityManager');
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
