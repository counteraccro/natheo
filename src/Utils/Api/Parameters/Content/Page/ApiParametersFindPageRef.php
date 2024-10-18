<?php
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Référence pour les paramètres attendus pour l'API find page
 */
namespace App\Utils\Api\Parameters\Content\Page;

class ApiParametersFindPageRef
{
    /**
     * Paramètre slug
     * @var string
     */
    public const PARAM_REF_FIND_PAGE_SLUG = 'slug';

    /**
     * Tableau des paramètres
     * @var array
     */
    public const PARAMS_REF_FIND_PAGE = [
       self::PARAM_REF_FIND_PAGE_SLUG => ''
    ];
}
