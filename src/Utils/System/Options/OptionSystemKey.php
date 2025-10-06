<?php
/**
 * Liste des clés pour les options Systèmes
 * @author Gourdon Aymeric
 * @version 1.2
 */
namespace App\Utils\System\Options;

class OptionSystemKey
{
    /**
     * Clé option nom du site
     * @var string
     */
    const string OS_SITE_NAME = 'OS_SITE_NAME';

    /**
     * Valeur par défaut défini pour OS_SITE_NAME
     * @var string
     */
    const string OS_SITE_NAME_DEFAULT_VALUE = 'Nathéo CMS';

    /**
     * Clé option theme du site
     * @var string
     */
    const string OS_THEME_SITE = 'OS_THEME_SITE';

    /**
     * Clé pour le logo du site
     */
    const string OS_LOGO_SITE = 'OS_LOGO_SITE';

    /**
     * Clé option site ouvert
     * @var string
     */
    const string OS_OPEN_SITE = 'OS_OPEN_SITE';

    /**
     * Valeur par défaut de la clé OS_OPEN_SITE
     * @var string
     */
    const string OS_OPEN_SITE_DEFAULT_VALUE = '0';

    /**
     * Clé option script header
     * @var string
     */
    const string OS_FRONT_SCRIPT_TOP = 'OS_FRONT_SCRIPT_TOP';

    /**
     * Clé option script début body
     * @var string
     */
    const string OS_FRONT_SCRIPT_START_BODY = 'OS_FRONT_SCRIPT_START_BODY';

    /**
     * Clé option script fin body
     * @var string
     */
    const string OS_FRONT_SCRIPT_END_BODY = 'OS_FRONT_SCRIPT_END_BODY';

    /**
     * Clé option remplacement user delete
     * @var string
     */
    const string OS_REPLACE_DELETE_USER = 'OS_REPLACE_DELETE_USER';

    /**
     * Clé option confirmation quitter form
     * @var string
     */
    const string OS_CONFIRM_LEAVE_FORM = 'OS_CONFIRM_LEAVE_FORM';

    /**
     * Clé option authorisation suppression de données
     * @var string
     */
    const string OS_ALLOW_DELETE_DATA = 'OS_ALLOW_DELETE_DATA';

    /**
     * Clé option langue par défaut pour le site
     * @var string
     */
    const string OS_DEFAULT_LANGUAGE = 'OS_DEFAULT_LANGUAGE';

    /**
     * Clé option nb élements par défaut pour les users
     * @var string
     */
    const string OS_NB_ELEMENT = 'OS_NB_ELEMENT';

    /**
     * Clé option qui permet de sauvegarder ou non les logs
     * @var string
     */
    const string OS_LOG_DOCTRINE = 'OS_LOG_DOCTRINE';

    /**
     * Clé option pour récupérer l'expéditeur des emails
     * @var string
     */
    const string OS_MAIL_FROM = 'OS_MAIL_FROM';

    /**
     * Clé option pour récupérer l'email de réponse
     * @var string
     */
    const string OS_MAIL_REPLY_TO = 'OS_MAIL_REPLY_TO';

    /**
     * Clé option url racine du site
     * @var string
     */
    const string OS_ADRESSE_SITE = 'OS_ADRESSE_SITE';

    /**
     * Valeur par défaut pour la clé OS_ADRESSE_SITE
     * @var string
     */
    const string OS_ADRESSE_SITE_DEFAULT_VALUE = 'http://www.value-must-be-change.com';

    /**
     * Clé option signature email
     * @var string
     */
    const string OS_MAIL_SIGNATURE = 'OS_MAIL_SIGNATURE';

    /**
     * Clé option envoi email
     * @var string
     */
    const string OS_MAIL_NOTIFICATION = 'OS_MAIL_NOTIFICATION';

    /**
     * Clé pour l'activation des notifications
     * @var string
     */
    const string OS_NOTIFICATION = 'OS_NOTIFICATION';

    /**
     * Clé pour la purge des notifications
     * @var string
     */
    const string OS_PURGE_NOTIFICATION = 'OS_PURGE_NOTIFICATION';

    /**
     * Clé pour le temps de validité du lien de changement de mot de passe
     * @var string
     */
    const string OS_MAIL_RESET_PASSWORD_TIME = 'OS_MAIL_RESET_PASSWORD_TIME';

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
