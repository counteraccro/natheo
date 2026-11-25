<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Fixtures pour les faq
 */

namespace App\DataFixtures\Admin\Content\Faq;

use App\DataFixtures\AppFixtures;
use App\Entity\Admin\Content\Faq\Faq;
use App\Entity\Admin\Content\Faq\FaqCategory;
use App\Entity\Admin\Content\Faq\FaqCategoryTranslation;
use App\Entity\Admin\Content\Faq\FaqQuestion;
use App\Entity\Admin\Content\Faq\FaqQuestionTranslation;
use App\Entity\Admin\Content\Faq\FaqTranslation;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class FaqFixtures extends AppFixtures implements FixtureGroupInterface, OrderedFixtureInterface
{
    /**
     * Dossier des FAQ : un fichier YAML par FAQ
     */
    const FAQ_FIXTURES_FOLDER = 'content' . DIRECTORY_SEPARATOR . 'faq' . DIRECTORY_SEPARATOR;

    public function load(ObjectManager $manager): void
    {
        foreach ($this->loadYamlFolder($this->pathDataFixtures . self::FAQ_FIXTURES_FOLDER) as $ref => $file) {
            $data = $file['data'];

            $faq = new Faq();
            $faq->setUser($this->getUserReference($data['user'] ?? 'User'));
            $faq->setDisabled($data['disabled'] ?? false);
            foreach ($data['title'] as $locale => $title) {
                $faq->addFaqTranslation(
                    $this->populateEntity(['locale' => $locale, 'title' => $title], new FaqTranslation()),
                );
            }

            foreach (array_values($data['categories']) as $index => $category) {
                $faq->addFaqCategory($this->createFaqCategory($category, $index + 1));
            }

            $manager->persist($faq);
            $this->addReference($ref, $faq);
        }
        $manager->flush();
    }

    /**
     * Création d'une catégorie et de ses questions
     * @param array $data
     * @param int $renderOrder
     * @return FaqCategory
     */
    private function createFaqCategory(array $data, int $renderOrder): FaqCategory
    {
        $faqCategory = new FaqCategory();
        $faqCategory->setRenderOrder($renderOrder);
        $faqCategory->setDisabled($data['disabled'] ?? false);
        foreach ($data['title'] as $locale => $title) {
            $faqCategory->addFaqCategoryTranslation(
                $this->populateEntity(['locale' => $locale, 'title' => $title], new FaqCategoryTranslation()),
            );
        }

        foreach (array_values($data['questions']) as $index => $question) {
            $faqCategory->addFaqQuestion($this->createFaqQuestion($question, $index + 1));
        }
        return $faqCategory;
    }

    /**
     * Création d'une question
     * @param array $data
     * @param int $renderOrder
     * @return FaqQuestion
     */
    private function createFaqQuestion(array $data, int $renderOrder): FaqQuestion
    {
        $faqQuestion = new FaqQuestion();
        $faqQuestion->setRenderOrder($renderOrder);
        $faqQuestion->setDisabled($data['disabled'] ?? false);
        foreach ($data['title'] as $locale => $title) {
            $faqQuestion->addFaqQuestionTranslation(
                $this->populateEntity(
                    ['locale' => $locale, 'title' => $title, 'answer' => $data['answer'][$locale]],
                    new FaqQuestionTranslation(),
                ),
            );
        }
        return $faqQuestion;
    }

    public static function getGroups(): array
    {
        return [self::GROUP_FAQ, self::GROUP_CONTENT];
    }

    public function getOrder(): int
    {
        return 204;
    }
}
