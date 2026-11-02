<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Conversion du Markdown en HTML
 */

namespace App\Service\Global;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Exception\CommonMarkException;
use League\CommonMark\Extension\Attributes\AttributesExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\Strikethrough\StrikethroughExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\MarkdownConverter;

class MarkdownService
{
    /**
     * Permet de convertir du Markdown en HTML
     * Le mode strict est à utiliser pour les contenus saisis par les visiteurs (commentaires) :
     * HTML échappé, liens dangereux (javascript:, data:...) supprimés et attributs personnalisés interdits
     * @param string $text
     * @param bool $strict
     * @return string
     * @throws CommonMarkException
     */
    public function convertMarkdownToHtml(string $text, bool $strict = false): string
    {
        $config = $strict ? ['html_input' => 'escape', 'allow_unsafe_links' => false] : [];

        $environment = new Environment($config);
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new TableExtension());
        $environment->addExtension(new StrikethroughExtension());
        if (!$strict) {
            $environment->addExtension(new AttributesExtension());
        }
        $converter = new MarkdownConverter($environment);
        return $converter->convert($text)->getContent();
    }

    /**
     * Convertit du Markdown en texte brut (sans balise), en mode strict
     * @param string $text
     * @return string
     * @throws CommonMarkException
     */
    public function convertMarkdownToText(string $text): string
    {
        $html = $this->convertMarkdownToHtml($text, true);
        $plain = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return trim(preg_replace('/\s+/u', ' ', $plain));
    }
}
