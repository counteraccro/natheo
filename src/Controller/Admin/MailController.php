<?php
/**
 * Gestionnaire des mails
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Controller\Admin;

use App\Entity\Admin\Mail;
use App\Entity\Admin\User;
use App\Service\Admin\Breadcrumb;
use App\Service\Admin\MailService;
use App\Service\Admin\MarkdownEditorService;
use App\Service\Admin\OptionSystemService;
use App\Service\Admin\OptionUserService;
use App\Service\Admin\TranslateService;
use App\Utils\Mail\KeyWord;
use App\Utils\Mail\MailKey;
use App\Utils\Mail\MailTemplate;
use App\Utils\Options\OptionSystemKey;
use App\Utils\Options\OptionUserKey;
use League\CommonMark\Exception\CommonMarkException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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
            'limit' => $this->optionUserService->getValueByKey(OptionUserKey::OU_NB_ELEMENT),
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
     * @param MailService $mailService
     * @param Mail $mail
     * @return JsonResponse
     */
    #[Route('/ajax/load-data/{id}', name: 'load_data', methods: ['POST'])]
    public function loadData(
        MarkdownEditorService $markdownEditorService,
        TranslateService      $translateService,
        Request               $request,
        TranslatorInterface   $translator,
        MailService           $mailService,
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
            'loading' => $translator->trans('mail.loading', domain: 'mail'),
            'titleTrans' => $translator->trans('mail.input.trans.title', domain: 'mail'),
            'msgEmptyTitle' => $translator->trans('mail.input.trans.title.empty', domain: 'mail'),
        ];

        try {
            $languages = $translateService->getListLanguages();
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            die($e->getMessage());
        }

        $tabEmail = $mailService->getMailFormat($locale, $mail);

        return $this->json(['translateEditor' => $markdownEditorService->getTranslate(),
            'languages' => $languages, 'locale' => $locale,
            'translate' => $translate, 'mail' => $tabEmail,
            'save_url' => $this->generateUrl('admin_mail_save', ['id' => $mail->getId()]),
            'demo_url' => $this->generateUrl('admin_mail_send_demo_mail', ['id' => $mail->getId()])]);
    }

    /**
     * Sauvegarde les données modifiées d'un mail
     * @param Request $request
     * @param MailService $mailService
     * @param TranslatorInterface $translator
     * @param Mail $mail
     * @return JsonResponse
     */
    #[Route('/ajax/save/{id}', name: 'save', methods: ['POST'])]
    public function save(
        Request             $request,
        MailService         $mailService,
        TranslatorInterface $translator,
        Mail                $mail
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $mailTranslation = $mail->geMailTranslationByLocale($data['locale']);
        $mailTranslation->setContent($data['content']);
        $mailTranslation->setTitle($data['title']);
        $mail->setUpdateAt(new \DateTime());
        $mailService->save($mail);

        $title = $translator->trans($mail->getTitle());
        return $this->json(['msg' => $translator->trans('mail.message.success', ['email' => $title], domain: 'mail')]);
    }

    /**
     * Permet de tester le contenu d'un email en l'envoyant
     * @param Mail $mail
     * @param MailService $mailService
     * @param OptionSystemService $optionSystemService
     * @param UrlGeneratorInterface $urlGenerator
     * @return JsonResponse
     * @throws CommonMarkException
     * @throws TransportExceptionInterface
     */
    #[Route('/ajax/send-demo-mail/{id}', name: 'send_demo_mail', methods: ['POST'])]
    public function sendDemoMail(
        Mail                  $mail,
        MailService           $mailService,
        OptionSystemService   $optionSystemService,
        UrlGeneratorInterface $urlGenerator,
        TranslatorInterface   $translator
    ): JsonResponse
    {
        /* @var User $user */
        $user = $this->getUser();
        $keyWord = new KeyWord($mail->getKey());

        $tabKeyWord = match ($mail->getKey()) {
            MailKey::KEY_MAIL_CHANGE_PASSWORD =>
            $keyWord->getMailChangePassword($user, $urlGenerator, $optionSystemService),
            MailKey::KEY_MAIL_ACCOUNT_ADM_DISABLE =>
            $keyWord->getTabMailAccountAdmDisabled($user, $user, $optionSystemService),
            MailKey::MAIL_ACCOUNT_ADM_ENABLE =>
            $keyWord->getTabMailAccountAdmEnabled($user, $user, $optionSystemService),
            MailKey::MAIL_CREATE_ACCOUNT_ADM =>
            $keyWord->getTabMailCreateAccountAdm($user, $user, $optionSystemService),
            MailKey::MAIL_SELF_DISABLED_ACCOUNT =>
            $keyWord->getTabMailSelfDisabled($user, $optionSystemService),
            MailKey::MAIL_SELF_DELETE_ACCOUNT =>
            $keyWord->getTabMailSelfDelete($user, $optionSystemService),
            MailKey::MAIL_SELF_ANONYMOUS_ACCOUNT =>
            $keyWord->getTabMailSelfAnonymous($user, $optionSystemService),
            default => [
                KeyWord::KEY_SEARCH => [],
                KeyWord::KEY_REPLACE => []
            ],
        };

        $mailTranslate = $mail->geMailTranslationByLocale($optionSystemService
            ->getValueByKey(OptionSystemKey::OS_DEFAULT_LANGUAGE));
        $content = str_replace(
            $tabKeyWord[KeyWord::KEY_SEARCH],
            $tabKeyWord[KeyWord::KEY_REPLACE],
            $mailTranslate->getContent()
        );

        $params = [
            MailService::TITLE => $mailTranslate->getTitle(),
            MailService::CONTENT => $content,
            MailService::TO => $user->getEmail(),
            MailService::TEMPLATE => MailTemplate::EMAIL_SIMPLE_TEMPLATE
        ];

        $mailService->sendMail($params);
        return $this->json([
            'type' => 'success',
            'msg' => 'Mail démo "' . $translator->trans($mail->getTitle()) . '" envoyé avec succès à l\'adresse email de votre compte'
        ]);
    }
}
