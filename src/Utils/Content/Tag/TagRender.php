<?php
/**
 * Permet de générer l'affichage d'un Tag en fonction de la locale
 * @author Gourdon Aymeric
 * @version 1.0
 */
namespace App\Utils\Content\Tag;

use App\Entity\Admin\Content\Tag\Tag;

class TagRender
{
    private Tag $tag;

    private string $locale;

    /**
     * @param Tag $tag
     * @param $locale
     */
    public function __construct(Tag $tag, $locale)
    {
        $this->tag = $tag;
        $this->locale = $locale;
    }

    /**
     * Génère le format HTML d'un tag
     * @return string
     */
    public function getHtml(): string
    {
        $label = $this->tag->getTagTranslationByLocale($this->locale)->getLabel();
        return '<span class="badge rounded-pill badge-nat"
                    style="background-color: ' .
            $this->tag->getColor() .
            '">' .
            $label .
            '</span>';
    }
}
