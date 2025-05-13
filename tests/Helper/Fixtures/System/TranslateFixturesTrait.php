<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Fixture pour les traductions
 */
namespace App\Tests\Helper\Fixtures\System;

use App\Tests\Helper\FakerTrait;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Yaml\Yaml;

trait TranslateFixturesTrait
{
    use FakerTrait;

    /**
     * Création d'un fichier de traduction de test
     * @param string $filename
     * @param string $locale
     * @param array $customData
     * @return string
     */
    public function createTranslateFile(string $filename = 'z_unit_test+intl-icu', string $locale = 'fr', array $customData = []): string
    {
        $containerBag = $this->container->get(ContainerBagInterface::class);
        $kernel = $containerBag->get('kernel.project_dir');

        $path = $kernel . DIRECTORY_SEPARATOR . 'translations' . DIRECTORY_SEPARATOR;

        if(empty($customData)) {
            $data = [
                'unit-test.key.1' => self::getFaker()->text(),
                'unit-test.key.2' => self::getFaker()->text(),
                'unit-test.key.3' => self::getFaker()->text(),
            ];
        }
        else {
            $data = $customData;
        }


        $fileName = $path . $filename . '.' . $locale . '.yml';

        $yaml = Yaml::dump($data);
        file_put_contents($fileName, $yaml);

        return $fileName;
    }

    /**
     * Supprime le fichier en paramètre
     * @param string $path
     * @return void
     */
    public function removeTranslateFile(string $path): void
    {
        unlink($path);
    }
}