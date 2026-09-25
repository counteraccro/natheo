<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Controller pour les options avancées
 */

namespace App\Controller\Admin\Tools;

use App\Enum\Admin\Global\Breadcrumb;
use App\Service\Admin\CommandService;
use App\Utils\Global\EnvFile;
use App\Utils\Translate\Tools\AdvancedOptionsTranslate;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[
    Route(
        '/admin/{_locale}/advanced-options',
        name: 'admin_advanced_options_',
        requirements: ['_locale' => '%app.supported_locales%'],
    ),
]
#[IsGranted('ROLE_SUPER_ADMIN')]
class AdvancedOptionsController extends AbstractController
{
    /**
     * Point d'entrée des options avancées
     * @param AdvancedOptionsTranslate $advancedOptionsTranslate
     * @param ParameterBagInterface $parameterBag
     * @return Response
     */
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(
        AdvancedOptionsTranslate $advancedOptionsTranslate,
        ParameterBagInterface $parameterBag,
    ): Response {
        $breadcrumb = [
            Breadcrumb::DOMAIN->value => 'advanced_options',
            Breadcrumb::BREADCRUMB->value => [
                'advanced_options.index.page_title_h1' => '#',
            ],
        ];

        return $this->render('admin/tools/advanced_options/index.html.twig', [
            'breadcrumb' => $breadcrumb,
            'translate' => $advancedOptionsTranslate->getTranslate(),
            'data' => [
                'app_debug' => $parameterBag->get('kernel.debug'),
                'app_env' => $parameterBag->get('kernel.environment'),
            ],
            'urls' => [
                'switch_env' => $this->generateUrl('admin_advanced_options_switch_env'),
                'reset_data' => $this->generateUrl('admin_advanced_options_reset_data'),
                'reset_database' => $this->generateUrl('admin_advanced_options_reset_database'),
            ],
        ]);
    }

    /**
     * Permet de changer la variable d'environnement
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param EnvFile $envFile
     * @param CommandService $commandService
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/switch-env', name: 'switch_env', methods: ['POST'])]
    public function switchEnv(
        Request $request,
        TranslatorInterface $translator,
        EnvFile $envFile,
        CommandService $commandService,
    ): JsonResponse {
        if (!$this->isCsrfTokenValid('switch_env', $this->getCsrfToken($request))) {
            return $this->jsonError($translator->trans('advanced_options.error.csrf', domain: 'advanced_options'));
        }

        if (!$envFile->isAppEnvEditable()) {
            return $this->jsonError(
                $translator->trans('advanced_options.error.switch.env.not.editable', domain: 'advanced_options'),
            );
        }

        try {
            $envFile->switchAppEnv();
            $commandService->reloadCache();
        } catch (\RuntimeException $e) {
            return $this->jsonCommandError($translator, $e);
        }

        return $this->json([
            'msg' => $translator->trans('advanced_options.success.switch.env', domain: 'advanced_options'),
            'success' => true,
        ]);
    }

    /**
     * Réinstalle les données du site
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param CommandService $commandService
     * @param KernelInterface $kernel
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/reset-data', name: 'reset_data', methods: ['POST'])]
    public function resetData(
        Request $request,
        TranslatorInterface $translator,
        CommandService $commandService,
        KernelInterface $kernel,
    ): JsonResponse {
        if (!$kernel->isDebug()) {
            return $this->jsonError(
                $translator->trans('advanced_options.error.reset.data.not.allowed', domain: 'advanced_options'),
            );
        }

        if (!$this->isCsrfTokenValid('reset_data', $this->getCsrfToken($request))) {
            return $this->jsonError($translator->trans('advanced_options.error.csrf', domain: 'advanced_options'));
        }

        try {
            $commandService->dropDatabase();
            $commandService->createDatabase();
            $commandService->createSchema();
            $commandService->loadFixtures();
        } catch (\RuntimeException $e) {
            return $this->jsonCommandError($translator, $e);
        }

        return $this->json([
            'msg' => $translator->trans('advanced_options.success.reset.data', domain: 'advanced_options'),
            'success' => true,
        ]);
    }

    /**
     * Suppression de la base de données
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param CommandService $commandService
     * @param KernelInterface $kernel
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/reset-database', name: 'reset_database', methods: ['POST'])]
    public function resetDatabase(
        Request $request,
        TranslatorInterface $translator,
        CommandService $commandService,
        KernelInterface $kernel,
    ): JsonResponse {
        if (!$kernel->isDebug()) {
            return $this->jsonError(
                $translator->trans('advanced_options.error.reset.database.not.allowed', domain: 'advanced_options'),
            );
        }

        if (!$this->isCsrfTokenValid('reset_database', $this->getCsrfToken($request))) {
            return $this->jsonError($translator->trans('advanced_options.error.csrf', domain: 'advanced_options'));
        }

        try {
            $commandService->dropDatabase();
        } catch (\RuntimeException $e) {
            return $this->jsonCommandError($translator, $e);
        }

        return $this->json([
            'msg' => $translator->trans('advanced_options.success.reset.database', domain: 'advanced_options'),
            'redirect' => $this->generateUrl('front_no_local'),
            'success' => true,
        ]);
    }

    /**
     * Retourne le jeton CSRF envoyé en header ou dans le formulaire
     * @param Request $request
     * @return string|null
     */
    private function getCsrfToken(Request $request): ?string
    {
        return $request->headers->get('X-CSRF-TOKEN') ?? $request->request->get('_token');
    }

    /**
     * Retourne une réponse JSON d'erreur
     * @param string $msg
     * @param int $status
     * @return JsonResponse
     */
    private function jsonError(string $msg, int $status = Response::HTTP_FORBIDDEN): JsonResponse
    {
        return $this->json(['success' => false, 'msg' => $msg], $status);
    }

    /**
     * Retourne une réponse JSON d'erreur suite à l'échec d'une commande console
     * @param TranslatorInterface $translator
     * @param \RuntimeException $e
     * @return JsonResponse
     */
    private function jsonCommandError(TranslatorInterface $translator, \RuntimeException $e): JsonResponse
    {
        return $this->jsonError(
            $translator->trans('advanced_options.error.command', ['error' => $e->getMessage()], 'advanced_options'),
            Response::HTTP_INTERNAL_SERVER_ERROR,
        );
    }
}
