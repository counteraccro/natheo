<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Fixtures pour la génération des mails
 */
namespace App\DataFixtures\Admin\System;

use App\DataFixtures\AppFixtures;
use App\Entity\Admin\System\Mail;
use App\Entity\Admin\System\MailTranslation;
use App\Utils\System\Mail\KeyWord;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Yaml\Yaml;

class MailFixtures extends AppFixtures implements FixtureGroupInterface, OrderedFixtureInterface
{
    const MAIL_FIXTURES_DATA_FILE = 'system' . DIRECTORY_SEPARATOR . 'mail_fixtures_data.yaml';

    public function load(ObjectManager $manager): void
    {
        $data = Yaml::parseFile($this->pathDataFixtures . self::MAIL_FIXTURES_DATA_FILE);
        foreach ($data['mail'] as $dataMail) {
            $mail = new Mail();
            foreach ($dataMail as $key => $value) {
                if ($key === 'translate') {
                    foreach ($value as $translate) {
                        $mail->addMailTranslation($this->populateEntity($translate, new MailTranslation()));
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


    public static function getGroups(): array
    {
        return [self::GROUP_MAIL, self::GROUP_SYSTEM];
    }

    public function getOrder(): int
    {
        return 104;
    }
}
