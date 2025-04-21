<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Fixture mail
 */
namespace App\Tests\Helper\Fixtures\System;

use App\Entity\Admin\System\Mail;
use App\Entity\Admin\System\MailTranslation;
use App\Tests\Helper\FakerTrait;
use App\Utils\System\Mail\KeyWord;
use Symfony\Component\Yaml\Yaml;

trait MailFixturesTrait
{
    use FakerTrait;

    /**
     * Création d'un email
     * @param array $customData
     * @param bool $persist
     * @return Mail
     */
    public function createMail(array $customData = [], bool $persist = true): Mail
    {
        $data = [
            'key' => self::getFaker()->text(),
            'title' => self::getFaker()->text(),
            'description' => self::getFaker()->text(),
            'keyWords' => self::getFaker()->text(),
        ];
        $mail = $this->initEntity(Mail::class, array_merge($data, $customData));

        if ($persist) {
            $this->persistAndFlush($mail);
        }
        return $mail;
    }

    /**
     * Création d"un emailTranslation
     * @param Mail|null $mail
     * @param array $customData
     * @param bool $persist
     * @return MailTranslation
     */
    public function createMailTranslation(?Mail $mail = null, array $customData = [], bool $persist = true): MailTranslation
    {
        $data = [
            'mail' => $mail ?: $this->createMail(),
            'locale' => self::getFaker()->languageCode(),
            'title' => self::getFaker()->text(),
            'content' => self::getFaker()->text(),
        ];

        $mailTranslation = $this->initEntity(MailTranslation::class, array_merge($data, $customData));
        $mail->addMailTranslation($mailTranslation);
        if ($persist) {
            $this->persistAndFlush($mailTranslation);
        }
        return $mailTranslation;
    }

    /**
     * Génère l'ensemble des emails nécessaire au fonctionnement du site
     * @return void
     */
    public function generateDefaultMails(): void
    {
        $path = '.' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR .
            'DataFixtures' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR . 'mail_fixtures_data.yaml';

        $data = Yaml::parseFile($path);
        foreach ($data['mail'] as $dataMail) {

            $data = [];
            $dataMailTranslate = [];

            foreach ($dataMail as $key => $value) {
                if ($key === 'translate') {
                    $i = 0;
                    foreach ($value as $translate) {
                        $i++;
                        foreach ($translate as $keyT => $valueT) {
                            $dataMailTranslate[$i][$keyT] = $valueT;
                        }
                    }
                } elseif ($key === 'keyWords') {
                    $keyWord = new KeyWord($value);
                    $data[$key] = $keyWord->getStringTabKeyWord();
                } else {
                    $data[$key] = $value;
                }
            }

            $mail = $this->createMail($data);
            foreach ($dataMailTranslate as $translate) {
                $this->createMailTranslation($mail, $translate);
            }
        }
    }
}