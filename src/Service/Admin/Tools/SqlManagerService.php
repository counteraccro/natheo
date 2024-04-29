<?php

namespace App\Service\Admin\Tools;

use App\Entity\Admin\Tools\SqlManager;
use App\Service\Admin\AppAdminService;
use App\Service\Admin\GridService;
use App\Service\Admin\System\OptionSystemService;
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

class SqlManagerService extends AppAdminService
{

    /**
     * @var GridService
     */
    private GridService $gridService;

    /**
     * @var OptionSystemService
     */
    private OptionSystemService $optionSystemService;

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
        'optionSystemService' => OptionSystemService::class,
        'gridService' => GridService::class,
    ])] ContainerInterface $handlers)
    {
        $this->gridService = $handlers->get('gridService');
        $this->optionSystemService = $handlers->get('optionSystemService');
        parent::__construct($handlers);
    }

    /**
     * Construit le tableau de donnée à envoyé au tableau GRID
     * @param int $page
     * @param int $limit
     * @param string|null $search
     * @return array
     */
    public function getAllFormatToGrid(int $page, int $limit, string $search = null): array
    {
        $column = [
            $this->translator->trans('sql_manager.grid.id', domain: 'sql_manager'),
            $this->translator->trans('sql_manager.grid.name', domain: 'sql_manager'),
            $this->translator->trans('sql_manager.grid.query', domain: 'sql_manager'),
            $this->translator->trans('sql_manager.grid.update_at', domain: 'sql_manager'),
            GridService::KEY_ACTION,
        ];

        $dataPaginate = $this->getAllPaginate($page, $limit, $search);

        $nb = $dataPaginate->count();
        $data = [];
        foreach ($dataPaginate as $element) {
            /* @var SqlManager $element */

            $action = $this->generateTabAction($element);


            $isDisabled = '';
            if ($element->isDisabled()) {
                $isDisabled = '<i class="bi bi-eye-slash"></i>';
            }

            $data[] = [
                $this->translator->trans('sql_manager.grid.id', domain: 'sql_manager') =>
                    $element->getId() . ' ' . $isDisabled,
                $this->translator->trans('sql_manager.grid.name', domain: 'sql_manager') => $element->getName(),
                $this->translator->trans('sql_manager.grid.query', domain: 'sql_manager') => $element->getQuery(),
                $this->translator->trans('sql_manager.grid.update_at', domain: 'faq') => $element
                    ->getUpdateAt()->format('d/m/y H:i'),
                GridService::KEY_ACTION => $action,
            ];
        }

        $tabReturn = [
            GridService::KEY_NB => $nb,
            GridService::KEY_DATA => $data,
            GridService::KEY_COLUMN => $column,
            GridService::KEY_RAW_SQL => $this->gridService->getFormatedSQLQuery($dataPaginate)
        ];
        return $this->gridService->addAllDataRequiredGrid($tabReturn);

    }

    /**
     * Retourne une liste de tag paginé
     * @param int $page
     * @param int $limit
     * @param string|null $search
     * @return Paginator
     */
    public function getAllPaginate(int $page, int $limit, string $search = null): Paginator
    {
        $repo = $this->getRepository(SqlManager::class);
        return $repo->getAllPaginate($page, $limit, $search);
    }

    /**
     * Génère le tableau d'action pour le Grid des faqs
     * @param SqlManager $sqlManager
     * @return array[]|string[]
     */
    private function generateTabAction(SqlManager $sqlManager): array
    {
        $actionDisabled = ['label' => '<i class="bi bi-eye-slash-fill"></i>',
            'type' => 'put',
            'url' => $this->router->generate('admin_sql_manager_disabled', ['id' => $sqlManager->getId()]),
            'ajax' => true,
            'confirm' => true,
            'msgConfirm' => $this->translator->trans('sql_manager.confirm.disabled.msg',
                ['label' => $sqlManager->getName()], 'sql_manager')];
        if ($sqlManager->isDisabled()) {
            $actionDisabled = [
                'label' => '<i class="bi bi-eye-fill"></i>',
                'type' => 'put',
                'url' => $this->router->generate('admin_sql_manager_disabled', ['id' => $sqlManager->getId()]),
                'ajax' => true
            ];
        }

        $actionDelete = '';
        if ($this->optionSystemService->canDelete() && !$sqlManager->isDisabled()) {

            $actionDelete = [
                'label' => '<i class="bi bi-trash"></i>',
                'type' => 'delete',
                'url' => $this->router->generate('admin_sql_manager_delete', ['id' => $sqlManager->getId()]),
                'ajax' => true,
                'confirm' => true,
                'msgConfirm' => $this->translator->trans('sql_manager.confirm.delete.msg', ['label' =>
                    $sqlManager->getName()], 'sql_manager')
            ];
        }

        $actions = [];
        $actions[] = $actionDisabled;
        if ($actionDelete != '') {
            $actions[] = $actionDelete;
        }

        if(!$sqlManager->isDisabled()) {
            // Bouton edit
            $actions[] = ['label' => '<i class="bi bi-pencil-fill"></i>',
                'id' => $sqlManager->getId(),
                'url' => $this->router->generate('admin_sql_manager_update', ['id' => $sqlManager->getId()]),
                'ajax' => false];
        }

        return $actions;
    }

}
