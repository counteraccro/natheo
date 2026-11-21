<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Props communes des thèmes du site public et gabarit de l'aperçu de menu
 */

namespace App\Service\Front;

use App\Enum\Front\Template;
use App\Service\Global\SupportedLocales;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;

class FrontThemeService extends AppFrontService
{
    /**
     * Gabarit de l'aperçu de menu, dans le dossier de chaque thème
     * @var string
     */
    public const string MENU_PREVIEW = 'menu_preview.html.twig';

    /**
     * Gabarit de l'aperçu de tag, dans le dossier de chaque thème
     * @var string
     */
    public const string TAG_PREVIEW = 'tag_preview.html.twig';

    /**
     * @param ContainerInterface $handlers
     * @param SupportedLocales $supportedLocales
     * @param OptionSystemFrontService $optionSystemFrontService
     */
    public function __construct(
        #[AutowireLocator(self::HANDLERS)] ContainerInterface $handlers,
        private readonly SupportedLocales $supportedLocales,
        private readonly OptionSystemFrontService $optionSystemFrontService,
    ) {
        parent::__construct($handlers);
    }

    /**
     * URLs passées à un thème : préfixe de l'API v2, accueil de chaque langue, sitemap, connexion et déconnexion
     * @return array{api: string, adminAuth: string, sitemap: string, logout: string, home: array<string, string>}
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getUrls(): array
    {
        $router = $this->getRouter();
        $home = [];
        foreach ($this->supportedLocales->getAll() as $locale) {
            $home[$locale] = $router->generate('front_index_2', [
                'locale' => $locale,
                'category' => null,
                'slug' => null,
            ]);
        }

        return [
            'api' => (string) $this->getParameterBag()->get('app.api_v2.prefix'),
            'adminAuth' => $router->generate('admin_dashboard_index'),
            'sitemap' => $router->generate('front_sitemap'),
            'logout' => $router->generate('auth_logout'),
            'home' => $home,
        ];
    }

    /**
     * Données passées à un thème pour un visiteur anonyme, sans page à prévisualiser
     * @param string $locale
     * @param string|null $slug
     * @return array{slug: ?string, locale: string, locales: string[], apiToken: string, userToken: string, user: null, preview: null, consent: null}
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getDatas(string $locale, ?string $slug = null): array
    {
        return [
            'slug' => $slug,
            'locale' => $locale,
            'locales' => $this->supportedLocales->getAll(),
            'apiToken' => $this->optionSystemFrontService->getFrontApiToken(),
            'userToken' => '',
            'user' => null,
            'preview' => null,
            'consent' => null,
        ];
    }

    /**
     * Gabarit d'aperçu du thème actif et son entrée Vite, ceux de Natheo-Base si le thème n'en a pas
     * @param string $preview MENU_PREVIEW ou TAG_PREVIEW
     * @return array{template: string, scriptsTag: string}
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getPreviewTemplate(string $preview): array
    {
        $template = $this->getPathTemplate() . '/' . $preview;
        if (is_file($this->getKernel()->getProjectDir() . '/templates/' . $template)) {
            return ['template' => $template, 'scriptsTag' => $this->getScriptTags()];
        }

        return [
            'template' => 'front/' . Template::NATHEO_BASE->value . '/' . $preview,
            'scriptsTag' => 'front_' . Template::NATHEO_BASE->value,
        ];
    }
}
