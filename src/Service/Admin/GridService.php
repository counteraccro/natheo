<?php

/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Service pour la génération du grid
 */

namespace App\Service\Admin;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use function Symfony\Component\String\u;

class GridService extends AppAdminService
{
    /**
     * Cle pour les actions dans les tableaux grid
     */
    const string KEY_ACTION = 'action';

    /**
     * Clé pour le nombre d'éléments
     */
    const string KEY_NB = 'nb';

    /**
     * Clé pour les données
     */
    const string KEY_DATA = 'data';

    /**
     * Clé pour les colonnes
     */
    const string KEY_COLUMN = 'column';

    /**
     * Clé pour la raw SQL
     */
    const string KEY_RAW_SQL = 'sql';

    /**
     * Clé pour l'url de sauvegarde de la raw sql
     */
    const string KEY_URL_SAVE_RAW_SQL = 'urlSaveSql';

    const string KEY_LIST_ORDER_FIELD = 'listOrderField';

    /**
     * Retourne l'ensemble des données obligatoires pour le grid
     * @param array $tab
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function addAllDataRequiredGrid(array $tab): array
    {
        $router = $this->getRouter();

        $tab[GridService::KEY_URL_SAVE_RAW_SQL] = $router->generate('admin_sql_manager_save_generic_query');
        $tab = $this->addOptionsSelectLimit($tab);
        return $this->addTranslateGrid($tab);
    }

    /**
     * Ajoute le choix des limits dans le tableau de donnée du GRID
     * @param array $tab
     * @return array
     */
    public function addOptionsSelectLimit(array $tab): array
    {
        $tab['listLimit'] = [5 => 5, 10 => 10, 20 => 20, 50 => 50, 100 => 100];
        return $tab;
    }

    /**
     * Génère une requête SQL runnable du grid
     * @param Paginator $paginator
     * @return array|string|string[]
     */
    public function getFormatedSQLQuery(Paginator $paginator): array|string
    {
        $sql = $paginator->getQuery()->getSQL();
        $parameters = $paginator->getQuery()->getParameters()->toArray();

        $values = array_map(fn($p) => $p->getValue(), $parameters);

        $i = 0;
        return preg_replace_callback(
            '/\?/',
            function () use (&$i, $values) {
                $value = $values[$i] ?? '?';
                $i++;
                return "'" . addslashes($value) . "'";
            },
            $sql,
        );
    }

    /**
     * Ajoute les traductions au tableau de donnée du GRID
     * @param array $tab
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function addTranslateGrid(array $tab): array
    {
        $gridTranslate = $this->getGridTranslate();

        $tab['translate'] = $gridTranslate->getTranslate();
        return $tab;
    }

    /**
     * Format le role de symfony en donnée à afficher pour le grid
     * @param string $role
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function renderRole(string $role): string
    {
        $translator = $this->getTranslator();

        $tabRole = [
            'ROLE_USER' => $translator->trans('global.role.user', domain: 'global'),
            'ROLE_CONTRIBUTEUR' => $translator->trans('global.role.contributeur', domain: 'global'),
            'ROLE_ADMIN' => $translator->trans('global.role.admin', domain: 'global'),
            'ROLE_SUPER_ADMIN' => $translator->trans('global.role.superadmin', domain: 'global'),
        ];
        return $tabRole[$role];
    }
}
