<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Conversion d'une question de FAQ en ressource AdminFaqQuestion
 */

namespace App\Api\V2\Mapper\Admin\Content;

use App\Api\V2\Mapper\Admin\AdminTranslationMapper;
use App\Api\V2\Output\Admin\Content\AdminFaqQuestionTranslation;
use App\Api\V2\Output\Admin\Global\Permissions;
use App\Api\V2\Resource\Admin\Content\AdminFaq;
use App\Api\V2\Resource\Admin\Content\AdminFaqCategory;
use App\Api\V2\Resource\Admin\Content\AdminFaqQuestion;
use App\Entity\Admin\Content\Faq\FaqQuestion;

final readonly class AdminFaqQuestionMapper
{
    /**
     * @param string[] $locales langues du site
     * @param string $locale langue de contenu
     * @param string $defaultLocale langue par défaut du site
     */
    public function __construct(private array $locales, private string $locale, private string $defaultLocale) {}

    /**
     * Ressource complète, avec le titre et la réponse de chaque langue
     * @param FaqQuestion $question
     * @param Permissions $permissions droits de la FAQ
     * @return AdminFaqQuestion
     */
    public function map(FaqQuestion $question, Permissions $permissions): AdminFaqQuestion
    {
        $category = $question->getFaqCategory();
        $resource = $this->mapSummary(
            (int) $question->getId(),
            (int) $category?->getId(),
            (int) $category?->getFaq()?->getId(),
        );
        $resource->position = (int) $question->getRenderOrder();
        $resource->disabled = (bool) $question->isDisabled();
        $resource->title = (string) $question
            ->getFaqQuestionTranslationByLocale($this->locale, $this->defaultLocale)
            ?->getTitle();
        $resource->permissions = $permissions;
        $resource->translations = AdminTranslationMapper::inSiteOrder(
            $this->locales,
            $question->getFaqQuestionTranslations(),
            fn($translation, string $locale) => new AdminFaqQuestionTranslation(
                $locale,
                (string) $translation->getTitle(),
                (string) $translation->getAnswer(),
            ),
        );
        return $resource;
    }

    /**
     * Identité de la question, de sa catégorie et de sa FAQ
     * @param int $id
     * @param int $categoryId
     * @param int $faqId
     * @return AdminFaqQuestion
     */
    public function mapSummary(int $id, int $categoryId, int $faqId): AdminFaqQuestion
    {
        $faq = new AdminFaq();
        $faq->id = $faqId;
        $category = new AdminFaqCategory();
        $category->id = $categoryId;
        $category->faq = $faq;

        $resource = new AdminFaqQuestion();
        $resource->id = $id;
        $resource->faq = $faq;
        $resource->category = $category;
        return $resource;
    }

    /**
     * Question de l'arbre d'une FAQ, lue par FaqQuestionRepository::getAdminTreeRows()
     * @param array{id: int, position: int, disabled: bool, title: ?string} $row
     * @return AdminFaqQuestion
     */
    public function mapTreeRow(array $row): AdminFaqQuestion
    {
        $resource = new AdminFaqQuestion();
        $resource->id = (int) $row['id'];
        $resource->position = (int) $row['position'];
        $resource->disabled = (bool) $row['disabled'];
        $resource->title = (string) $row['title'];
        return $resource;
    }
}
