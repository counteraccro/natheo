<?php
/**
 * Index, point d'entrée sur le site
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Controller\Front;

use App\Entity\Admin\Content\Page\PageMeta;
use App\Entity\Admin\System\User;
use App\Service\Admin\System\User\UserDataService;
use App\Service\Admin\System\User\UserService;
use App\Service\Api\Global\ApiSitemapService;
use App\Utils\Translate\Front\FrontTranslate;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\LocaleSwitcher;

#[Route('/', name: 'front_', requirements: ['_locale' => '%app.supported_locales%'],
    defaults: ["_locale" => "%app.default_locale%"])]
class IndexController extends AppFrontController
{

    /**
     * Génération du sitemap du site
     * @param Request $request
     * @param ApiSitemapService $apiSitemapService
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/sitemap.xml', name: 'sitemap', format: 'xml')]
    public function sitemap(Request $request, ApiSitemapService $apiSitemapService): Response
    {
        $hostname = $request->getSchemeAndHttpHost();

        $xml = $this->renderView($this->getPathTemplate() . DIRECTORY_SEPARATOR . 'sitemap.xml.twig', [
            'urls' => $apiSitemapService->getSitemap(),
            'hostname' => $hostname
        ]);
        return new Response($xml, 200, ['Content-Type' => 'text/xml']);
    }

    /**
     * Route qui sert uniquement à redirigé vers la connexion avec la bonne locale
     * @return RedirectResponse
     */
    #[Route('/', name: 'no_local')]
    public function indexNoLocale(): RedirectResponse
    {
        $defaultLocal = $this->getParameter('app.default_locale');
        return $this->redirectToRoute('front_index_2', ['locale' => $defaultLocal, 'category' => null, 'slug' => null]);
    }

    /**
     * Redirige vers la connexion
     * @param UserDataService $userDataService
     * @param FrontTranslate $frontTranslate
     * @param string|null $locale
     * @param string|null $slug
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \DateMalformedStringException
     *
     */
    #[Route('/{locale}/{category}/{slug}', name: 'index_2', requirements: ['category' => '|faq|page|article|projet|blog|evenement|documentation|evolution'])]
    public function index(
        UserDataService $userDataService,
        FrontTranslate  $frontTranslate,
        LocaleSwitcher $localeSwitcher,
        ContainerBagInterface $containerBag,
        ?string         $locale = '',
        ?string         $slug = null): Response
    {

        $localeSwitcher->setLocale($locale);

        if (!$this->installationService->checkSchema()) {
            return $this->redirectToRoute('installation_step_1');
        }

        if (!$this->installationService->checkDataExiste(User::class)) {
            return $this->redirectToRoute('installation_step_2');
        }

        if (!$this->isOpenSite()) {
            return $this->render($this->getPathTemplate() . DIRECTORY_SEPARATOR . 'close.html.twig', ['scriptsTag' => $this->getScriptTags()]);
        }

        if ($locale === '') {
            $locale = $this->getParameter('app.default_locale');
        }

        $version = $this->getParameter('app.api_version');

        $urls = [
            'apiPageFind' => $this->generateUrl('api_page_find', ['api_version' => $version]),
            'apiOptionsSystems' => $this->generateUrl('api_options_systems_listing', ['api_version' => $version]),
            'apiPageContent' => $this->generateUrl('api_page_content', ['api_version' => $version]),
            'adminAuth' => $this->generateUrl('admin_dashboard_index'),
            'sitemap' => $this->generateUrl('front_sitemap'),
            'logout' => $this->generateUrl('auth_logout'),
            'indexFr' => $this->generateUrl('front_index_2', ['locale' => 'fr', 'category' => null, 'slug' => null]),
            'indexEs' => $this->generateUrl('front_index_2', ['locale' => 'es', 'category' => null, 'slug' => null]),
            'indexEn' => $this->generateUrl('front_index_2', ['locale' => 'en', 'category' => null, 'slug' => null]),
            'apiCommentsByPage' => $this->generateUrl('api_comment_by_page', ['api_version' => $version]),
            'apiAddComment' => $this->generateUrl('api_comment_add_comment', ['api_version' => $version]),
            'apiModerateComment' => $this->generateUrl('api_comment_moderate_comment', ['api_version' => $version, 'id' => 0]),
        ];

        /** @var User $user */
        $user = $this->getUser();
        $token = '';
        $userInfo = [];
        if ($user != null) {
            $token = $userDataService->generateUserToken($user);
            $userInfo = [
                'login' => $user->getLogin(),
                'avatarImg' => $user->getAvatar(),
                'avatar' => substr(ucfirst($user->getLogin()), 0, 1),
                'pathImgAvatar' => $containerBag->get('app.path.avatar')
            ];
        }

        $datas = [
            'slug' => $slug,
            'locale' => $locale,
            'pageCategories' => $this->pageService->getAllCategories(),
            'userToken' => $token,
            'userInfo' => $userInfo,
        ];

        $seoRobots = $this->optionSystemFrontService->getMetaRobots(true);
        $pageMetaRepo = $this->getRepository(PageMeta::class);
        $seoPage = $pageMetaRepo->getMetasByPageAndLocale($locale, $slug);
        $seo = array_merge($seoPage, $seoRobots);

        return $this->render($this->getPathTemplate() . DIRECTORY_SEPARATOR . 'index.html.twig',
            [
                'urls' => $urls,
                'datas' => $datas,
                'translate' => $frontTranslate->getTranslate(),
                'scriptsTag' => $this->getScriptTags(),
                'metaSeo' => $seo,
            ]);
    }
}
