<?php

declare(strict_types=1);
/**
 * Index, point d'entrée sur le site
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Controller\Front;

use App\Entity\Admin\Content\Page\PageMeta;
use App\Entity\Admin\System\User;
use App\Service\Api\Content\Page\ApiPageService;
use App\Service\Api\Global\ApiSitemapService;
use App\Service\Api\System\User\UserSessionService;
use App\Service\Front\FrontPreviewService;
use App\Service\Global\User\AvatarUrl;
use App\Translate\Front\FrontTranslate;
use League\CommonMark\Exception\CommonMarkException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Translation\LocaleSwitcher;

#[Route('/', name: 'front_')]
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

        $xml = $this->renderView('front/sitemap.xml.twig', [
            'urls' => $apiSitemapService->getSitemap(),
            'hostname' => $hostname,
        ]);
        return new Response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
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
     * Affiche une page du site
     * @param Request $request
     * @param UserSessionService $userSessionService
     * @param ApiPageService $apiPageService
     * @param FrontPreviewService $frontPreviewService
     * @param FrontTranslate $frontTranslate
     * @param ContainerBagInterface $containerBag
     * @param string $locale
     * @param string|null $slug
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \DateMalformedStringException
     * @throws CommonMarkException
     * @throws ExceptionInterface
     */
    #[
        Route(
            '/{locale}/{category}/{slug}',
            name: 'index_2',
            requirements: [
                'locale' => '%app.supported_locales%',
                'category' => '|faq|page|article|projet|blog|evenement|news|documentation|evolution',
            ],
        ),
    ]
    public function index(
        Request $request,
        UserSessionService $userSessionService,
        ApiPageService $apiPageService,
        FrontPreviewService $frontPreviewService,
        FrontTranslate $frontTranslate,
        ContainerBagInterface $containerBag,
        string $locale,
        ?string $slug = null,
    ): Response {
        if (!$this->installationService->checkSchema()) {
            return $this->redirectToRoute('installation_step_0');
        }

        if (!$this->installationService->checkDataExiste(User::class)) {
            return $this->redirectToRoute('installation_step_2');
        }

        if (!$this->isOpenSite()) {
            return $this->render(
                $this->getPathTemplate() . DIRECTORY_SEPARATOR . 'close.html.twig',
                ['scriptsTag' => $this->getScriptTags(), 'locale' => $locale],
                new Response(status: Response::HTTP_SERVICE_UNAVAILABLE),
            );
        }

        /** @var User|null $user */
        $user = $this->getUser();
        $editablePage = $user === null ? null : $apiPageService->findEditablePageBySlug((string) $slug, $locale, $user);
        $avatarPath = (string) $containerBag->get('app.path.avatar');
        $locales = explode('|', (string) $this->getParameter('app.supported_locales'));

        $urls = [
            'api' => $this->getParameter('app.api_v2.prefix'),
            'adminAuth' => $this->generateUrl('admin_dashboard_index'),
            'sitemap' => $this->generateUrl('front_sitemap'),
            'logout' => $this->generateUrl('auth_logout'),
            'home' => array_combine(
                $locales,
                array_map(
                    fn(string $homeLocale) => $this->generateUrl('front_index_2', [
                        'locale' => $homeLocale,
                        'category' => null,
                        'slug' => null,
                    ]),
                    $locales,
                ),
            ),
        ];

        $datas = [
            'slug' => $slug,
            'locale' => $locale,
            'locales' => $locales,
            'apiToken' => $this->optionSystemFrontService->getFrontApiToken(),
            'userToken' => '',
            'user' => null,
            'pathAvatar' => AvatarUrl::directory($avatarPath, $request->getBasePath()),
            'preview' => null,
        ];

        if ($user !== null) {
            $datas['userToken'] = $userSessionService->getFrontAccessToken(
                $user,
                $datas['apiToken'],
                $request->getSession(),
                $request->headers->get('User-Agent'),
                $request->getClientIp(),
            );
            $datas['user'] = [
                'login' => $user->getLogin(),
                'avatar' => AvatarUrl::file($user->getAvatar(), $avatarPath, $request->getBasePath()),
                'canModerate' => $this->isGranted('ROLE_CONTRIBUTEUR'),
                'editUrl' =>
                    $editablePage === null
                        ? null
                        : $this->generateUrl('admin_page_update', [
                            '_locale' => $locale,
                            'id' => $editablePage->getId(),
                        ]),
            ];
        }

        if ($editablePage !== null) {
            $datas['preview'] = $frontPreviewService->getPreview(
                $editablePage,
                $locale,
                $request->query->getBoolean('preview'),
            );
        }

        return $this->render($this->getPathTemplate() . DIRECTORY_SEPARATOR . 'index.html.twig', [
            'urls' => $urls,
            'datas' => $datas,
            'translate' => $frontTranslate->getTranslate($locale),
            'scriptsTag' => $this->getScriptTags(),
            'metaSeo' => $this->getMetaSeo($locale, $slug),
            'locale' => $locale,
        ]);
    }

    /**
     * Balises meta de la page (SEO de la page et consignes aux robots)
     * @param string $locale
     * @param string|null $slug
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getMetaSeo(string $locale, ?string $slug): array
    {
        $seoPage = $this->getRepository(PageMeta::class)->getMetasByPageAndLocale($locale, $slug);
        return array_merge($seoPage, $this->optionSystemFrontService->getMetaRobots(true));
    }
}
