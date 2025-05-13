<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Initialisation Faker
 */
namespace App\Tests\Helper;

use Faker\Factory;
use Faker\Generator;

trait FakerTrait
{
    /**
     * @var Generator
     */
    protected static Generator $faker;

    /**
     * Récupère une instance de faker
     * @return Generator
     */
    protected static function getFaker(): Generator
    {
        if (empty(self::$faker)) {
            self::$faker = Factory::create('fr_FR');
        }
        return self::$faker;
    }

}