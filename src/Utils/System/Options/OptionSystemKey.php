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
     * Défini si on créé des dossiers physiques ou non pour la médiathèque
     * @var string
     */
    const string OS_MEDIA_CREATE_PHYSICAL_FOLDER = 'OS_MEDIA_CREATE_PHYSICAL_FOLDER';

    /**
     * Path du dossier média
     * @var string
     */
    const string OS_MEDIA_PATH = 'OS_MEDIA_PATH';

    /**
     * URL public d'accès au média
     * @var string
     */
    const string OS_MEDIA_URL = 'OS_MEDIA_URL';

    /**
     * Temps de validité du token user
     * @var string
     */
    const string OS_API_TIME_VALIDATE_USER_TOKEN = 'OS_API_TIME_VALIDATE_USER_TOKEN';

    /**
     * Ouverture des commentaires
     * @var string
     */
    const string OS_OPEN_COMMENT = 'OS_OPEN_COMMENT';

    /**
     * Mettre le status des nouveaux commentaires en attente de validation
     * @var string
     */
    const string OS_NEW_COMMENT_WAIT_VALIDATION = 'OS_NEW_COMMENT_WAIT_VALIDATION';

    /**
     * Clé option theme du front site
     * @var string
     */
    const string OS_THEME_FRONT_SITE = 'OS_THEME_FRONT_SITE';

    /**
     * Clé option front, texte en bas du footer
     * @var string
     */
    const string OS_FRONT_FOOTER_TEXTE = 'OS_FRONT_FOOTER_TEXTE';

    /**
     * Clé option front, url réseau social GITHUB
     * @var string
     */
    const string OS_FRONT_FOOTER_SOCIAL_GITHUB_URL = 'OS_FRONT_FOOTER_SOCIAL_GITHUB_URL';

    /**
     * Clé option front, url réseau social Linkedin
     * @var string
     */
    const string OS_FRONT_FOOTER_SOCIAL_LINKEDIN_URL = 'OS_FRONT_FOOTER_SOCIAL_LINKEDIN_URL';

    /**
     * Clé option front, url réseau social Youtube
     * @var string
     */
    const string OS_FRONT_FOOTER_SOCIAL_YOUTUBE_URL = 'OS_FRONT_FOOTER_SOCIAL_YOUTUBE_URL';

    /**
     * Clé option front, url réseau social X
     * @var string
     */
    const string OS_FRONT_FOOTER_SOCIAL_X_URL = 'OS_FRONT_FOOTER_SOCIAL_X_URL';

    /**
     * Clé option front, url réseau social facebook
     * @var string
     */
    const string OS_FRONT_FOOTER_SOCIAL_FACEBOOK_URL = 'OS_FRONT_FOOTER_SOCIAL_FACEBOOK_URL';

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
