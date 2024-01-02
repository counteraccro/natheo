<?php

namespace App\DataFixtures\Admin\Content\Faq;

use App\DataFixtures\AppFixtures;
use App\Entity\Admin\Content\Faq\Faq;
use App\Entity\Admin\Content\Faq\FaqCategory;
use App\Entity\Admin\Content\Faq\FaqCategoryTranslation;
use App\Entity\Admin\Content\Faq\FaqQuestion;
use App\Entity\Admin\Content\Faq\FaqQuestionTranslation;
use App\Entity\Admin\Content\Faq\FaqStatistique;
use App\Entity\Admin\Content\Faq\FaqTranslation;
use App\Entity\Admin\System\User;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Yaml\Yaml;

;

class FaqFixtures extends AppFixtures implements FixtureGroupInterface, OrderedFixtureInterface
{
    const FAQ_FIXTURES_DATA_FILE = 'content' . DIRECTORY_SEPARATOR . 'faq' . DIRECTORY_SEPARATOR .
    'faq_fixtures_data.yaml';

    public function load(ObjectManager $manager): void
    {
        $data = Yaml::parseFile($this->pathDataFixtures . self::FAQ_FIXTURES_DATA_FILE);

        foreach ($data['faq'] as $faqData) {
            $faq = new Faq();
            foreach ($faqData as $key => $value) {
                switch ($key) {
                    case "user" :
                        $faq->setUser($this->getReference($value, User::class));
                        break;
                    case "faqTranslation":
                        foreach ($value as $faqTrans) {
                            $faq->addFaqTranslation($this->createFaqTranslation($faqTrans));
                        }
                        break;
                    case "faqCategory" :
                        foreach ($value as $faqCat) {
                            $faq->addFaqCategory($this->createFaqCategory($faqCat));
                        }
                        break;
                    case "faqStatistique" :
                        foreach ($value as $faqCat) {
                            $faq->addFaqStatistique($this->createFaqStatistique($faqCat));
                        }
                        break;
                    default:
                        $this->setData($key, $value, $faq);
                }
            }
            $manager->persist($faq);
        }
        $manager->flush();
    }

    /**
     * Création d'une faqTranslation
     * @param array $data
     * @return FaqTranslation
     */
    private function createFaqTranslation(array $data): FaqTranslation
    {
        $faqTranslation = new FaqTranslation();
        foreach ($data as $key => $value) {
            $this->setData($key, $value, $faqTranslation);
        }
        return $faqTranslation;
    }

    /**
     * Création d'une FAQCatégorie
     * @param array $data
     * @return FaqCategory
     */
    private function createFaqCategory(array $data): FaqCategory
    {

        $faqCategory = new FaqCategory();
        foreach ($data as $key => $value) {

            switch ($key) {
                case "faqCategoryTranslation" :
                    foreach ($value as $faqCatTrans) {
                        $faqCategory->addFaqCategoryTranslation($this->createFaqCategoryTranslation($faqCatTrans));
                    }
                    break;
                case "faqQuestion" :
                    foreach ($value as $faqQuestion) {
                        $faqCategory->addFaqQuestion($this->createFaqQuestion($faqQuestion));
                    }
                    break;
                default:
                    $this->setData($key, $value, $faqCategory);
            }
        }
        return $faqCategory;
    }

    /**
     * Création d'une FaqCategoryTranslation
     * @param array $data
     * @return FaqCategoryTranslation
     */
    private function createFaqCategoryTranslation(array $data): FaqCategoryTranslation
    {
        $faqCategoryTranslation = new FaqCategoryTranslation();
        foreach ($data as $key => $value) {
            $this->setData($key, $value, $faqCategoryTranslation);
        }
        return $faqCategoryTranslation;
    }

    /**
     * Création d'une FaqQuestion
     * @param array $data
     * @return FaqQuestion
     */
    private function createFaqQuestion(array $data): FaqQuestion
    {
        $faqQuestion = new FaqQuestion();
        foreach ($data as $key => $value) {
            if ($key === 'faqQuestionTranslation') {
                foreach ($value as $faqQuestionTranslation) {
                    $faqQuestion->addFaqQuestionTranslation(
                        $this->createFaqQuestionTranslation($faqQuestionTranslation));
                }
            } else {
                $this->setData($key, $value, $faqQuestion);
            }

        }
        return $faqQuestion;
    }

    /**
     * Création d'une FaqQuestionTranslation
     * @param array $data
     * @return FaqQuestionTranslation
     */
    private function createFaqQuestionTranslation(array $data): FaqQuestionTranslation
    {
        $faqQuestionTranslation = new FaqQuestionTranslation();
        foreach ($data as $key => $value) {
            $this->setData($key, $value, $faqQuestionTranslation);
        }
        return $faqQuestionTranslation;
    }

    /**
     * Création d'une FaqStatistique
     * @param array $data
     * @return FaqStatistique
     */
    private function createFaqStatistique(array $data): FaqStatistique
    {
        $faqStatistique = new FaqStatistique();
        foreach ($data as $key => $value) {
            $this->setData($key, $value, $faqStatistique);
        }
        return $faqStatistique;
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
