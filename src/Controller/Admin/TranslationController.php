<?php
/**
 * Translation controller, gestion des traductions
 * @author Gourdon Aymeric
 * @version 1.0
 */
namespace App\Controller\Admin;

use App\Service\Admin\Breadcrumb;
use App\Service\Admin\TranslateService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/{_locale}/translation', name: 'admin_translation_',
    requirements: ['_locale' => '%app.supported_locales%'])]
#[IsGranted('ROLE_SUPER_ADMIN')]
class TranslationController extends AppAdminController
{
    /**
     * Point d'entrée pour le module de traduction
     * @return Response
     */
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        $breadcrumb = [
            Breadcrumb::DOMAIN => 'translate',
            Breadcrumb::BREADCRUMB => [
                'translate.page_title_h1' => '#'
            ]
        ];

        return $this->render('admin/translation/index.html.twig', [
            'breadcrumb' => $breadcrumb,
        ]);
    }

    /**
     * Récupère la liste de langues
     * @param TranslatorInterface $translator
     * @return JsonResponse
     */
    #[Route('/ajax/languages', name: 'list_languages', methods: ['POST'])]
    public function loadLanguages(TranslatorInterface $translator, TranslateService $translateService): JsonResponse
    {
        $tabTranslate = [
            'translate_select_language' => $translator->trans('translate.select.language', domain: 'translate')
            ];

        try {
            $languages = $translateService->getListLanguages();
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            die($e->getMessage());
        }

        return $this->json(['trans' => $tabTranslate, 'languages' => $languages]);
    }

    /**
     * Récupère la liste des fichiers de traduction en fonction de la langue
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/ajax/files-translates', name: 'files_translate', methods: ['POST'])]
    public function loadFilesTranslates(Request $request, TranslateService $translateService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $files = $translateService->getTranslatationFileByLanguage($data['language']);

        return $this->json(['files' => $files]);
    }
}
