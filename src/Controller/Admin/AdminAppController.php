<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Page de l'application Vue du back-office et renouvellement de son access token
 */

namespace App\Controller\Admin;

use App\Service\Admin\App\AdminAppBootstrapService;
use Pentatrion\ViteBundle\Service\EntrypointsLookupCollection;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class AdminAppController extends AppAdminController
{
    /**
     * Page de l'application pour toute navigation sous /admin/<langue>/ qu'aucune page Twig ne sert. La racine renvoie
     * vers le tableau de bord tant qu'il est en Twig. Les appels AJAX et les autres méthodes gardent le 404 ou le 405
     * de Symfony
     * @param Request $request
     * @param AdminAppBootstrapService $bootstrapService
     * @param EntrypointsLookupCollection $entrypoints
     * @param string $path
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[
        Route(
            '/admin/{_locale}/{path}',
            name: 'admin_app',
            requirements: ['_locale' => '%app.supported_locales%', 'path' => '.*'],
            defaults: ['path' => ''],
            condition: "request.isMethod('GET') and 'text/html' in request.getAcceptableContentTypes()",
            priority: -100,
        ),
    ]
    public function index(
        Request $request,
        AdminAppBootstrapService $bootstrapService,
        EntrypointsLookupCollection $entrypoints,
        string $path,
    ): Response {
        if ($path === '') {
            return $this->redirectToRoute('admin_dashboard_index');
        }

        $nonce = base64_encode(random_bytes(16));
        $bootstrap = $bootstrapService->getBootstrap($request);
        $response = $this->render('admin/app.html.twig', [
            'locale' => $request->getLocale(),
            'siteName' => $bootstrap['site']['name'],
            'nonce' => $nonce,
            'bootstrap' => json_encode(
                $bootstrap,
                JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
            ),
        ]);

        // La page porte l'access token de l'utilisateur
        $response->headers->set('Cache-Control', 'no-store, private');
        $response->headers->set(
            'Content-Security-Policy',
            $this->buildContentSecurityPolicy($nonce, $entrypoints->getEntrypointsLookup()->getViteServer()),
        );
        return $response;
    }

    /**
     * Access token de la session API du back-office : celui en cours s'il reste valable, renouvelé sinon
     * @param Request $request
     * @param AdminAppBootstrapService $bootstrapService
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[
        Route(
            '/admin/{_locale}/app/token',
            name: 'admin_app_token',
            requirements: ['_locale' => '%app.supported_locales%'],
            methods: ['GET'],
        ),
    ]
    public function token(Request $request, AdminAppBootstrapService $bootstrapService): JsonResponse
    {
        $response = $this->json($bootstrapService->formatAccessToken($bootstrapService->getAccessToken($request)));
        $response->headers->set('Cache-Control', 'no-store, private');
        return $response;
    }

    /**
     * Politique de sécurité du contenu de la page : scripts du site avec le nonce de la page, serveur Vite en dev
     * @param string $nonce
     * @param string|null $viteServer origine du serveur Vite quand il tourne, null pour les fichiers construits
     * @return string
     */
    private function buildContentSecurityPolicy(string $nonce, ?string $viteServer): string
    {
        $vite = $viteServer === null ? [] : [rtrim($viteServer, '/')];
        $viteSocket = array_map(fn(string $origin): string => preg_replace('#^http#', 'ws', $origin), $vite);
        // Vite injecte les styles du serveur de dev dans des balises <style>
        $styles = $viteServer === null ? ["'self'"] : ["'self'", "'unsafe-inline'"];

        $directives = [
            'default-src' => ["'self'"],
            'script-src' => ["'self'", "'nonce-" . $nonce . "'", ...$vite],
            'style-src' => [...$styles, ...$vite],
            'img-src' => ["'self'", 'data:'],
            'font-src' => ["'self'", ...$vite],
            'connect-src' => ["'self'", ...$vite, ...$viteSocket],
            'object-src' => ["'none'"],
            'base-uri' => ["'self'"],
            'frame-ancestors' => ["'self'"],
            'form-action' => ["'self'"],
        ];

        return implode(
            '; ',
            array_map(
                fn(string $name, array $sources): string => $name . ' ' . implode(' ', $sources),
                array_keys($directives),
                $directives,
            ),
        );
    }
}
