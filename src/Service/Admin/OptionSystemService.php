<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Service lier aux options système
 */
namespace App\Service\Admin;

class OptionSystemService extends AppAdminService
{
    /**
     * Clé option nom du site
     * @var string
     */
    const OS_SITE_NAME = 'OS_SITE_NAME';

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
}