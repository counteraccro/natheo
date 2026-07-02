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

    /**
     * Clé option qui permet de sauvegarder ou non les logs
     * @var string
     */
    case OS_LOG_DOCTRINE = 'OS_LOG_DOCTRINE';

    /**
     * Clé option pour récupérer l'expéditeur des emails
     * @var string
     */
    case OS_MAIL_FROM = 'OS_MAIL_FROM';

    /**
     * Clé option pour récupérer l'email de réponse
     * @var string
     */
    case OS_MAIL_REPLY_TO = 'OS_MAIL_REPLY_TO';

    /**
     * Clé option url racine du site
     * @var string
     */
    case OS_ADRESSE_SITE = 'OS_ADRESSE_SITE';

    /**
     * Clé option signature email
     * @var string
     */
    case OS_MAIL_SIGNATURE = 'OS_MAIL_SIGNATURE';

    /**
     * Clé option envoi email
     * @var string
     */
    case OS_MAIL_NOTIFICATION = 'OS_MAIL_NOTIFICATION';

    /**
     * Clé pour l'activation des notifications
     * @var string
     */
    case OS_NOTIFICATION = 'OS_NOTIFICATION';

    /**
     * Clé pour la purge des notifications
     * @var string
     */
    case OS_PURGE_NOTIFICATION = 'OS_PURGE_NOTIFICATION';

    /**
     * Clé pour le temps de validité du lien de changement de mot de passe
     * @var string
     */
    case OS_MAIL_RESET_PASSWORD_TIME = 'OS_MAIL_RESET_PASSWORD_TIME';

    const CONFIG = [
        self::OS_SITE_NAME->value => ['default' => 'Nathéo CMS'],
        self::OS_OPEN_SITE->value => ['default' => '0'],
        self::OS_ADRESSE_SITE->value => ['default' => 'http://www.value-must-be-change.com'],
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
