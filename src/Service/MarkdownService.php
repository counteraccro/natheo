<?php

namespace App\Service;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Exception\CommonMarkException;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\Strikethrough\StrikethroughExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\MarkdownConverter;
use League\CommonMark\Util\HtmlElement;

class MarkdownService extends AppService
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
        $converter = new MarkdownConverter($environment);
        return $converter->convert($text)->getContent();
    }
}
