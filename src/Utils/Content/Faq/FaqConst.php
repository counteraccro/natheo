<?php
/**
 * Constantes pour les FAQ
 * @author Gourdon Aymeric
 * @version 1.0
 */
namespace App\Utils\Content\Faq;

class FaqConst
{
    /**
     * Type catégorie
     * @var string
     */
    const TYPE_CATEGORY = 'category';

    /**
     * Type question
     * @var string
     */
    const TYPE_QUESTION = 'question';

    /**
     * Action addition sur les statistiques
     * @var string
     */
    const STATISTIQUE_ACTION_ADD = 'add';

    /**
     * Action soustraction sur les statistiques
     * @var string
     */
    const STATISTIQUE_ACTION_SUB = 'sub';

    /**
     * Action soustraction sur les statistiques
     * @var string
     */
    const STATISTIQUE_ACTION_OVERWRITE = 'overwrite';
}
