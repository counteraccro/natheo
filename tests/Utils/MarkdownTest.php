<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test Markdown
 */
namespace App\Tests\Utils;

use App\Tests\AppWebTestCase;
use App\Utils\Markdown;
use League\CommonMark\Exception\CommonMarkException;

class MarkdownTest extends AppWebTestCase
{
    /**
     * Test méthode convertMarkdownToHtml()
     * @return void
     * @throws CommonMarkException
     */
    public function testConvertMarkdownToHtml() :void
    {
        $markdonw = new Markdown();

        $text = 'Je suis un **test gras** et *italique*';

        $result = $markdonw->convertMarkdownToHtml($text);
        $this->assertNotEmpty($result);
        $this->assertIsString($result);
        $this->assertStringContainsString('<strong>test gras</strong>', $result);
        $this->assertStringContainsString('<em>italique</em>', $result);
    }
}