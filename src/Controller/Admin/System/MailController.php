<?php

declare(strict_types=1);
/**
 * Gestionnaire des mails
 * @author Gourdon Aymeric
 * @version 2.1
 */

namespace App\Controller\Admin\System;

use App\Controller\Admin\AppAdminController;
use App\Entity\Admin\System\Mail;
use App\Entity\Admin\System\User;
use App\Enum\Admin\Global\Breadcrumb;
use App\Enum\Admin\System\Options\OptionUser;
use App\Service\Admin\System\MailService;
use App\Service\Admin\System\TranslateService;
use App\Utils\Translate\MarkdownEditorTranslate;
use App\Utils\Translate\System\MailTranslate;
use League\CommonMark\Exception\CommonMarkException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/{_locale}/mail', name: 'admin_mail_', requirements: ['_locale' => '%app.supported_locales%'])]
#[IsGranted('ROLE_SUPER_ADMIN')]
class MailController extends AppAdminController
{
    /**
     * Identifiant du jeton CSRF pour la sauvegarde d'un email
     */
    public const string CSRF_TOKEN_SAVE = 'mail_save';

    /**
     * Point d'entrée de la gestion des emails
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        $breadcrumb = [
            Breadcrumb::DOMAIN->value => 'mail',
            Breadcrumb::BREADCRUMB->value => [
                'mail.page_title_h1' => '#',
            ],
        ];

        return $this->render('admin/system/mail/index.html.twig', [
            'breadcrumb' => $breadcrumb,
            'page' => 1,
            'limit' => $this->optionUserService->getValueByKey(OptionUser::OU_NB_ELEMENT->value),
        ]);
    }

    /**
     * Charge le tableau grid de mail en ajax
     * @param Request $request
     * @param MailService $mailService
     * @param int $page
     * @param int $limit
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/load-grid-data/{page}/{limit}', name: 'load_grid_data', methods: ['GET'])]
    public function loadGridData(
        Request $request,
        MailService $mailService,
        int $page = 1,
        int $limit = 20,
    ): JsonResponse {
        $queryParams = [
            'search' => $request->query->get('search'),
            'orderField' => $request->query->get('orderField'),
            'order' => $request->query->get('order'),
            'locale' => $request->getLocale(),
        ];

        $grid = $mailService->getAllFormatToGrid($page, $limit, $queryParams);
        return $this->json($grid);
    }

    /**
     * Edition d'un email
     * @param Mail|null $mail
     * @return Response
     */
    #[Route('/edit/{id}', name: 'edit')]
    public function edit(#[MapEntity(id: 'id')] ?Mail $mail = null): Response
    {
        $breadcrumb = [
            Breadcrumb::DOMAIN->value => 'mail',
            Breadcrumb::BREADCRUMB->value => [
                'mail.page_title_h1' => 'admin_mail_index',
                'mail.edit_page_title_h1' => '#',
            ],
        ];

        return $this->render('admin/system/mail/edit.html.twig', [
            'breadcrumb' => $breadcrumb,
            'mail' => $mail,
            'csrfTokenSave' => self::CSRF_TOKEN_SAVE,
            'csrfTokenSendDemo' => MailService::CSRF_TOKEN_SEND_DEMO,
        ]);
    }

    /**
     * Charge les données pour les emails en ajax en fonction de la langue
     * @param MarkdownEditorTranslate $markdownEditorTranslate
     * @param TranslateService $translateService
     * @param MailService $mailService
     * @param MailTranslate $mailTranslate
     * @param TranslatorInterface $translator
     * @param Request $request
     * @param Mail $mail
     * @param string|null $locale si null, la langue courante de l'interface
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[
        Route(
            '/ajax/load-data/{id}/{locale?}',
            name: 'load_data',
            requirements: ['locale' => '%app.supported_locales%'],
            methods: ['GET'],
        ),
    ]
    public function loadData(
        MarkdownEditorTranslate $markdownEditorTranslate,
        TranslateService $translateService,
        MailService $mailService,
        MailTranslate $mailTranslate,
        TranslatorInterface $translator,
        Request $request,
        #[MapEntity(id: 'id')] Mail $mail,
        ?string $locale = null,
    ): JsonResponse {
        $locale ??= $request->getLocale();
        if ($mail->getMailTranslationByLocale($locale) === null) {
            return $this->jsonError($translator->trans('mail.error.locale', domain: 'mail'), Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'translateEditor' => $markdownEditorTranslate->getTranslate(),
            'languages' => $translateService->getListLanguages(),
            'locale' => $locale,
            'translate' => $mailTranslate->getTranslate(),
            'mail' => $mailService->getMailFormat($locale, $mail),
            'save_url' => $this->generateUrl('admin_mail_save', ['id' => $mail->getId()]),
            'demo_url' => $this->generateUrl('admin_mail_send_demo_mail', ['id' => $mail->getId()]),
        ]);
    }

    /**
     * Sauvegarde les données modifiées d'un mail
     * @param Request $request
     * @param MailService $mailService
     * @param TranslatorInterface $translator
     * @param Mail $mail
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/save/{id}', name: 'save', methods: ['POST'])]
    public function save(
        Request $request,
        MailService $mailService,
        TranslatorInterface $translator,
        #[MapEntity(id: 'id')] Mail $mail,
    ): JsonResponse {
        if (!$this->isCsrfTokenValid(self::CSRF_TOKEN_SAVE, $request->headers->get('X-CSRF-TOKEN'))) {
            return $this->jsonError($translator->trans('mail.error.csrf', domain: 'mail'), Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);
        $title = trim((string) ($data['title'] ?? ''));
        $content = trim((string) ($data['content'] ?? ''));
        $mailTranslation = is_string($data['locale'] ?? null)
            ? $mail->getMailTranslationByLocale($data['locale'])
            : null;

        if ($mailTranslation === null || $title === '' || $content === '') {
            return $this->jsonError($translator->trans('mail.error.save', domain: 'mail'), Response::HTTP_BAD_REQUEST);
        }

        $mailTranslation->setTitle($title);
        $mailTranslation->setContent($content);
        // Seule la traduction change : force la mise à jour de la date de l'email
        $mail->setUpdateAt(new \DateTime());
        $mailService->save($mail);

        $msg = $translator->trans(
            'mail.message.success',
            ['email' => $translator->trans($mail->getTitle())],
            domain: 'mail',
        );
        return $this->json($mailService->getResponseAjax($msg));
    }

    /**
     * Permet de tester le contenu d'un email en l'envoyant à l'utilisateur courant.
     * Body JSON optionnel {locale, title, content} pour tester une version non sauvegardée
     * @param Request $request
     * @param Mail $mail
     * @param MailService $mailService
     * @param TranslatorInterface $translator
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/send-demo-mail/{id}', name: 'send_demo_mail', methods: ['POST'])]
    public function sendDemoMail(
        Request $request,
        #[MapEntity(id: 'id')] Mail $mail,
        MailService $mailService,
        TranslatorInterface $translator,
    ): JsonResponse {
        if (!$this->isCsrfTokenValid(MailService::CSRF_TOKEN_SEND_DEMO, $request->headers->get('X-CSRF-TOKEN'))) {
            return $this->jsonError($translator->trans('mail.error.csrf', domain: 'mail'), Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);
        $data = is_array($data) ? $data : [];
        $locale = is_string($data['locale'] ?? null) ? $data['locale'] : null;

        if ($locale !== null && $mail->getMailTranslationByLocale($locale) === null) {
            return $this->jsonError(
                $translator->trans('mail.error.locale', domain: 'mail'),
                Response::HTTP_BAD_REQUEST,
            );
        }

        /** @var User $user */
        $user = $this->getUser();
        $title = $translator->trans($mail->getTitle());

        try {
            $mailService->sendDemoMail(
                $mail,
                $user,
                $locale,
                is_string($data['title'] ?? null) ? trim($data['title']) : null,
                is_string($data['content'] ?? null) ? $data['content'] : null,
            );
        } catch (TransportExceptionInterface | CommonMarkException $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e, 'mail' => $mail->getKey()]);
            return $this->jsonError(
                $translator->trans('mail.demo.error', ['email' => $title], domain: 'mail'),
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }

        return $this->json([
            'success' => true,
            'msg' => $translator->trans('mail.demo.success', ['email' => $title], domain: 'mail'),
        ]);
    }

    /**
     * Retourne une réponse JSON d'erreur
     * @param string $msg
     * @param int $status
     * @return JsonResponse
     */
    private function jsonError(string $msg, int $status): JsonResponse
    {
        return $this->json(['success' => false, 'msg' => $msg], $status);
    }
}
