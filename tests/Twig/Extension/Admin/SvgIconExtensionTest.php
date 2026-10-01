<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test SvgIconExtension et enum SvgIcon
 */
namespace App\Tests\Twig\Extension\Admin;

use App\Enum\Admin\Global\SvgIcon;
use App\Twig\Extension\Admin\SvgIconExtension;
use PHPUnit\Framework\TestCase;

class SvgIconExtensionTest extends TestCase
{
    /**
     * Test méthode svgIcon() : rendu d'une icône connue
     * @return void
     */
    public function testSvgIcon(): void
    {
        $html = (new SvgIconExtension())->svgIcon('LOCK', 'inline w-5 h-5');
        $this->assertSame(SvgIcon::LOCK->render('inline w-5 h-5'), $html);
        $this->assertStringContainsString('class="inline w-5 h-5"', $html);
        $this->assertStringContainsString('d="' . SvgIcon::LOCK->value . '"', $html);
    }

    /**
     * Test méthode svgIcon() : une icône inconnue lève une exception
     * @return void
     */
    public function testSvgIconUnknown(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new SvgIconExtension())->svgIcon('NOT_EXIST');
    }

    /**
     * Test méthode renderPath() : le tracé et les classes sont échappés
     * @return void
     */
    public function testRenderPathEscaped(): void
    {
        $html = SvgIcon::renderPath('M1 1Z"/><script>', 'w-4" onclick="x');
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('" onclick="', $html);
    }
}
