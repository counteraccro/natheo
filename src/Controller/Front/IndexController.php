<?php
/**
 * Index, point d'entrée sur le site
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Controller\Front;

use App\Entity\Admin\System\User;
use App\Utils\Translate\Front\FrontTranslate;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/', name: 'front_', requirements: ['_locale' => '%app.supported_locales%'],
    defaults: ["_locale" => "%app.default_locale%"])]
class IndexController extends AppFrontController
{
    /**
     * Route qui sert uniquement à redirigé vers la connexion avec la bonne locale
     * @return RedirectResponse
     */
    #[Route('/', name: 'no_local')]
    public function indexNoLocale(): RedirectResponse
    {
        $defaultLocal = $this->getParameter('app.default_locale');
        return $this->redirectToRoute('front_index', ['locale' => $defaultLocal, 'slug' => null]);
    }

    /**
     * Redirige vers la connexion
     * @param Request $request
     * @param FrontTranslate $frontTranslate
     * @param string|null $locale
     * @param string|null $slug
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/{locale}/{slug}', name: 'index')]
    #[Route('/{locale}/{category}/{slug}', name: 'index_2', requirements: ['category' => 'faq|page|article|projet|blog|evenement|documentation'])]
    public function index(Request $request, FrontTranslate $frontTranslate, ?string $locale = '', ?string $slug = null): Response
    {
        if (!$this->installationService->checkSchema()) {
            return $this->redirectToRoute('installation_step_1');
        }

        if (!$this->installationService->checkDataExiste(User::class)) {
            return $this->redirectToRoute('installation_step_2');
        }

        if(!$this->isOpenSite()) {
            return $this->render($this->getPathTemplate() . DIRECTORY_SEPARATOR . 'close.html.twig');
        }

        if($locale === '') {
            $locale = $this->getParameter('app.default_locale');
        }

        $version = $this->getParameter('app.api_version');

        $urls = [
            'apiPageFind' => $this->generateUrl('api_page_find', ['api_version' => $version]),
            'apiOptionsSystems' => $this->generateUrl('api_options_systems_listing', ['api_version' => $version]),
            'adminAuth' => $this->generateUrl('admin_dashboard_index')
        ];

        $datas = [
            'slug' => $slug,
            'locale' => $locale,
            'pageCategories' => $this->pageService->getAllCategories(),
        ];

        return $this->render($this->getPathTemplate() . DIRECTORY_SEPARATOR . 'index.html.twig', ['urls' => $urls, 'datas' => $datas, "translate" => $frontTranslate->getTranslate()]);
    }
}
