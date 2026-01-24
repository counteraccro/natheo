<?php
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Service pour SQLManager
 */
namespace App\Service\Admin\Tools;

use App\Entity\Admin\Tools\SqlManager;
use App\Service\Admin\AppAdminService;
use App\Service\Admin\GridService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class SqlManagerService extends AppAdminService
{
    /**
     * Construit le tableau de donnée à envoyé au tableau GRID
     * @param int $page
     * @param int $limit
     * @param array $queryParams
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAllFormatToGrid(int $page, int $limit, array $queryParams): array
    {
        $translator = $this->getTranslator();
        $gridService = $this->getGridService();

        $column = [
            $translator->trans('sql_manager.grid.id', domain: 'sql_manager'),
            $translator->trans('sql_manager.grid.name', domain: 'sql_manager'),
            $translator->trans('sql_manager.grid.query', domain: 'sql_manager'),
            $translator->trans('sql_manager.grid.update_at', domain: 'sql_manager'),
            GridService::KEY_ACTION,
        ];

        $dataPaginate = $this->getAllPaginate($page, $limit, $queryParams);

        $nb = $dataPaginate->count();
        $data = [];
        foreach ($dataPaginate as $element) {
            /* @var SqlManager $element */

            $action = $this->generateTabAction($element);

            $data[] = [
                $translator->trans('sql_manager.grid.id', domain: 'sql_manager') => $element->getId(),
                $translator->trans('sql_manager.grid.name', domain: 'sql_manager') => $element->getName(),
                $translator->trans('sql_manager.grid.query', domain: 'sql_manager') => $element->getQuery(),
                $translator->trans('sql_manager.grid.update_at', domain: 'sql_manager') => $element
                    ->getUpdateAt()
                    ->format('d/m/y H:i'),
                GridService::KEY_ACTION => $action,
                'isDisabled' => $element->isDisabled(),
            ];
        }

        $tabReturn = [
            GridService::KEY_NB => $nb,
            GridService::KEY_DATA => $data,
            GridService::KEY_COLUMN => $column,
            GridService::KEY_RAW_SQL => $gridService->getFormatedSQLQuery($dataPaginate),
            GridService::KEY_LIST_ORDER_FIELD => [
                'id' => $translator->trans('sql_manager.grid.id', domain: 'sql_manager'),
                'name' => $translator->trans('sql_manager.grid.name', domain: 'sql_manager'),
                'updateAt' => $translator->trans('sql_manager.grid.update_at', domain: 'sql_manager'),
            ],
        ];
        return $gridService->addAllDataRequiredGrid($tabReturn);
    }

    /**
     * Retourne une liste de SqlManager paginé
     * @param int $page
     * @param int $limit
     * @param string|null $search
     * @return Paginator
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAllPaginate(int $page, int $limit, array $queryParams): Paginator
    {
        $repo = $this->getRepository(SqlManager::class);
        return $repo->getAllPaginate($page, $limit, $queryParams);
    }

    /**
     * Génère le tableau d'action pour le Grid des faqs
     * @param SqlManager $sqlManager
     * @return array[]|string[]
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function generateTabAction(SqlManager $sqlManager): array
    {
        $translator = $this->getTranslator();
        $router = $this->getRouter();
        $optionSystemService = $this->getOptionSystemService();

        $actionDisabled = [
            'label' => [
                'M3.933 13.909A4.357 4.357 0 0 1 3 12c0-1 4-6 9-6m7.6 3.8A5.068 5.068 0 0 1 21 12c0 1-3 6-9 6-.314 0-.62-.014-.918-.04M5 19 19 5m-4 7a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
            ],
            'color' => 'primary',
            'type' => 'put',
            'url' => $router->generate('admin_sql_manager_disabled', ['id' => $sqlManager->getId()]),
            'ajax' => true,
            'confirm' => true,
            'msgConfirm' => $translator->trans(
                'sql_manager.confirm.disabled.msg',
                ['label' => $sqlManager->getName()],
                'sql_manager',
            ),
        ];
        if ($sqlManager->isDisabled()) {
            $actionDisabled = [
                'label' => [
                    'M21 12c0 1.2-4.03 6-9 6s-9-4.8-9-6c0-1.2 4.03-6 9-6s9 4.8 9 6Z',
                    'M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
                ],
                'color' => 'primary',
                'type' => 'put',
                'url' => $router->generate('admin_sql_manager_disabled', ['id' => $sqlManager->getId()]),
                'ajax' => true,
            ];
        }

        $actionDelete = '';
        if ($optionSystemService->canDelete() && !$sqlManager->isDisabled()) {
            $actionDelete = [
                'label' => [
                    'M5 7h14m-9 3v8m4-8v8M10 3h4a1 1 0 0 1 1 1v3H9V4a1 1 0 0 1 1-1ZM6 7h12v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7Z',
                ],
                'color' => 'danger',
                'type' => 'delete',
                'url' => $router->generate('admin_sql_manager_delete', ['id' => $sqlManager->getId()]),
                'ajax' => true,
                'confirm' => true,
                'msgConfirm' => $translator->trans(
                    'sql_manager.confirm.delete.msg',
                    ['label' => $sqlManager->getName()],
                    'sql_manager',
                ),
            ];
        }

        $actions = [];
        $actions[] = $actionDisabled;
        if ($actionDelete != '') {
            $actions[] = $actionDelete;
        }

        if (!$sqlManager->isDisabled()) {
            // Bouton edit
            $actions[] = [
                'label' => [
                    'M10.779 17.779 4.36 19.918 6.5 13.5m4.279 4.279 8.364-8.643a3.027 3.027 0 0 0-2.14-5.165 3.03 3.03 0 0 0-2.14.886L6.5 13.5m4.279 4.279L6.499 13.5m2.14 2.14 6.213-6.504M12.75 7.04 17 11.28',
                ],
                'color' => 'primary',
                'id' => $sqlManager->getId(),
                'url' => $router->generate('admin_sql_manager_update', ['id' => $sqlManager->getId()]),
                'ajax' => false,
            ];

            $actions[] = [
                'label' => [
                    'm8 9 3 3-3 3m5 0h3M4 19h16a1 1 0 0 0 1-1V6a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1Z',
                ],
                'color' => 'success',
                'id' => $sqlManager->getId(),
                'url' => $router->generate('admin_sql_manager_execute', [
                    'id' => $sqlManager->getId(),
                    'isExecute' => true,
                ]),
                'ajax' => false,
            ];
        }

        return $actions;
    }

    /**
     * Vérifie si la query est uniquement un select
     * retourne true si c'est vrai, false sinon
     * @param $query
     * @return bool
     */
    public function isOnlySelectQuery($query): bool
    {
        $arrayWords = ['UPDATE', 'DELETE', 'INSERT', 'CREATE', 'ALTER', 'DROP', 'TRUNCATE'];

        $pattern = '/(' . strtolower(implode('|', $arrayWords)) . ')\b/i';
        if (preg_match($pattern, strtolower($query)) !== 0) {
            return false;
        }

        return true;
    }
}
