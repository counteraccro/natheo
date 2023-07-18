<?php
/**
 * Liste des clés pour les options Systèmes
 * @author Gourdon Aymeric
 * @version 1.0
 */
namespace App\Utils\System\Options;

class OptionSystemKey
{
    /**
     * Clé option nom du site
     * @var string
     */
    const OS_SITE_NAME = 'OS_SITE_NAME';

    /**
     * Clé option theme du site
     * @var string
     */
    const OS_THEME_SITE = 'OS_THEME_SITE';

    /**
     * Clé pour le logo du site
     */
    const OS_LOGO_SITE = 'OS_LOGO_SITE';

    /**
     * Clé option site ouvert
     * @var string
     */
    const OS_OPEN_SITE = 'OS_OPEN_SITE';

    /**
     * Clé option script header
     * @var string
     */
    const OS_FRONT_SCRIPT_TOP = 'OS_FRONT_SCRIPT_TOP';

    /**
     * Clé option script début body
     * @var string
     */
    const OS_FRONT_SCRIPT_START_BODY = 'OS_FRONT_SCRIPT_START_BODY';

    /**
     * Clé option script fin body
     * @var string
     */
    const OS_FRONT_SCRIPT_END_BODY = 'OS_FRONT_SCRIPT_END_BODY';

    /**
     * Clé option remplacement user delete
     * @var string
     */
    const OS_REPLACE_DELETE_USER = 'OS_REPLACE_DELETE_USER';

    /**
     * Clé option confirmation quitter form
     * @var string
     */
    const OS_CONFIRM_LEAVE_FORM = 'OS_CONFIRM_LEAVE_FORM';

    /**
     * Clé option authorisation suppression de données
     * @var string
     */
    const OS_ALLOW_DELETE_DATA = 'OS_ALLOW_DELETE_DATA';

    /**
     * Clé option langue par défaut pour le site
     * @var string
     */
    const OS_DEFAULT_LANGUAGE = 'OS_DEFAULT_LANGUAGE';

    /**
     * Clé option nb élements par défaut pour les users
     * @var string
     */
    const OS_NB_ELEMENT = 'OS_NB_ELEMENT';

    /**
     * Clé option qui permet de sauvegarder ou non les logs
     * @var string
     */
    const OS_LOG_DOCTRINE = 'OS_LOG_DOCTRINE';

    /**
     * Clé option pour récupérer l'expéditeur des emails
     * @var string
     */
    const OS_MAIL_FROM = 'OS_MAIL_FROM';

    /**
     * Clé option pour récupérer l'email de réponse
     * @var string
     */
    const OS_MAIL_REPLY_TO = 'OS_MAIL_REPLY_TO';

    /**
     * Clé option url racine du site
     * @var string
     */
    const OS_ADRESSE_SITE = 'OS_ADRESSE_SITE';

    /**
     * Clé option signature email
     * @var string
     */
    const OS_MAIL_SIGNATURE = 'OS_MAIL_SIGNATURE';

    /**
     * Clé option envoi email
     * @var string
     */
    const OS_MAIL_NOTIFICATION = 'OS_MAIL_NOTIFICATION';

    /**
     * Clé pour l'activation des notifications
     * @var string
     */
    const OS_NOTIFICATION = 'OS_NOTIFICATION';

    /**
     * Clé pour la purge des notifications
     * @var string
     */
    const OS_PURGE_NOTIFICATION = 'OS_PURGE_NOTIFICATION';

    /**
     * Clé pour le temps de validité du lien de changement de mot de passe
     * @var string
     */
    const OS_MAIL_RESET_PASSWORD_TIME = 'OS_MAIL_RESET_PASSWORD_TIME';

    /**
     * Défini si on créé des dossiers physiques ou non pour la médiathèque
     * @var string
     */
    const OS_MEDIA_CREATE_PHYSICAL_FOLDER = 'OS_MEDIA_CREATE_PHYSICAL_FOLDER';

    /**
     * Path du dossier média
     * @var string
     */
    const OS_MEDIA_PATH = 'OS_MEDIA_PATH';

    /**
     * URL public d'accès au média
     */
    const OS_MEDIA_URL = 'OS_MEDIA_URL';
}
