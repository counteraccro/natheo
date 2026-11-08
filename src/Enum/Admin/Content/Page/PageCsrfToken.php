<?php

declare(strict_types=1);
/**
 * Identifiants des jetons CSRF des actions d'écriture sur les pages
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Admin\Content\Page;

enum PageCsrfToken: string
{
    /**
     * Suppression d'une page
     * @var string
     */
    case DELETE = 'page_delete';

    /**
     * Activation / désactivation d'une page
     * @var string
     */
    case DISABLED = 'page_update_disabled';

    /**
     * Changement de landing page
     * @var string
     */
    case LANDING_PAGE = 'page_switch_landing_page';

    /**
     * Sauvegarde d'une page
     * @var string
     */
    case SAVE = 'page_save';

    /**
     * Sauvegarde automatique (historique)
     * @var string
     */
    case AUTO_SAVE = 'page_auto_save';

    /**
     * Publication du brouillon d'une page publiée
     * @var string
     */
    case PUBLISH_DRAFT = 'page_publish_draft';

    /**
     * Abandon du brouillon d'une page publiée
     * @var string
     */
    case DISCARD_DRAFT = 'page_discard_draft';

    /**
     * Archivage d'une page
     * @var string
     */
    case ARCHIVE = 'page_archive';

    /**
     * Soumission d'une page à validation
     * @var string
     */
    case SUBMIT_VALIDATION = 'page_submit_validation';

    /**
     * Validation d'une page soumise
     * @var string
     */
    case APPROVE_VALIDATION = 'page_approve_validation';

    /**
     * Refus d'une page soumise
     * @var string
     */
    case REFUSE_VALIDATION = 'page_refuse_validation';

    /**
     * Retrait d'une soumission à validation
     * @var string
     */
    case WITHDRAW_VALIDATION = 'page_withdraw_validation';
}
