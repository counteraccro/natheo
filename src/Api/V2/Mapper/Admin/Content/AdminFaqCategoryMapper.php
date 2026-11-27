<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Conversion d'une catégorie de FAQ en ressource AdminFaqCategory
 */

namespace App\Api\V2\Mapper\Admin\Content;

use App\Api\V2\Mapper\Admin\AdminTranslationMapper;
use App\Api\V2\Output\Admin\Global\AdminTitleTranslation;
use App\Api\V2\Output\Admin\Global\Permissions;
use App\Api\V2\Resource\Admin\Content\AdminFaq;
use App\Api\V2\Resource\Admin\Content\AdminFaqCategory;
use App\Api\V2\Resource\Admin\Content\AdminFaqQuestion;
use App\Entity\Admin\Content\Faq\FaqCategory;

final readonly class AdminFaqCategoryMapper
{
    /**
     * @param string[] $locales langues du site
     * @param string $locale langue de contenu
     * @param string $defaultLocale langue par défaut du site
     */
    public function __construct(private array $locales, private string $locale, private string $defaultLocale) {}

    /**
     * Ressource complète, avec le titre de chaque langue
     * @param FaqCategory $category
     * @param Permissions $permissions droits de la FAQ
     * @return AdminFaqCategory
     */
    public function map(FaqCategory $category, Permissions $permissions): AdminFaqCategory
    {
        $resource = $this->mapTreeItem($category, []);
        $resource->faq = new AdminFaq();
        $resource->faq->id = (int) $category->getFaq()?->getId();
        $resource->permissions = $permissions;
        return $resource;
    }

    /**
     * Identité de la catégorie et de sa FAQ, pour une IRI reçue dans un corps de requête
     * @param int $id
     * @param int $faqId
     * @return AdminFaqCategory
     */
    public function mapSummary(int $id, int $faqId): AdminFaqCategory
    {
        $faq = new AdminFaq();
        $faq->id = $faqId;
        $resource = new AdminFaqCategory();
        $resource->id = $id;
        $resource->faq = $faq;
        return $resource;
    }

    /**
     * Catégorie de l'arbre d'une FAQ
     * @param FaqCategory $category
     * @param AdminFaqQuestion[] $questions questions triées de la catégorie
     * @return AdminFaqCategory
     */
    public function mapTreeItem(FaqCategory $category, array $questions): AdminFaqCategory
    {
        $resource = new AdminFaqCategory();
        $resource->id = (int) $category->getId();
        $resource->position = (int) $category->getRenderOrder();
        $resource->disabled = (bool) $category->isDisabled();
        $resource->title = (string) $category
            ->getFaqCategoryTranslationByLocale($this->locale, $this->defaultLocale)
            ?->getTitle();
        $resource->translations = AdminTranslationMapper::inSiteOrder(
            $this->locales,
            $category->getFaqCategoryTranslations(),
            fn($translation, string $locale) => new AdminTitleTranslation($locale, (string) $translation->getTitle()),
        );
        $resource->questions = $questions;
        return $resource;
    }
}
