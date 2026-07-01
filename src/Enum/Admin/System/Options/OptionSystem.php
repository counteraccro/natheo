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
