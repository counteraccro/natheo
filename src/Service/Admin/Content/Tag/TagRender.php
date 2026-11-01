<?php

declare(strict_types=1);
/**
 * Génère l'affichage HTML d'un tag en fonction de la locale
 * @author Gourdon Aymeric
 * @version 2.0
 */

namespace App\Service\Admin\Content\Tag;

use App\Entity\Admin\Content\Tag\Tag;

class TagRender
{
    /**
     * Couleur utilisée si la couleur enregistrée est invalide
     * @var string
     */
    public const string DEFAULT_COLOR = '#6c757d';

    /**
     * Génère le format HTML d'un tag, libellé échappé et couleur validée
     * @param Tag $tag
     * @param string $locale
     * @return string
     */
    public function getHtml(Tag $tag, string $locale): string
    {
        $label = (string) $tag->getTagTranslationByLocale($locale)?->getLabel();

        return '<span class="badge rounded-pill badge-nat" style="background-color: ' .
            $this->getSafeColor($tag->getColor()) .
            '">' .
            htmlspecialchars($label, ENT_QUOTES) .
            '</span>';
    }

    /**
     * Retourne la couleur si elle respecte le format #rrggbb, sinon la couleur par défaut
     * @param string|null $color
     * @return string
     */
    public function getSafeColor(?string $color): string
    {
        if ($color !== null && preg_match(Tag::COLOR_PATTERN, $color) === 1) {
            return $color;
        }
        return self::DEFAULT_COLOR;
    }
}
