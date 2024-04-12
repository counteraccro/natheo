<?php

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Service pour la génération du grid
 */

namespace App\Service\Admin;

use App\Utils\Translate\GridTranslate;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class GridService extends AppAdminService
{
    /**
     * Cle pour les actions dans les tableaux grid
     */
    const KEY_ACTION = 'action';

    /**
     * Clé pour le nombre d'éléments
     */
    const KEY_NB = 'nb';

    /**
     * Clé pour les données
     */
    const KEY_DATA = 'data';

    /**
     * Clé pour les colonnes
     */
    const KEY_COLUMN = 'column';

    /**
     * Clé pour la raw SQL
     */
    const KEY_RAW_SQL = 'sql';

    private GridTranslate $gridTranslate;

    public function __construct(#[AutowireLocator([
        'logger' => LoggerInterface::class,
        'entityManager' => EntityManagerInterface::class,
        'containerBag' => ContainerBagInterface::class,
        'translator' => TranslatorInterface::class,
        'router' => UrlGeneratorInterface::class,
        'security' => Security::class,
        'requestStack' => RequestStack::class,
        'parameterBag' => ParameterBagInterface::class,
        'gridTranslate' => GridTranslate::class
    ])] private readonly ContainerInterface $handlers)
    {
        $this->gridTranslate = $this->handlers->get('gridTranslate');
        parent::__construct($this->handlers);
    }

    /**
     * Retourne l'ensemble des données obligatoires pour le grid
     * @param array $tab
     * @return array
     */
    public function addAllDataRequiredGrid(array $tab): array
    {
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
        $parameters = $paginator->getQuery()->getParameters();
        foreach ($parameters as $parameter) {
            $sql = str_replace('?', "'" . $parameter->getValue() . "'", $sql);
        }
        return $sql;

    }

    /**
     * Ajoute les traductions au tableau de donnée du GRID
     * @param array $tab
     * @return array
     */
    public function addTranslateGrid(array $tab): array
    {
        $tab['translate'] = $this->gridTranslate->getTranslate();
        return $tab;
    }

    /**
     * Format le role de symfony en donnée à afficher pour le grid
     * @param string $role
     * @return string
     */
    public function renderRole(string $role): string
    {
        $tabRole = [
            'ROLE_USER' => $this->translator->trans('global.role.user', domain: 'global'),
            'ROLE_CONTRIBUTEUR' => $this->translator->trans('global.role.contributeur', domain: 'global'),
            'ROLE_ADMIN' => $this->translator->trans('global.role.admin', domain: 'global'),
            'ROLE_SUPER_ADMIN' => $this->translator->trans('global.role.superadmin', domain: 'global')
        ];
        return $tabRole[$role];
    }
}
