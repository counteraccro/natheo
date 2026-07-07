<?php

declare(strict_types=1);
/**
 * Liste des clés pour les options User
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Admin\System\Options;

enum OptionUser: string
{
    /**
     * Clé option langue pour le user
     * @var string
     */
    case OU_DEFAULT_LANGUAGE = 'OU_DEFAULT_LANGUAGE';

    /**
     * Clé option theme du site pour le user
     * @var string
     */
    case OU_THEME_SITE = 'OU_THEME_SITE';

    /**
     * Clé option nb éléments pour les users
     * @var string
     */
    case OU_NB_ELEMENT = 'OU_NB_ELEMENT';

    const CONFIG = [];

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
