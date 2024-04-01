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

class FaqFixtures extends AppFixtures implements FixtureGroupInterface, OrderedFixtureInterface
{
    const FAQ_FIXTURES_DATA_FILE = 'content' . DIRECTORY_SEPARATOR . 'faq' . DIRECTORY_SEPARATOR .
    'faq_fixtures_data.yaml';

    public function load(ObjectManager $manager): void
    {
        $data = Yaml::parseFile($this->pathDataFixtures . self::FAQ_FIXTURES_DATA_FILE);

        foreach ($data['faq'] as $ref => $faqData) {
            $faq = new Faq();
            foreach ($faqData as $key => $value) {
                switch ($key) {
                    case "user" :
                        $faq->setUser($this->getReference($value, User::class));
                        break;
                    case "faqTranslation":
                        foreach ($value as $faqTrans) {
                            $faq->addFaqTranslation($this->populateEntity($faqTrans, new FaqTranslation()));
                        }
                        break;
                    case "faqCategory" :
                        foreach ($value as $faqCat) {
                            $faq->addFaqCategory($this->createFaqCategory($faqCat));
                        }
                        break;
                    case "faqStatistique" :
                        foreach ($value as $faqStat) {
                            $faq->addFaqStatistique($this->populateEntity($faqStat, new FaqStatistique()));
                        }
                        break;
                    default:
                        $this->setData($key, $value, $faq);
                }
            }
            $manager->persist($faq);
            $this->addReference($ref, $faq);
        }
        $manager->flush();
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
                        $faqCategory->addFaqCategoryTranslation($this->populateEntity(
                            $faqCatTrans, new FaqCategoryTranslation()));
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
                        $this->populateEntity($faqQuestionTranslation, new FaqQuestionTranslation()));
                }
            } else {
                $this->setData($key, $value, $faqQuestion);
            }

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
