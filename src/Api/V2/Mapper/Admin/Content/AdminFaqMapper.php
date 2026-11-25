<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Conversion d'une FAQ en ressource AdminFaq
 */

namespace App\Api\V2\Mapper\Admin\Content;

use App\Api\V2\Mapper\Admin\AdminTranslationMapper;
use App\Api\V2\Output\Admin\Global\AdminAuthor;
use App\Api\V2\Output\Admin\Global\AdminTitleTranslation;
use App\Api\V2\Output\Admin\Global\Permissions;
use App\Api\V2\Resource\Admin\Content\AdminFaq;
use App\Entity\Admin\Content\Faq\Faq;
use App\Entity\Admin\Content\Faq\FaqCategory;

final readonly class AdminFaqMapper
{
    /**
     * @param string[] $locales langues du site
     * @param string $locale langue de contenu
     * @param string $defaultLocale langue par défaut du site
     */
    public function __construct(private array $locales, private string $locale, private string $defaultLocale) {}

    /**
     * Ressource complète, avec le titre de chaque langue et l'arbre
     * @param Faq $faq
     * @param FaqCategory[] $categories catégories triées, traductions chargées
     * @param array<int, array{id: int, categoryId: int, position: int, disabled: bool, title: ?string}> $questionRows
     *   questions triées de la FAQ
     * @param int $pageCount
     * @param Permissions $permissions
     * @return AdminFaq
     */
    public function map(
        Faq $faq,
        array $categories,
        array $questionRows,
        int $pageCount,
        Permissions $permissions,
    ): AdminFaq {
        $user = $faq->getUser();
        $resource = $this->mapSummary(
            $faq,
            $faq->getFaqTranslationByLocale($this->locale, $this->defaultLocale)?->getTitle(),
            count($categories),
            count($questionRows),
            $pageCount,
            $user === null ? null : new AdminAuthor((int) $user->getId(), (string) $user->getLogin()),
            $permissions,
        );
        $resource->translations = AdminTranslationMapper::inSiteOrder(
            $this->locales,
            $faq->getFaqTranslations(),
            fn($translation, string $locale) => new AdminTitleTranslation($locale, (string) $translation->getTitle()),
        );

        $questionMapper = new AdminFaqQuestionMapper($this->locales, $this->locale, $this->defaultLocale);
        $questionsByCategory = [];
        foreach ($questionRows as $row) {
            $questionsByCategory[(int) $row['categoryId']][] = $questionMapper->mapTreeRow($row);
        }
        $categoryMapper = new AdminFaqCategoryMapper($this->locales, $this->locale, $this->defaultLocale);
        foreach ($categories as $category) {
            $resource->categories[] = $categoryMapper->mapTreeItem(
                $category,
                $questionsByCategory[(int) $category->getId()] ?? [],
            );
        }
        return $resource;
    }

    /**
     * Ressource d'une collection, titre, compteurs et auteur lus par la requête
     * @param Faq $faq
     * @param string|null $title
     * @param int $categoryCount
     * @param int $questionCount
     * @param int $pageCount
     * @param AdminAuthor|null $author
     * @param Permissions $permissions
     * @return AdminFaq
     */
    public function mapSummary(
        Faq $faq,
        ?string $title,
        int $categoryCount,
        int $questionCount,
        int $pageCount,
        ?AdminAuthor $author,
        Permissions $permissions,
    ): AdminFaq {
        $resource = new AdminFaq();
        $resource->id = (int) $faq->getId();
        $resource->title = (string) $title;
        $resource->disabled = (bool) $faq->isDisabled();
        $resource->categoryCount = $categoryCount;
        $resource->questionCount = $questionCount;
        $resource->pageCount = $pageCount;
        $resource->author = $author;
        $resource->createdAt = $faq->getCreatedAt()?->format(\DateTimeInterface::ATOM);
        $resource->updatedAt = $faq->getUpdateAt()?->format(\DateTimeInterface::ATOM);
        $resource->permissions = $permissions;
        return $resource;
    }
}
