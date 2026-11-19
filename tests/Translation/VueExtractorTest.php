<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests de l'extracteur des clés t('…') du code Vue et TypeScript
 */

namespace App\Tests\Translation;

use App\Translation\VueExtractor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Translation\MessageCatalogue;

class VueExtractorTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/vue_extractor_' . uniqid();
        $filesystem = new Filesystem();
        $filesystem->dumpFile(
            $this->directory . '/Components/Demo.vue',
            <<<'VUE'
            <template>
              <p :title="t('admin_app.not_found.title')">{{ t("admin_app.not_found.text", { path }) }}</p>
              <button>{{ t('btnSave') }}</button>
            </template>
            <script setup lang="ts">
            const label = t(`admin_app.menu.logout`);
            const dynamic = t(`admin_app.role.${role}`);
            const built = t('admin_app.role.' + role);
            const other = format('admin_app.format.ignored');
            </script>
            VUE
            ,
        );
        $filesystem->dumpFile($this->directory . '/Composables/labels.ts', "t('admin_ui.dialog.close');\n");
        $filesystem->dumpFile($this->directory . '/Composables/__tests__/labels.spec.ts', "t('admin_ui.test.only');\n");
        $filesystem->dumpFile($this->directory . '/Composables/notes.md', "t('admin_ui.markdown.only');\n");
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove($this->directory);
    }

    /**
     * Clés littérales au format de la convention, domaine déduit du premier segment, préfixe appliqué
     * @return void
     */
    public function testExtract(): void
    {
        $extractor = new VueExtractor($this->directory);
        $extractor->setPrefix('__');
        $catalogue = new MessageCatalogue('fr');
        $extractor->extract('src', $catalogue);

        $this->assertSame(
            [
                'admin_app.menu.logout' => '__admin_app.menu.logout',
                'admin_app.not_found.text' => '__admin_app.not_found.text',
                'admin_app.not_found.title' => '__admin_app.not_found.title',
            ],
            $this->sorted($catalogue->all('admin_app')),
        );
        $this->assertSame(['admin_ui.dialog.close' => '__admin_ui.dialog.close'], $catalogue->all('admin_ui'));
        $this->assertSame(['admin_app', 'admin_ui'], $this->sortedDomains($catalogue));
    }

    /**
     * La commande appelle l'extracteur pour chaque dossier de code : le dossier Vue n'est lu qu'une fois
     * @return void
     */
    public function testExtractOncePerCatalogue(): void
    {
        $extractor = new VueExtractor($this->directory);
        $catalogue = new MessageCatalogue('fr');
        $extractor->extract('src', $catalogue);
        $catalogue->set('admin_ui.dialog.close', 'Fermer', 'admin_ui');
        $extractor->extract('templates', $catalogue);

        $this->assertSame('Fermer', $catalogue->get('admin_ui.dialog.close', 'admin_ui'));
    }

    /**
     * Clé dynamique, clé hors convention, autre fonction et fonction locale d'un composant ignorées
     * @return void
     */
    public function testExtractKeysIgnored(): void
    {
        $extractor = new VueExtractor($this->directory);

        $this->assertSame([], $extractor->extractKeys("t('btnHeading', 'Titres')"));
        $this->assertSame([], $extractor->extractKeys("t('admin_ui.close')"));
        $this->assertSame([], $extractor->extractKeys('t(`admin_ui.role.${role}`)'));
        $this->assertSame([], $extractor->extractKeys("format('admin_ui.dialog.close')"));
        $this->assertSame([], $extractor->extractKeys('$t(\'admin_ui.dialog.close\')'));
        $this->assertSame(['admin_ui.dialog.close'], $extractor->extractKeys("labels.t( 'admin_ui.dialog.close' )"));
    }

    /**
     * @param array<string, string> $messages
     * @return array<string, string>
     */
    private function sorted(array $messages): array
    {
        ksort($messages);
        return $messages;
    }

    /**
     * @param MessageCatalogue $catalogue
     * @return string[]
     */
    private function sortedDomains(MessageCatalogue $catalogue): array
    {
        $domains = $catalogue->getDomains();
        sort($domains);
        return $domains;
    }
}
