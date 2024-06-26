<?php
/**
 * Liste des clés pour les options User
 * @author Gourdon Aymeric
 * @version 1.0
 */
namespace App\Utils\System\Options;

class OptionUserKey
{
    /**
     * Clé option langue pour le user
     * @var string
     */
    const OU_DEFAULT_LANGUAGE = 'OU_DEFAULT_LANGUAGE';

    /**
     * Clé option theme du site pour le user
     * @var string
     */
    const OU_THEME_SITE = 'OU_THEME_SITE';

    /**
     * Clé option nb éléments pour les users
     * @var string
     */
    const OU_NB_ELEMENT = 'OU_NB_ELEMENT';

    /**
     * Clé option pour l'affichage des données personnelles
     * @var string
     */
    const OU_DEFAULT_PERSONAL_DATA_RENDER = 'OU_DEFAULT_PERSONAL_DATA_RENDER';
}
