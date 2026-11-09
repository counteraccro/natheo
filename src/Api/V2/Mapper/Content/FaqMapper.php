<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Convertit une FAQ en contenu de bloc FAQ de l'API v2, sans les catégories et questions désactivées
 */

namespace App\Api\V2\Mapper\Content;

use App\Api\V2\Output\Content\FaqContent;
use App\Api\V2\Output\Content\FaqContentCategory;
use App\Api\V2\Output\Content\FaqContentQuestion;
use App\Entity\Admin\Content\Faq\Faq;

final readonly class FaqMapper
{
    /**
     * @param string $locale
     * @param \Closure(string): string $renderMarkdown convertit une réponse Markdown en HTML
     */
    public function __construct(private string $locale, private \Closure $renderMarkdown) {}

    /**
     * @param Faq $faq
     * @return FaqContent
     */
    public function map(Faq $faq): FaqContent
    {
        $categories = [];
        foreach ($faq->getFaqCategories() as $faqCategory) {
            if ($faqCategory->isDisabled()) {
                continue;
            }

            $questions = [];
            foreach ($faqCategory->getFaqQuestions() as $faqQuestion) {
                if ($faqQuestion->isDisabled()) {
                    continue;
                }
                $translation = $faqQuestion->getFaqQuestionTranslationByLocale($this->locale);
                $questions[] = new FaqContentQuestion(
                    (string) $translation?->getTitle(),
                    ($this->renderMarkdown)((string) $translation?->getAnswer()),
                );
            }

            $categories[] = new FaqContentCategory(
                (string) $faqCategory->getFaqCategoryTranslationByLocale($this->locale)?->getTitle(),
                $questions,
            );
        }

        return new FaqContent((string) $faq->getFaqTranslationByLocale($this->locale)?->getTitle(), $categories);
    }
}
