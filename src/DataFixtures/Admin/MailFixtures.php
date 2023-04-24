<?php

namespace App\DataFixtures\Admin;

use App\DataFixtures\AppFixtures;
use App\Entity\Admin\Mail;
use App\Entity\Admin\MailTranslation;
use App\Utils\Mail\KeyWord;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Yaml\Yaml;

class MailFixtures extends AppFixtures implements FixtureGroupInterface, OrderedFixtureInterface
{
    const MAIL_FIXTURES_DATA_FILE = 'mail_fixtures_data.yaml';

    public function load(ObjectManager $manager): void
    {
        $data = Yaml::parseFile($this->pathDataFixtures . self::MAIL_FIXTURES_DATA_FILE);
        foreach ($data['mail'] as $id => $data) {
            $mail = new Mail();
            foreach ($data as $key => $value) {
                if ($key === 'translate') {
                    foreach ($value as $translate) {
                        $mailTranslate = new MailTranslation();
                        foreach ($translate as $keyChild => $valueChild) {
                            $this->setData($keyChild, $valueChild, $mailTranslate);
                        }
                        $mailTranslate->setMail($mail);
                        $manager->persist($mailTranslate);
                    }
                } elseif ($key === 'keyWords') {
                    $keyWord = new KeyWord($value);
                    $this->setData($key, $keyWord->getStringTabKeyWord(), $mail);
                } else {
                    $this->setData($key, $value, $mail);
                }
            }
            $manager->persist($mail);
        }
        $manager->flush();
    }

    /**
     * Set une valeur pour l'objet Email et EmaiTranslate
     * @param string $key
     * @param mixed $value
     * @param mixed $entity
     * @return void
     */
    private function setData(string $key, mixed $value, mixed $entity): void
    {
        $func = 'set' . ucfirst($key);
        $entity->$func($value);
    }

    public static function getGroups(): array
    {
        return [self::GROUP_MAIL];
    }

    public function getOrder(): int
    {
        return 4;
    }
}
