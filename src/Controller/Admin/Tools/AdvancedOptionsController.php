<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Controller pour les options avancées
 */

namespace App\Controller\Admin\Tools;

use App\Utils\Breadcrumb;
use App\Utils\Translate\Tools\AdvancedOptionsTranslate;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
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
            ]
        ]);
    }

    /**
     * Permet de changer la variable d'environnement
     * @param TranslatorInterface $translator
     * @return JsonResponse
     */
    #[Route('/ajax/switch-env', name: 'switch_env', methods: ['GET'])]
    public function switchEnv(TranslatorInterface $translator): JsonResponse
    {
        //$debug = $parameterBag->get('kernel.debug');
        //$env = $parameterBag->get('kernel.environment');

        //var_dump($env);

        /*$filesystem = new Filesystem();
        $contents = $filesystem->readFile('../.env');
        $contents = str_replace('APP_ENV=dev', 'APP_ENV=prod', $contents);

        //$filesystem->dumpFile('../.env-test-demo', $contents);
        var_dump($contents);*/

        $return['msg'] = $translator->trans('advanced_options.success.switch.env', domain: 'advanced_options');
        $return['success'] = true;

        return $this->json($return);
    }
}
