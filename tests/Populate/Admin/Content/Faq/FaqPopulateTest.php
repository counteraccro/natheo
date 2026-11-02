<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Test de FaqPopulate
 */

namespace App\Tests\Populate\Admin\Content\Faq;

use App\Entity\Admin\Content\Faq\Faq;
use App\Entity\Admin\Content\Faq\FaqCategory;
use App\Entity\Admin\Content\Faq\FaqQuestion;
use App\Populate\Admin\Content\Faq\FaqPopulate;
use App\Tests\AppWebTestCase;

class FaqPopulateTest extends AppWebTestCase
{
    /**
     * Test méthode populate() : mise à jour des données existantes, ids conservés
     * @return void
     */
    public function testPopulateUpdateKeepIds(): void
    {
        $faq = $this->createFaqAllDataDefault();
        $data = $this->faqToPopulateArray($faq);

        $data['disabled'] = true;
        $data['faqTranslations'][0]['title'] = 'Titre FAQ modifié';
        $data['faqCategories'][0]['faqCategoryTranslations'][0]['title'] = 'Catégorie modifiée';
        $data['faqCategories'][0]['faqQuestions'][0]['faqQuestionTranslations'][0]['answer'] = 'Réponse modifiée';

        $faq = (new FaqPopulate($faq, $data))->populate()->getFaq();
        $this->persistAndFlush($faq);
        $this->em->clear();

        $verif = $this->em->getRepository(Faq::class)->find($faq->getId());
        $this->assertTrue($verif->isDisabled());
        $this->assertEquals(self::faqIds($data), self::faqIds($this->faqToPopulateArray($verif)));

        $locale = $data['faqTranslations'][0]['locale'];
        $this->assertEquals('Titre FAQ modifié', $verif->getFaqTranslationByLocale($locale)->getTitle());

        $category = $this->em->getRepository(FaqCategory::class)->find($data['faqCategories'][0]['id']);
        $this->assertEquals('Catégorie modifiée', $category->getFaqCategoryTranslations()->first()->getTitle());

        $question = $this->em
            ->getRepository(FaqQuestion::class)
            ->find($data['faqCategories'][0]['faqQuestions'][0]['id']);
        $this->assertEquals('Réponse modifiée', $question->getFaqQuestionTranslations()->first()->getAnswer());
    }

    /**
     * Test méthode populate() : ajout, déplacement et suppression de catégories et questions
     * @return void
     */
    public function testPopulateAddMoveRemove(): void
    {
        $faq = $this->createFaqAllDataDefault();
        $data = $this->faqToPopulateArray($faq);

        $categoryKeptId = $data['faqCategories'][0]['id'];
        $categoryRemovedId = $data['faqCategories'][1]['id'];
        $movedQuestion = $data['faqCategories'][1]['faqQuestions'][0];
        $removedQuestionId = $data['faqCategories'][0]['faqQuestions'][2]['id'];
        $keptQuestionIds = [
            $data['faqCategories'][0]['faqQuestions'][0]['id'],
            $data['faqCategories'][0]['faqQuestions'][1]['id'],
        ];

        // Question déplacée de la catégorie 2 vers la 1, troisième question de la 1 supprimée
        $data['faqCategories'][0]['faqQuestions'][2] = $movedQuestion;
        // Catégorie 2 supprimée, nouvelle catégorie (id temporaire négatif) avec une nouvelle question
        $data['faqCategories'][1] = [
            'id' => -1,
            'disabled' => false,
            'renderOrder' => 2,
            'faqCategoryTranslations' => [['id' => -2, 'locale' => 'fr', 'title' => 'Nouvelle catégorie']],
            'faqQuestions' => [
                [
                    'id' => -3,
                    'disabled' => false,
                    'renderOrder' => 1,
                    'faqQuestionTranslations' => [
                        ['id' => -4, 'locale' => 'fr', 'title' => 'Nouvelle question', 'answer' => 'Réponse'],
                    ],
                ],
            ],
        ];

        $faq = (new FaqPopulate($faq, $data))->populate()->getFaq();
        $this->persistAndFlush($faq);
        $this->em->clear();

        $verif = $this->em->getRepository(Faq::class)->find($faq->getId());
        $this->assertCount(2, $verif->getFaqCategories());

        $categoryKept = $this->em->getRepository(FaqCategory::class)->find($categoryKeptId);
        $questionIds = $categoryKept->getFaqQuestions()->map(fn(FaqQuestion $q) => $q->getId())->toArray();
        sort($questionIds);
        $expected = array_merge($keptQuestionIds, [$movedQuestion['id']]);
        sort($expected);
        $this->assertEquals($expected, $questionIds);

        $this->assertNull($this->em->getRepository(FaqCategory::class)->find($categoryRemovedId));
        $this->assertNull($this->em->getRepository(FaqQuestion::class)->find($removedQuestionId));

        $newCategory = $verif
            ->getFaqCategories()
            ->filter(fn(FaqCategory $c) => $c->getId() !== $categoryKeptId)
            ->first();
        $this->assertGreaterThan(0, $newCategory->getId());
        $this->assertEquals('Nouvelle catégorie', $newCategory->getFaqCategoryTranslations()->first()->getTitle());
        $this->assertCount(1, $newCategory->getFaqQuestions());
        $this->assertEquals(
            'Nouvelle question',
            $newCategory->getFaqQuestions()->first()->getFaqQuestionTranslations()->first()->getTitle(),
        );
    }

    /**
     * Test méthode populate() : un id appartenant à une autre FAQ est traité comme un nouvel élément
     * @return void
     */
    public function testPopulateIgnoreIdFromOtherFaq(): void
    {
        $faq = $this->createFaqAllDataDefault();
        $otherFaq = $this->createFaqAllDataDefault();
        $otherCategoryId = $otherFaq->getFaqCategories()->first()->getId();

        $data = $this->faqToPopulateArray($faq);
        $data['faqCategories'][0]['id'] = $otherCategoryId;

        $faq = (new FaqPopulate($faq, $data))->populate()->getFaq();
        $this->persistAndFlush($faq);
        $this->em->clear();

        $otherCategory = $this->em->getRepository(FaqCategory::class)->find($otherCategoryId);
        $this->assertEquals($otherFaq->getId(), $otherCategory->getFaq()->getId());
        $this->assertCount(2, $this->em->getRepository(Faq::class)->find($faq->getId())->getFaqCategories());
    }

    /**
     * Retourne les ids des catégories, questions et traductions d'une FAQ au format tableau, triés
     * @param array $data
     * @return string[]
     */
    public static function faqIds(array $data): array
    {
        $ids = [];
        foreach ($data['faqCategories'] as $category) {
            $ids[] = 'c' . $category['id'];
            foreach ($category['faqCategoryTranslations'] as $translation) {
                $ids[] = 'ct' . $translation['id'];
            }
            foreach ($category['faqQuestions'] as $question) {
                $ids[] = 'q' . $question['id'];
                foreach ($question['faqQuestionTranslations'] as $translation) {
                    $ids[] = 'qt' . $translation['id'];
                }
            }
        }
        sort($ids);
        return $ids;
    }
}
