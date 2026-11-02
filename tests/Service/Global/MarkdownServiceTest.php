<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Test MarkdownService
 */
namespace App\Tests\Service\Global;

use App\Service\Global\MarkdownService;
use App\Tests\AppWebTestCase;
use League\CommonMark\Exception\CommonMarkException;

class MarkdownServiceTest extends AppWebTestCase
{
    /**
     * Test méthode convertMarkdownToHtml()
     * @return void
     * @throws CommonMarkException
     */
    public function testConvertMarkdownToHtml(): void
    {
        $markdown = new MarkdownService();

        $result = $markdown->convertMarkdownToHtml('Je suis un **test gras** et *italique*');
        $this->assertStringContainsString('<strong>test gras</strong>', $result);
        $this->assertStringContainsString('<em>italique</em>', $result);

        // Mode par défaut (pages, mails) : HTML et attributs personnalisés conservés
        $result = $markdown->convertMarkdownToHtml("<span>html</span>\n\n# Titre {.ma-classe}");
        $this->assertStringContainsString('<span>html</span>', $result);
        $this->assertStringContainsString('class="ma-classe"', $result);
    }

    /**
     * Test méthode convertMarkdownToHtml() en mode strict
     * @return void
     * @throws CommonMarkException
     */
    public function testConvertMarkdownToHtmlStrict(): void
    {
        $markdown = new MarkdownService();

        $result = $markdown->convertMarkdownToHtml(
            "**gras** <script>alert(1)</script> [clic](javascript:alert(2))\n\n# Titre {onclick=alert(3)}",
            true,
        );
        $this->assertStringContainsString('<strong>gras</strong>', $result);
        $this->assertStringContainsString('&lt;script&gt;', $result);
        $this->assertStringNotContainsString('<script', $result);
        $this->assertStringNotContainsString('javascript:', $result);
        $this->assertStringContainsString('<h1>Titre {onclick=alert(3)}</h1>', $result);
    }

    /**
     * Test méthode convertMarkdownToText()
     * @return void
     * @throws CommonMarkException
     */
    public function testConvertMarkdownToText(): void
    {
        $markdown = new MarkdownService();

        $result = $markdown->convertMarkdownToText("**x<y** et `<div>`\n\n[lien](javascript:alert(1))");
        $this->assertEquals('x<y et <div> lien', $result);
    }
}
