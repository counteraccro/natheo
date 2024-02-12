<?php

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Service lier à l'objet SidebarElement
 */

namespace App\Service\Admin\System;

use App\Entity\Admin\System\SidebarElement;
use App\Service\Admin\AppAdminService;
use App\Service\Admin\GridService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SidebarElementService extends AppAdminService
{
    /**
     * @var GridService
     */
    private GridService $gridService;

    /**
     * @param ContainerInterface $handlers
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(#[AutowireLocator([
        'logger' => LoggerInterface::class,
        'entityManager' => EntityManagerInterface::class,
        'containerBag' => ContainerBagInterface::class,
        'translator' => TranslatorInterface::class,
        'router' => UrlGeneratorInterface::class,
        'security' => Security::class,
        'requestStack' => RequestStack::class,
        'parameterBag' => ParameterBagInterface::class,
        'gridService' => GridService::class
    ])] ContainerInterface $handlers)
    {
        $this->gridService = $handlers->get('gridService');
        parent::__construct($handlers);
    }

    /**
     * Récupère l'ensemble des sidebarElement parent
     * @param bool $disabled
     * @return mixed
     */
    public function getAllParent(bool $disabled = false): mixed
    {
        $repo = $this->getRepository(SidebarElement::class);
        return $repo->getAllParent($disabled);
    }

    /**
     * Retourne une liste de sidebarElement paginé
     * @param int $page
     * @param int $limit
     * @return Paginator
     */
    public function getAllPaginate(int $page, int $limit): Paginator
    {
        $repo = $this->getRepository(SidebarElement::class);
        return $repo->getAllPaginate($page, $limit);
    }

    /**
     * Construit le tableau de donnée à envoyé au tableau GRID
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getAllFormatToGrid(int $page, int $limit)
    {
        $column = [
            $this->translator->trans('sidebar.grid.id', domain: 'sidebar'),
            $this->translator->trans('sidebar.grid.parent', domain: 'sidebar'),
            $this->translator->trans('sidebar.grid.label', domain: 'sidebar'),
            $this->translator->trans('sidebar.grid.role', domain: 'sidebar'),
            $this->translator->trans('sidebar.grid.description', domain: 'sidebar'),
            $this->translator->trans('sidebar.grid.created_at', domain: 'sidebar'),
            $this->translator->trans('sidebar.grid.update_at', domain: 'sidebar'),
            GridService::KEY_ACTION,
        ];

        $dataPaginate = $this->getAllPaginate($page, $limit);

        $nb = $dataPaginate->count();
        $data = [];
        foreach ($dataPaginate as $element) {
            /* @var SidebarElement $element */

            $parent = '---';
            if ($element->getParent() !== null) {
                $parent = '<i class="bi ' . $element->getParent()->getIcon() . '"></i> ' .
                    $this->translator->trans($element->getParent()->getLabel());
            }

            $action = $this->generateTabAction($element);

            $isLock = '';
            if ($element->isLock()) {
                $isLock = '<i class="bi bi-lock-fill"></i>';
            }
            $isDisabled = '';
            if ($element->isDisabled()) {
                $isDisabled = '<i class="bi bi-eye-slash"></i>';
            }

            $data[] = [
                $this->translator->trans('sidebar.grid.id', domain: 'sidebar') => $element->getId() . ' ' . $isLock .
                    ' ' . $isDisabled,
                $this->translator->trans('sidebar.grid.parent', domain: 'sidebar') => $parent,
                $this->translator->trans('sidebar.grid.label', domain: 'sidebar') => '<i class="bi ' .
                    $element->getIcon() . '"></i> ' . $this->translator->trans($element->getLabel()),
                $this->translator->trans('sidebar.grid.role', domain: 'sidebar') => $this->gridService
                    ->renderRole($element->getRole()),
                $this->translator->trans('sidebar.grid.description', domain: 'sidebar') => $this->translator
                    ->trans($element->getDescription()),
                $this->translator->trans('sidebar.grid.created_at', domain: 'sidebar') => $element
                    ->getCreatedAt()->format('d/m/y H:i'),
                $this->translator->trans('sidebar.grid.update_at', domain: 'sidebar') => $element
                    ->getUpdateAt()->format('d/m/y H:i'),
                GridService::KEY_ACTION => json_encode($action),
            ];
        }

        $tabReturn = [
            GridService::KEY_NB => $nb,
            GridService::KEY_DATA => $data,
            GridService::KEY_COLUMN => $column,
        ];
        return $this->gridService->addAllDataRequiredGrid($tabReturn);

    }

    /**
     * Génère le tableau d'action pour le Grid des sidebarElement
     * @param SidebarElement $element
     * @return array[]|string[]
     */
    private function generateTabAction(SidebarElement $element): array
    {
        $actionDisabled = '';
        if (!$element->isLock()) {
            $actionDisabled = ['label' => '<i class="bi bi-eye-slash-fill"></i>',
                'url' => $this->router->generate('admin_sidebar_update_disabled', ['id' => $element->getId()]),
                'ajax' => true,
                'confirm' => true,
                'msgConfirm' => $this->translator->trans('sidebar.confirm.disabled.msg', ['{label}' => '<i class="bi ' .
                    $element->getIcon() . '"></i> ' . $this->translator->trans($element->getLabel())], 'sidebar')];
            if ($element->isDisabled()) {
                $actionDisabled = [
                    'label' => '<i class="bi bi-eye-fill"></i>',
                    'url' => $this->router->generate('admin_sidebar_update_disabled', ['id' => $element->getId()]),
                    'ajax' => true
                ];
            }
        }

        $action = [];
        if ($actionDisabled != '') {
            $action[] = $actionDisabled;
        }

        return $action;
    }
}
