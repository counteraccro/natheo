<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
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
     * Test méthode getMenuPreviewTemplate() : gabarit du thème actif, Natheo-Base pour un thème qui n'en a pas
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetMenuPreviewTemplate(): void
    {
        $optionSystemService = $this->container->get(OptionSystemService::class);

        $optionSystemService->saveValueByKee(OptionSystem::OS_THEME_FRONT_SITE->value, Template::NATHEO_HORIZON->value);
        $this->assertSame(
            ['template' => 'front/natheo_horizon/menu_preview.html.twig', 'scriptsTag' => 'front_natheo_horizon'],
            $this->frontThemeService->getMenuPreviewTemplate(),
        );

        $optionSystemService->saveValueByKee(OptionSystem::OS_THEME_FRONT_SITE->value, Template::NATHEO_STORM->value);
        $this->assertSame(
            ['template' => 'front/natheo_base/menu_preview.html.twig', 'scriptsTag' => 'front_natheo_base'],
            $this->frontThemeService->getMenuPreviewTemplate(),
        );
    }
}
