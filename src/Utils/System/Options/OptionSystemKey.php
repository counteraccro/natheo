<?php

declare(strict_types=1);
/**
 * Liste des clés pour les options Systèmes
 * @author Gourdon Aymeric
 * @version 1.2
 */
namespace App\Utils\System\Options;

class OptionSystemKey
{
    /**
     * Clé option front, url réseau social instagram
     * @var string
     */
    const string OS_FRONT_FOOTER_SOCIAL_INSTAGRAM_URL = 'OS_FRONT_FOOTER_SOCIAL_INSTAGRAM_URL';

    /**
     * Clé option front, url réseau social tiktok
     * @var string
     */
    const string OS_FRONT_FOOTER_SOCIAL_TIKTOK_URL = 'OS_FRONT_FOOTER_SOCIAL_TIKTOK_URL';

    /**
     * Clé option front, pas d'indexation pour les robots
     */
    const string OS_FRONT_ROBOT_NO_INDEX = 'OS_FRONT_ROBOT_NO_INDEX';

    /**
     * Clé option front, pas de suivi des liens
     */
    const string OS_FRONT_ROBOT_NO_FOLLOW = 'OS_FRONT_ROBOT_NO_FOLLOW';
}
