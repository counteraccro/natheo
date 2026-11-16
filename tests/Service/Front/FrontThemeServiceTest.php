<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Test du service des props communes des thèmes
 */

namespace App\Tests\Service\Front;

use App\Enum\Admin\System\Options\OptionSystem;
use App\Enum\Front\Template;
use App\Service\Admin\System\OptionSystemService;
use App\Service\Front\FrontThemeService;
use App\Tests\AppWebTestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class FrontThemeServiceTest extends AppWebTestCase
{
    /**
     * @var FrontThemeService
     */
    private FrontThemeService $frontThemeService;

    public function setUp(): void
    {
        parent::setUp();
        $this->frontThemeService = $this->container->get(FrontThemeService::class);
    }

    /**
     * Test méthode getUrls() : accueil de chaque langue
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetUrls(): void
    {
        $urls = $this->frontThemeService->getUrls();
        $this->assertSame($this->container->getParameter('app.api_v2.prefix'), $urls['api']);
        foreach ($this->locales as $locale) {
            $this->assertArrayHasKey($locale, $urls['home']);
        }
    }

    /**
     * Test méthode getDatas() : visiteur anonyme, sans prévisualisation ni bandeau
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetDatas(): void
    {
        $datas = $this->frontThemeService->getDatas('en', 'slug-test');
        $this->assertSame('en', $datas['locale']);
        $this->assertSame('slug-test', $datas['slug']);
        $this->assertSame('', $datas['userToken']);
        $this->assertNull($datas['user']);
        $this->assertNull($datas['preview']);
        $this->assertNull($datas['consent']);
    }

    /**
     * Test méthode getMenuPreviewTemplate() : gabarit du dossier du thème actif, présent pour chaque thème
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetMenuPreviewTemplate(): void
    {
        $optionSystemService = $this->container->get(OptionSystemService::class);

        foreach (Template::cases() as $template) {
            $optionSystemService->saveValueByKee(OptionSystem::OS_THEME_FRONT_SITE->value, $template->value);
            $this->assertSame(
                [
                    'template' => 'front/' . $template->value . '/menu_preview.html.twig',
                    'scriptsTag' => 'front_' . $template->value,
                ],
                $this->frontThemeService->getMenuPreviewTemplate(),
            );
        }
    }

    /**
     * Test méthode getTemplateVersion() : version propre au thème actif
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetTemplateVersion(): void
    {
        $optionSystemService = $this->container->get(OptionSystemService::class);

        foreach (Template::cases() as $template) {
            $optionSystemService->saveValueByKee(OptionSystem::OS_THEME_FRONT_SITE->value, $template->value);
            $this->assertSame(
                $this->container->getParameter('app.' . $template->value . '.version'),
                $this->frontThemeService->getTemplateVersion(),
            );
        }
    }
}
