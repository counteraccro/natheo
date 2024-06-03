<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
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
     * @param string|null $search
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAllFormatToGrid(int $page, int $limit, string $search = null): array
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
                $translator->trans('sql_manager.grid.id', domain: 'sql_manager') =>
                    $element->getId() . ' ' . $isDisabled,
                $translator->trans('sql_manager.grid.name', domain: 'sql_manager') => $element->getName(),
                $translator->trans('sql_manager.grid.query', domain: 'sql_manager') => $element->getQuery(),
                $translator->trans('sql_manager.grid.update_at', domain: 'sql_manager') => $element
                    ->getUpdateAt()->format('d/m/y H:i'),
                GridService::KEY_ACTION => $action,
            ];
        }

        $tabReturn = [
            GridService::KEY_NB => $nb,
            GridService::KEY_DATA => $data,
            GridService::KEY_COLUMN => $column,
            GridService::KEY_RAW_SQL => $gridService->getFormatedSQLQuery($dataPaginate),
        ];
        return $gridService->addAllDataRequiredGrid($tabReturn);

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
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function generateTabAction(SqlManager $sqlManager): array
    {
        $translator = $this->getTranslator();
        $router = $this->getRouter();
        $optionSystemService = $this->getOptionSystemService();

        $actionDisabled = ['label' => '<i class="bi bi-eye-slash-fill"></i>',
            'type' => 'put',
            'url' => $router->generate('admin_sql_manager_disabled', ['id' => $sqlManager->getId()]),
            'ajax' => true,
            'confirm' => true,
            'msgConfirm' => $translator->trans('sql_manager.confirm.disabled.msg',
                ['label' => $sqlManager->getName()], 'sql_manager')];
        if ($sqlManager->isDisabled()) {
            $actionDisabled = [
                'label' => '<i class="bi bi-eye-fill"></i>',
                'type' => 'put',
                'url' => $router->generate('admin_sql_manager_disabled', ['id' => $sqlManager->getId()]),
                'ajax' => true
            ];
        }

        $actionDelete = '';
        if ($optionSystemService->canDelete() && !$sqlManager->isDisabled()) {

            $actionDelete = [
                'label' => '<i class="bi bi-trash"></i>',
                'type' => 'delete',
                'url' => $router->generate('admin_sql_manager_delete', ['id' => $sqlManager->getId()]),
                'ajax' => true,
                'confirm' => true,
                'msgConfirm' => $translator->trans('sql_manager.confirm.delete.msg', ['label' =>
                    $sqlManager->getName()], 'sql_manager')
            ];
        }

        $actions = [];
        $actions[] = $actionDisabled;
        if ($actionDelete != '') {
            $actions[] = $actionDelete;
        }

        if (!$sqlManager->isDisabled()) {
            // Bouton edit
            $actions[] = ['label' => '<i class="bi bi-pencil-fill"></i>',
                'id' => $sqlManager->getId(),
                'url' => $router->generate('admin_sql_manager_update', ['id' => $sqlManager->getId()]),
                'ajax' => false];

            $actions[] = ['label' => '<i class="bi bi-database-fill-check"></i>',
                'id' => $sqlManager->getId(),
                'url' => $router->generate('admin_sql_manager_execute', ['id' => $sqlManager->getId(),
                    'isExecute' => true]),
                'ajax' => false];
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
        $arrayWords = [
            'UPDATE', 'DELETE', 'INSERT',
            'CREATE', 'ALTER', 'DROP',
        ];

        $pattern = '/(' . implode('|', $arrayWords) . ')\b/i';
        if (preg_match($pattern, $query) !== 0) {
            return false;
        }

        return true;
    }


}
