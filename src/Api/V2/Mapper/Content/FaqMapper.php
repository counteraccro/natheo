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
     */
    public function __construct(private string $locale) {}

    /**
     * @param Faq $faq
     * @param array<int, string> $answers réponses HTML indexées par id de question
     * @return FaqContent
     */
    public function map(Faq $faq, array $answers): FaqContent
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
                    $answers[(int) $faqQuestion->getId()] ?? '',
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
