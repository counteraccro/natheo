<?php

/**
 * User
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Controller\Admin;

use App\Entity\Admin\User;
use App\Form\Admin\User\MyAccountType;
use App\Service\Admin\Breadcrumb;
use App\Service\Admin\MailService;
use App\Service\Admin\OptionSystemService;
use App\Service\Admin\OptionUserService;
use App\Service\Admin\UserService;
use App\Utils\Mail\KeyWord;
use App\Utils\Mail\MailKey;
use App\Utils\Mail\MailTemplate;
use App\Utils\Options\OptionSystemKey;
use App\Utils\Options\OptionUserKey;
use App\Utils\User\Anonymous;
use App\Utils\User\Role;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/{_locale}/user', name: 'admin_user_', requirements: ['_locale' => '%app.supported_locales%'])]
class UserController extends AppAdminController
{
    /**
     * point d'entrée
     * @return Response
     */
    #[Route('/', name: 'index')]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function index(): Response
    {
        $breadcrumb = [
            Breadcrumb::DOMAIN => 'user',
            Breadcrumb::BREADCRUMB => [
                'user.page_title_h1' => '#'
            ]
        ];

        return $this->render('admin/user/index.html.twig', [
            'breadcrumb' => $breadcrumb,
            'page' => 1,
            'limit' => $this->optionUserService->getValueByKey(OptionUserKey::OU_NB_ELEMENT),
        ]);
    }

    /**
     * Permet au user courant (connecté) de pouvoir gérer ses options
     * @return Response
     */
    #[Route('/my-option', name: 'my_option')]
    #[IsGranted('ROLE_USER')]
    public function userOption(): Response
    {
        $breadcrumb = [
            Breadcrumb::DOMAIN => 'user',
            Breadcrumb::BREADCRUMB => [
                'user.my_option_title_h1' => '#'
            ]
        ];

        return $this->render('admin/user/my_options.html.twig', [
            'breadcrumb' => $breadcrumb,
        ]);
    }

    /**
     * Met à jour une option
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/ajax/update', name: 'ajax_update_my_option', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function updateMyOption(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $this->optionUserService->saveValueByKee($data['key'], $data['value']);
        return $this->json(['success' => 'true']);
    }

    /**
     * Charge le tableau grid de user en ajax
     * @param Request $request
     * @param UserService $userService
     * @return JsonResponse
     */
    #[Route('/ajax/load-grid-data', name: 'load_grid_data', methods: ['POST'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function loadGridData(Request $request, UserService $userService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $grid = $userService->getAllFormatToGrid($data['page'], $data['limit']);
        return $this->json($grid);
    }

    /**
     * Disabled ou non un user
     * @param User $user
     * @param UserService $userService
     * @param TranslatorInterface $translator
     * @return JsonResponse
     */
    #[Route('/ajax/update-disabled/{id}', name: 'update_disabled', methods: ['POST'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function updateDisabled(User $user, UserService $userService, TranslatorInterface $translator): JsonResponse
    {
        $role = new Role($user);
        if ($role->isSuperAdmin()) {
            return $this->json([
                'type' => 'success',
                'msg' => $translator->trans('user.error_not_disabled', domain: 'user')
            ]);
        }

        $user->setDisabled(!$user->isDisabled());
        $userService->save($user);

        $msg = $translator->trans('user.success.no.disabled', ['login' => $user->getLogin()], 'user');
        if ($user->isDisabled()) {
            $msg = $translator->trans('user.success.disabled', ['login' => $user->getLogin()], 'user');
        }
        return $this->json(['type' => 'success', 'msg' => $msg]);
    }

    #[Route('/ajax/delete/{id}', name: 'delete', methods: ['POST'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function delete(
        User                $user,
        UserService         $userService,
        TranslatorInterface $translator,
        OptionSystemService $optionSystemService
    ): JsonResponse
    {
        $role = new Role($user);
        if ($role->isSuperAdmin()) {
            $msg = $translator->trans('user.error_not_disabled', domain: 'user');
        } else {
            $canDelete = $optionSystemService->getValueByKey(OptionSystemKey::OS_ALLOW_DELETE_DATA);
            $canReplace = $optionSystemService->getValueByKey(OptionSystemKey::OS_REPLACE_DELETE_USER);

            if ($canDelete === '1') {
                if ($canReplace === '1') {
                    $userService->anonymizer($user);
                    $msg = $translator->trans('user.success_anonymous', domain: 'user');
                } else {
                    $userService->remove($user);
                    $msg = $translator->trans('user.success_remove', domain: 'user');
                }
            } else {
                $msg = $translator->trans('user.error_not_allowed', domain: 'user');
            }
        }
        return $this->json(['type' => 'success', 'msg' => $msg]);
    }

    /**
     * Permet de modifier un user
     * @param User $user
     * @param UserService $userService
     * @return Response
     */
    #[Route('/update/{id}', name: 'update', methods: ['GET'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function update(User $user, UserService $userService): Response
    {
        $breadcrumb = [
            Breadcrumb::DOMAIN => 'user',
            Breadcrumb::BREADCRUMB => [
                'user.page_title_h1' => 'admin_user_index',
                'user.page_update_title_h1' => '#'
            ]
        ];

        return $this->render('admin/user/update.html.twig', [
            'breadcrumb' => $breadcrumb,
        ]);
    }

    /**
     * Mise à jour des données de l'utilisateur par lui-même
     * @param UserService $userService
     * @param Request $request
     * @param OptionSystemService $optionSystemService
     * @return Response
     */
    #[Route('/my-account', name: 'my_account', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function updateMyAccount(
        UserService         $userService,
        Request             $request,
        OptionSystemService $optionSystemService
    ): Response
    {
        $breadcrumb = [
            Breadcrumb::DOMAIN => 'user',
            Breadcrumb::BREADCRUMB => [
                'user.page_my_account.title_h1' => '#'
            ]
        ];

        /** @var User $user */
        $user = $this->getUser();
        $form = $this->createForm(MyAccountType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $userService->save($user);
        }

        $canDelete = $optionSystemService->getValueByKey(OptionSystemKey::OS_ALLOW_DELETE_DATA);
        $canReplace = $optionSystemService->getValueByKey(OptionSystemKey::OS_REPLACE_DELETE_USER);

        return $this->render('admin/user/my_account.html.twig', [
            'breadcrumb' => $breadcrumb,
            'form' => $form,
            'dateUpdate' => $user->getFormatDate(),
            'changePasswordTranslate' => $userService->getTranslateChangePassword(),
            'dangerZoneTranslate' => $userService->getTranslateDangerZone(),
            'canDelete' => $canDelete,
            'canReplace' => $canReplace
        ]);
    }

    /**
     * Changement de mot passe
     * @param UserService $userService
     * @param Request $request
     * @param TranslatorInterface $translator
     * @return JsonResponse
     */
    #[Route('/change-my-password', name: 'change_my_password', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function updatePassword(
        UserService         $userService,
        Request             $request,
        TranslatorInterface $translator
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        /* @var User $user */
        $user = $this->getUser();
        $userService->updatePassword($user, $data['data']);

        return $this->json([
            'status' => 'success',
            'msg' => $translator->trans('user.update_password.success', domain: 'user')
        ]);
    }

    /**
     * Permet à l'utilisateur de s'auto désactivé
     * @param TranslatorInterface $translator
     * @param UserService $userService
     * @param MailService $mailService
     * @return JsonResponse
     */
    #[Route('/ajax/self-disabled', name: 'self_disabled', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function selfDisabled(
        TranslatorInterface $translator,
        UserService         $userService,
        MailService         $mailService,
        OptionSystemService $optionSystemService
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $role = new Role($user);
        if ($role->isSuperAdmin()) {
            $msg = $translator->trans('user.error_not_disabled', domain: 'user');
            $url = '';
        } else {
            $user->setDisabled(true);
            $userService->save($user);
            $msg = $translator->trans('user.self_disabled_success', domain: 'user');
            $url = $this->generateUrl('auth_logout');
        }


        if ($optionSystemService->isSendMailNotification()) {
            $mail = $mailService->getByKey(MailKey::MAIL_SELF_DISABLED_ACCOUNT);
            $keyWord = new KeyWord($mail->getKey());
            $tabKeyWord = $keyWord->getTabMailSelfDisabled($user, $optionSystemService);
            $params = $mailService->getDefaultParams($mail, $tabKeyWord);
            $params[MailService::TO] = $userService->getTabMailByListeUser(
                $userService->getByRole(Role::ROLE_SUPER_ADMIN)
            );
            $mailService->sendMail($params);
        }


        return $this->json([
            'status' => 'success',
            'msg' => $msg,
            'redirect' => $url
        ]);
    }

    /**
     * Permet à l'utilisateur de s'auto supprimer
     * @param TranslatorInterface $translator
     * @param UserService $userService
     * @param OptionSystemService $optionSystemService
     * @return JsonResponse
     * @throws Exception
     */
    #[Route('/ajax/self-delete', name: 'self_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function selfDelete(
        TranslatorInterface $translator,
        UserService         $userService,
        MailService         $mailService,
        OptionSystemService $optionSystemService
    ): JsonResponse
    {
        $status = 0;

        /** @var User $user */
        $user = $this->getUser();
        $user2 = clone $user;
        $role = new Role($user);
        $url = '';
        if ($role->isSuperAdmin()) {
            $msg = $translator->trans('user.error_not_disabled', domain: 'user');
        } else {

            if ($optionSystemService->canDelete()) {
                if ($optionSystemService->canReplace()) {
                    $status = 1;
                    $userService->anonymizer($user);
                    $msg = $translator->trans('user.danger_zone.success_anonymous', domain: 'user');
                    $url = $this->generateUrl('auth_logout');
                } else {
                    $status = 2;
                    $userService->remove($user);
                    $msg = $translator->trans('user.danger_zone.success_remove', domain: 'user');
                    $url = $this->generateUrl('index_index');
                }
            } else {
                $msg = $translator->trans('user.error_not_allowed', domain: 'user');
            }
        }

        if ($optionSystemService->canSendMailNotification()) {
            if ($status === 1) { // anonymisation
                $mail = $mailService->getByKey(MailKey::MAIL_SELF_ANONYMOUS_ACCOUNT);
                $keyWord = new KeyWord($mail->getKey());
                $tabKeyWord = $keyWord->getTabMailSelfAnonymous($user2, $optionSystemService);
            } else { // delete
                $mail = $mailService->getByKey(MailKey::MAIL_SELF_DELETE_ACCOUNT);
                $keyWord = new KeyWord($mail->getKey());
                $tabKeyWord = $keyWord->getTabMailSelfDelete($user2, $optionSystemService);
            }
            $params = $mailService->getDefaultParams($mail, $tabKeyWord);
            $params[MailService::TO] = $userService->getTabMailByListeUser(
                $userService->getByRole(Role::ROLE_SUPER_ADMIN)
            );
            $mailService->sendMail($params);
        }

        return $this->json([
            'status' => 'success',
            'msg' => $msg,
            'redirect' => $url
        ]);
    }
}
