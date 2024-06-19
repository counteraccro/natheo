<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Controller pour les options avancées
 */

namespace App\Controller\Admin\Tools;

use App\Service\Admin\CommandService;
use App\Service\Admin\Tools\AdvancedOptionsService;
use App\Utils\Breadcrumb;
use App\Utils\Translate\Tools\AdvancedOptionsTranslate;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[\Symfony\Component\Routing\Annotation\Route('/admin/{_locale}/advanced-options', name: 'admin_advanced_options_',
    requirements: ['_locale' => '%app.supported_locales%'])]
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
        ParameterBagInterface $parameterBag
    ): Response
    {

        $breadcrumb = [
            Breadcrumb::DOMAIN => 'advanced_options',
            Breadcrumb::BREADCRUMB => [
                'advanced_options.index.page_title_h1' => '#'
            ]
        ];

        return $this->render('admin/tools/advanced_options/index.html.twig', [
            'breadcrumb' => $breadcrumb,
            'translate' => $advancedOptionsTranslate->getTranslate(),
            'data' => [
                'app_debug' => $parameterBag->get('kernel.debug'),
                'app_env' => $parameterBag->get('kernel.environment')
            ],
            'urls' => [
                'switch_env' => $this->generateUrl('admin_advanced_options_switch_env'),
                'reset_data' => $this->generateUrl('admin_advanced_options_reset_data'),
                'reset_database' => $this->generateUrl('admin_advanced_options_reset_database'),
            ]
        ]);
    }

    /**
     * Permet de changer la variable d'environnement
     * @param TranslatorInterface $translator
     * @param AdvancedOptionsService $advancedOptionsService
     * @param CommandService $commandService
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/switch-env', name: 'switch_env', methods: ['GET'])]
    public function switchEnv(
        TranslatorInterface $translator,
        AdvancedOptionsService $advancedOptionsService,
        CommandService $commandService
    ): JsonResponse
    {
        $advancedOptionsService->switchEnv();
        $commandService->reloadCache();
        $return['msg'] = $translator->trans('advanced_options.success.switch.env', domain: 'advanced_options');
        $return['success'] = true;

        return $this->json($return);
    }

    /**
     * Réinstalle les données du site
     * @param TranslatorInterface $translator
     * @param CommandService $commandService
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/reset-data', name: 'reset_data', methods: ['GET'])]
    public function resetData(
        TranslatorInterface $translator,
        CommandService $commandService
    ): JsonResponse
    {
        set_time_limit(0);

        $commandService->dropDatabase();
        $commandService->createDatabase();
        $commandService->createSchema();
        $commandService->loadFixtures();

        $return['msg'] = $translator->trans('advanced_options.success.reset.data', domain: 'advanced_options');
        $return['success'] = true;

        return $this->json($return);
    }

    /**
     * Suppression de la base de données
     * @param TranslatorInterface $translator
     * @param CommandService $commandService
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/reset-database', name: 'reset_database', methods: ['GET'])]
    public function resetDatabase(
        TranslatorInterface $translator,
        CommandService $commandService
    )
    {
        set_time_limit(0);

        $commandService->dropDatabase();

        $return['msg'] = $translator->trans('advanced_options.success.reset.database', domain: 'advanced_options');
        $return['redirect'] = $this->generateUrl('index_index');
        $return['success'] = true;

        return $this->json($return);
    }
}
