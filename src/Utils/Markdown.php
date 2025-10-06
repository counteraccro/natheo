<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Class utilitaire pour convertir du Markdown en HTML
 */

namespace App\Utils;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Exception\CommonMarkException;
use League\CommonMark\Extension\Attributes\AttributesExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\Strikethrough\StrikethroughExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\MarkdownConverter;

class Markdown
{
    /**
     * Permet de convertir du Markdown en HTML
     * @throws CommonMarkException
     */
    public function convertMarkdownToHtml(string $text): string
    {
        $environment = new Environment();
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new TableExtension());
        $environment->addExtension(new StrikethroughExtension());
        $environment->addExtension(new AttributesExtension());
        $converter = new MarkdownConverter($environment);
        return $converter->convert($text)->getContent();
    }
}
