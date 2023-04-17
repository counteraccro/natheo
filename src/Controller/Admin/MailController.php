<?php
/**
 * Gestionnaire des mails
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Controller\Admin;

use App\Entity\Admin\Mail;
use App\Service\Admin\Breadcrumb;
use App\Service\Admin\MailService;
use App\Service\Admin\MarkdownEditorService;
use App\Service\Admin\OptionUserService;
use App\Service\Admin\TranslateService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/{_locale}/mail', name: 'admin_mail_', requirements: ['_locale' => '%app.supported_locales%'])]
#[IsGranted('ROLE_SUPER_ADMIN')]
class MailController extends AppAdminController
{
    /**
     * Point d'entrée de la gestion des emails
     * @return Response
     */
    #[Route('/', name: 'index')]
    public function index(): Response
    {

        $breadcrumb = [
            Breadcrumb::DOMAIN => 'mail',
            Breadcrumb::BREADCRUMB => [
                'mail.page_title_h1' => '#'
            ]
        ];

        return $this->render('admin/mail/index.html.twig', [
            'breadcrumb' => $breadcrumb,
            'page' => 1,
            'limit' => $this->optionUserService->getValueByKey(OptionUserService::OU_NB_ELEMENT),
        ]);
    }

    /**
     * Charge le tableau grid de mail en ajax
     * @param Request $request
     * @param MailService $mailService
     * @return JsonResponse
     */
    #[Route('/ajax/load-grid-data', name: 'load_grid_data', methods: ['POST'])]
    public function loadGridData(Request $request, MailService $mailService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $grid = $mailService->getAllFormatToGrid($data['page'], $data['limit']);
        return $this->json($grid);
    }


    /**
     * @param Mail $mail
     * @return Response
     */
    #[Route('/edit/{id}', name: 'edit')]
    public function edit(Mail $mail): Response
    {
        $breadcrumb = [
            Breadcrumb::DOMAIN => 'mail',
            Breadcrumb::BREADCRUMB => [
                'mail.page_title_h1' => 'admin_mail_index',
                'mail.edit_page_title_h1' => '#'
            ]
        ];

        return $this->render('admin/mail/edit.html.twig', [
            'breadcrumb' => $breadcrumb,
            'mail' => $mail
        ]);
    }

    /**
     * Charge les données pour les emails en ajax en fonction de la langue
     * @param MarkdownEditorService $markdownEditorService
     * @param TranslateService $translateService
     * @param Request $request
     * @param TranslatorInterface $translator
     * @return JsonResponse
     */
    #[Route('/ajax/load-data/{id}', name: 'load_data', methods: ['POST'])]
    public function loadData(MarkdownEditorService $markdownEditorService,
                             TranslateService      $translateService, Request $request,
                             TranslatorInterface   $translator, MailService $mailService,
                             Mail                  $mail
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $locale = $data['locale'];
        if ($locale === null) {
            $locale = $request->getLocale();
        }

        $translate = [
            'listLanguage' => $translator->trans('mail.list.language', domain: 'mail'),
            'loading' => $translator->trans('mail.loading', domain: 'mail')
        ];

        try {
            $languages = $translateService->getListLanguages();
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            die($e->getMessage());
        }

        $tabEmail = $mailService->getMailFormat($locale, $mail);

        return $this->json(['translateEditor' => $markdownEditorService->getTranslate(),
            'languages' => $languages, 'locale' => $locale, 'translate' => $translate, 'mail' => $tabEmail]);
    }
}
