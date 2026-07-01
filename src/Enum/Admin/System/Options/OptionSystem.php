<?php

declare(strict_types=1);
/**
 * Liste des clés pour les options Systèmes
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Admin\System\Options;

enum OptionSystem: string
{
    /**
     * Clé option nom du site
     */
    case OS_SITE_NAME = 'OS_SITE_NAME';

    /**
     * Clé option theme du site
     */
    case OS_THEME_SITE = 'OS_THEME_SITE';

    /**
     * Clé pour le logo du site
     */
    case OS_LOGO_SITE = 'OS_LOGO_SITE';

    /**
     * Clé option site ouvert
     * @var string
     */
    case OS_OPEN_SITE = 'OS_OPEN_SITE';

    /**
     * Clé option script header
     * @var string
     */
    case OS_FRONT_SCRIPT_TOP = 'OS_FRONT_SCRIPT_TOP';

    /**
     * Clé option script début body
     * @var string
     */
    case OS_FRONT_SCRIPT_START_BODY = 'OS_FRONT_SCRIPT_START_BODY';

    /**
     * Clé option script fin body
     * @var string
     */
    case OS_FRONT_SCRIPT_END_BODY = 'OS_FRONT_SCRIPT_END_BODY';

    /**
     * Clé option remplacement user delete
     * @var string
     */
    case OS_REPLACE_DELETE_USER = 'OS_REPLACE_DELETE_USER';

    /**
     * Clé option confirmation quitter form
     * @var string
     */
    case OS_CONFIRM_LEAVE_FORM = 'OS_CONFIRM_LEAVE_FORM';

    /**
     * Clé option authorisation suppression de données
     * @var string
     */
    case OS_ALLOW_DELETE_DATA = 'OS_ALLOW_DELETE_DATA';

    /**
     * Clé option langue par défaut pour le site
     * @var string
     */
    case OS_DEFAULT_LANGUAGE = 'OS_DEFAULT_LANGUAGE';

    /**
     * Clé option nb élements par défaut pour les users
     * @var string
     */
    case OS_NB_ELEMENT = 'OS_NB_ELEMENT';

    const CONFIG = [
        self::OS_SITE_NAME->value => ['default' => 'Nathéo CMS'],
        self::OS_OPEN_SITE->value => ['default' => '0'],
    ];

    /**
     * Récupère la valeur par défaut
     * @return string|null
     */
    public function getDefault(): ?string
    {
        if (isset(self::CONFIG[$this->value]['default'])) {
            return self::CONFIG[$this->value]['default'];
        }
        return null;
    }
}
