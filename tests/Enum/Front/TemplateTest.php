<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests de l'enum Template des thèmes du site
 */

namespace App\Tests\Enum\Front;

use App\Enum\Front\Template;
use PHPUnit\Framework\TestCase;

class TemplateTest extends TestCase
{
    /**
     * Chaque thème déclare l'entrée Vite de sa feuille de texte, qui définit sa classe de texte
     * @return void
     */
    public function testTextStylesheet(): void
    {
        $root = dirname(__DIR__, 3);
        $vite = (string) file_get_contents($root . '/vite.config.mts');
        foreach (Template::cases() as $template) {
            $entry = 'front_' . $template->value . '_content';
            $this->assertMatchesRegularExpression(
                "#\\b$entry: '\\./assets/styles/app_$entry\\.css'#",
                $vite,
                "Entrée Vite $entry absente",
            );
            $css = $root . '/assets/styles/app_' . $entry . '.css';
            $this->assertFileExists($css);

            // La classe de texte est définie par l'une des feuilles importées
            $imported = '';
            preg_match_all("#@import '\\./([^']+)'#", (string) file_get_contents($css), $imports);
            foreach ($imports[1] as $import) {
                $imported .= (string) file_get_contents($root . '/assets/styles/' . $import);
            }
            $this->assertStringContainsString('.' . $template->getTextClass(), $imported);
        }
    }
}
