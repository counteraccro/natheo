<?php

declare(strict_types=1);
/**
 * Test de la mise en forme des tailles de fichier
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Tests\Formatter\Global;

use App\Formatter\Global\FileSizeFormatter;
use PHPUnit\Framework\TestCase;

class FileSizeFormatterTest extends TestCase
{
    /**
     * Test méthode format()
     * @return void
     */
    public function testFormat(): void
    {
        $this->assertEquals('0 octet', FileSizeFormatter::format(0));
        $this->assertEquals('999 octet', FileSizeFormatter::format(999));
        $this->assertEquals('1.5 Ko', FileSizeFormatter::format(1536));
        $this->assertEquals('2 Mo', FileSizeFormatter::format(2 * 1024 * 1024));
        $this->assertEquals('3 Go', FileSizeFormatter::format(3 * 1024 * 1024 * 1024));
    }
}
