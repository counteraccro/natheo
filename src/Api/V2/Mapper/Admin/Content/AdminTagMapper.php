<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Conversion d'un tag en ressource AdminTag
 */

namespace App\Api\V2\Mapper\Admin\Content;

use App\Api\V2\Output\Admin\Global\AdminAuthor;
use App\Api\V2\Output\Admin\Global\AdminLabelTranslation;
use App\Api\V2\Output\Admin\Permissions;
use App\Api\V2\Resource\Admin\Content\AdminTag;
use App\Entity\Admin\Content\Tag\Tag;

final readonly class AdminTagMapper
{
    /**
     * @param string[] $locales langues du site
     * @param string $locale langue de contenu
     * @param string $defaultLocale langue par défaut du site
     */
    public function __construct(private array $locales, private string $locale, private string $defaultLocale) {}

    /**
     * Ressource complète, avec le libellé de chaque langue
     * @param Tag $tag
     * @param int $pageCount
     * @param Permissions $permissions
     * @return AdminTag
     */
    public function map(Tag $tag, int $pageCount, Permissions $permissions): AdminTag
    {
        $label = $tag->getTagTranslationByLocale($this->locale, $this->defaultLocale)?->getLabel();
        $resource = $this->mapSummary($tag, $label, $pageCount, $permissions);
        foreach ($this->locales as $locale) {
            $translation = $tag->getTagTranslations()->findFirst(fn($key, $t) => $t->getLocale() === $locale);
            if ($translation !== null) {
                $resource->translations[] = new AdminLabelTranslation($locale, (string) $translation->getLabel());
            }
        }
        return $resource;
    }

    /**
     * Ressource d'une collection, libellé lu par la requête
     * @param Tag $tag
     * @param string|null $label
     * @param int $pageCount
     * @param Permissions $permissions
     * @return AdminTag
     */
    public function mapSummary(Tag $tag, ?string $label, int $pageCount, Permissions $permissions): AdminTag
    {
        $resource = new AdminTag();
        $resource->id = (int) $tag->getId();
        $resource->label = (string) $label;
        $resource->color = (string) $tag->getColor();
        $resource->disabled = (bool) $tag->isDisabled();
        $resource->pageCount = $pageCount;
        $user = $tag->getUser();
        $resource->author = $user === null ? null : new AdminAuthor((int) $user->getId(), (string) $user->getLogin());
        $resource->createdAt = $tag->getCreatedAt()?->format(\DateTimeInterface::ATOM);
        $resource->updatedAt = $tag->getUpdateAt()?->format(\DateTimeInterface::ATOM);
        $resource->permissions = $permissions;
        return $resource;
    }
}
