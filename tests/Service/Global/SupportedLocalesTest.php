<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests de la liste des langues du site
 */

namespace App\Tests\Service\Global;

use App\Service\Global\SupportedLocales;
use PHPUnit\Framework\TestCase;

class SupportedLocalesTest extends TestCase
{
    /**
     * Découpe la chaîne du paramètre dans l'ordre
     * @return void
     */
    public function testGetAll(): void
    {
        $this->assertSame(['fr', 'en', 'es'], (new SupportedLocales('fr|en|es'))->getAll());
    }

}
