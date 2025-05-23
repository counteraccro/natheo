<?php

/**
 * User
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Controller\Admin\System;

use App\Controller\Admin\AppAdminController;
use App\Entity\Admin\System\User;
use App\Form\Admin\User\MyAccountType;
use App\Form\Admin\User\UserAddType;
use App\Form\Admin\User\UserType;
use App\Service\Admin\NotificationService;
use App\Service\Admin\System\MailService;
use App\Service\Admin\System\OptionSystemService;
use App\Service\Admin\System\OptionUserService;
use App\Service\Admin\System\User\UserDataService;
use App\Service\Admin\System\User\UserService;
use App\Service\LoggerService;
use App\Utils\Breadcrumb;
use App\Utils\Flash\FlashKey;
use App\Utils\Notification\NotificationKey;
use App\Utils\System\Mail\KeyWord;
use App\Utils\System\Mail\MailKey;
use App\Utils\System\Options\OptionSystemKey;
use App\Utils\System\Options\OptionUserKey;
use App\Utils\System\User\Role;
use App\Utils\System\User\UserDataKey;
use App\Utils\Translate\System\UserTranslate;
use Exception;
use League\CommonMark\Exception\CommonMarkException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\ByteString;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/{_locale}/user', name: 'admin_user_', requirements: ['_locale' => '%app.supported_locales%'])]
class UserController extends AppAdminController
{
    /**
     * point d'entrée
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
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

        return $this->render('admin/system/user/index.html.twig', [
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

        return $this->render('admin/system/user/my_options.html.twig', [
            'breadcrumb' => $breadcrumb,
        ]);
    }

    /**
     * Met à jour une option
     * @param Request $request
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
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
     * @param UserService $userService
     * @param Request $request
     * @param int $page
     * @param int $limit
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/load-grid-data/{page}/{limit}', name: 'load_grid_data', methods: ['GET'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function loadGridData(
        UserService $userService,
        Request     $request,
        int         $page = 1,
        int         $limit = 20
    ): JsonResponse
    {
        $search = $request->query->get('search');
        $grid = $userService->getAllFormatToGrid($page, $limit, $search);
        return $this->json($grid);
    }

    /**
     * Disabled ou non un user
     * @param User $user
     * @param UserService $userService
     * @param TranslatorInterface $translator
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/update-disabled/{id}', name: 'update_disabled', methods: ['PUT'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function updateDisabled(
        #[MapEntity(id: 'id')] User $user,
        UserService                 $userService,
        TranslatorInterface         $translator): JsonResponse
    {
        $role = new Role($user);
        if ($role->isSuperAdmin() && $user->isFounder()) {
            return $this->json([
                'success' => 'false',
                'msg' => $translator->trans('user.error_not_disabled', domain: 'user')
            ]);
        }

        $user->setDisabled(!$user->isDisabled());
        $userService->save($user);

        $msg = $translator->trans('user.success.no.disabled', ['login' => $user->getLogin()], 'user');
        if ($user->isDisabled()) {
            $msg = $translator->trans('user.success.disabled', ['login' => $user->getLogin()], 'user');
        }
        return $this->json($userService->getResponseAjax($msg));
    }

    #[Route('/ajax/delete/{id}', name: 'delete', methods: ['DELETE', 'PUT'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function delete(
        #[MapEntity(id: 'id')] User $user,
        UserService                 $userService,
        TranslatorInterface         $translator,
        OptionSystemService         $optionSystemService
    ): JsonResponse
    {
        $role = new Role($user);
        if ($role->isSuperAdmin() && $user->isFounder()) {
            return $this->json([
                'success' => 'false',
                'msg' => $translator->trans('user.error_not_disabled', domain: 'user')
            ]);

        }

        $canDelete = $optionSystemService->getValueByKey(OptionSystemKey::OS_ALLOW_DELETE_DATA);
        $canReplace = $optionSystemService->getValueByKey(OptionSystemKey::OS_REPLACE_DELETE_USER);

        $msg = $msg_error = null;
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
        return $this->json($userService->getResponseAjax($msg, $msg_error));
    }

    /**
     * Permet de modifier un user
     * @param User $user
     * @param UserService $userService
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param UserDataService $userDataService
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/update/{id}', name: 'update', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function update(
        #[MapEntity(id: 'id')] User $user,
        UserService                 $userService,
        Request                     $request,
        TranslatorInterface         $translator,
        UserDataService             $userDataService
    ): Response
    {
        $breadcrumb = [
            Breadcrumb::DOMAIN => 'user',
            Breadcrumb::BREADCRUMB => [
                'user.page_title_h1' => 'admin_user_index',
                'user.page_update_title_h1_2' => '#'
            ]
        ];

        if ($user->isFounder() && $this->getUser()->getId() !== $user->getId()) {
            return $this->redirectToRoute('admin_user_index');
        }

        $isSuperAdmin = false;
        $role = new Role($user);
        if ($role->isSuperAdmin() && $user->isFounder()) {
            $isSuperAdmin = true;
        }

        $form = $this->createForm(UserType::class, $user, ['is_super_adm' => $isSuperAdmin]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $userService->save($user);
            $this->addFlash(
                FlashKey::FLASH_SUCCESS,
                $translator->trans('user.page_update.success', ['email' => $user->getEmail()], domain: 'user'));
        }

        $lastConnexion = $userDataService->getLastConnexion($user);

        return $this->render('admin/system/user/update.html.twig', [
            'breadcrumb' => $breadcrumb,
            'form' => $form,
            'user' => $user,
            'lastConnexion' => $lastConnexion,
            'isSuperAdmin' => $isSuperAdmin
        ]);
    }

    /**
     * Mise à jour des données de l'utilisateur par lui-même
     * @param UserService $userService
     * @param UserDataService $userDataService
     * @param UserTranslate $userTranslate
     * @param Request $request
     * @param OptionSystemService $optionSystemService
     * @param TranslatorInterface $translator
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/my-account', name: 'my_account', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function updateMyAccount(
        UserService         $userService,
        UserDataService     $userDataService,
        UserTranslate       $userTranslate,
        Request             $request,
        OptionSystemService $optionSystemService,
        TranslatorInterface $translator
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
            $this->addFlash(
                FlashKey::FLASH_SUCCESS,
                $translator->trans('user.page_my_account.success', domain: 'user'));
        }

        $canDelete = $optionSystemService->getValueByKey(OptionSystemKey::OS_ALLOW_DELETE_DATA);
        $canReplace = $optionSystemService->getValueByKey(OptionSystemKey::OS_REPLACE_DELETE_USER);

        return $this->render('admin/system/user/my_account.html.twig', [
            'breadcrumb' => $breadcrumb,
            'form' => $form,
            'user' => $user,
            'changePasswordTranslate' => $userTranslate->getTranslateChangePassword(),
            'dangerZoneTranslate' => $userTranslate->getTranslateDangerZone(),
            'moreOptionsTranslate' => $userTranslate->getTranslateMoreOptions(),
            'moreOptionsDatas' => [
                'help_first_connexion' => $userDataService->getHelpFirstConnexion($this->getUser()),
                'user_data_key_first_connexion' => UserDataKey::KEY_HELP_FIRST_CONNEXION
            ],
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
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
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
            'msg' => $translator->trans('user.update_password.success', domain: 'user'),
            'redirect' => false,
        ]);
    }

    /**
     * Permet à l'utilisateur de s'auto désactivé
     * @param TranslatorInterface $translator
     * @param UserService $userService
     * @param MailService $mailService
     * @param OptionSystemService $optionSystemService
     * @param NotificationService $notificationService
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/self-disabled', name: 'self_disabled', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function selfDisabled(
        TranslatorInterface $translator,
        UserService         $userService,
        MailService         $mailService,
        OptionSystemService $optionSystemService,
        NotificationService $notificationService
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


        if ($optionSystemService->canSendMailNotification() && !$role->isSuperAdmin()) {
            $mail = $mailService->getByKey(MailKey::MAIL_SELF_DISABLED_ACCOUNT);
            $keyWord = new KeyWord($mail->getKey());
            $tabKeyWord = $keyWord->getTabMailSelfDisabled($user, $optionSystemService);
            $params = $mailService->getDefaultParams($mail, $tabKeyWord);
            $tabTo = $userService->getTabMailByListeUser(
                $userService->getByRole(Role::ROLE_SUPER_ADMIN)
            );

            try {
                foreach ($tabTo as $to) {
                    $params[MailService::TO] = $to;
                    $mailService->sendMail($params);
                }

            } catch (CommonMarkException|TransportExceptionInterface $e) {
                die($e->getMessage());
            }
        }

        // Notifications
        if ($optionSystemService->canNotification() && !$role->isSuperAdmin()) {

            $users = $userService->getByRole(Role::ROLE_SUPER_ADMIN);

            foreach ($users as $user_notification) {
                $notificationService->add(
                    $user_notification,
                    NotificationKey::NOTIFICATION_SELF_DISABLED,
                    ['login' => $user->getLogin()]
                );
            }
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
     * @param MailService $mailService
     * @param OptionSystemService $optionSystemService
     * @param NotificationService $notificationService
     * @return JsonResponse
     * @throws CommonMarkException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws TransportExceptionInterface
     * @throws Exception
     */
    #[Route('/ajax/self-delete', name: 'self_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function selfDelete(
        TranslatorInterface $translator,
        UserService         $userService,
        MailService         $mailService,
        OptionSystemService $optionSystemService,
        NotificationService $notificationService,
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

        // Envoi emails
        if ($optionSystemService->canSendMailNotification() && !$role->isSuperAdmin() && $optionSystemService->canDelete()) {
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
            $tabTo = $userService->getTabMailByListeUser(
                $userService->getByRole(Role::ROLE_SUPER_ADMIN)
            );

            foreach ($tabTo as $to) {
                $params[MailService::TO] = $to;
                $mailService->sendMail($params);
            }
        }

        // Notifications
        if ($optionSystemService->canNotification() && !$role->isSuperAdmin() && $optionSystemService->canDelete()) {
            $users = $userService->getByRole(Role::ROLE_SUPER_ADMIN);
            if ($status === 1) {
                $key = NotificationKey::NOTIFICATION_SELF_ANONYMOUS;
            } else {
                $key = NotificationKey::NOTIFICATION_SELF_DELETE;
            }

            foreach ($users as $user_notification) {
                $notificationService->add($user_notification, $key, ['login' => $user2->getLogin()]);
            }
        }

        return $this->json([
            'status' => 'success',
            'msg' => $msg,
            'redirect' => $url
        ]);
    }

    /**
     * Permet de créer un nouvel user
     * @param Request $request
     * @param UserService $userService
     * @param OptionUserService $optionUserService
     * @param OptionSystemService $optionSystemService
     * @param TranslatorInterface $translator
     * @param UserDataService $userDataService
     * @param NotificationService $notificationService
     * @param MailService $mailService
     * @return Response
     * @throws CommonMarkException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws TransportExceptionInterface
     */
    #[Route('/add', name: 'add', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function add(
        Request             $request,
        UserService         $userService,
        OptionUserService   $optionUserService,
        OptionSystemService $optionSystemService,
        TranslatorInterface $translator,
        UserDataService     $userDataService,
        NotificationService $notificationService,
        MailService         $mailService
    ): Response
    {
        $breadcrumb = [
            Breadcrumb::DOMAIN => 'user',
            Breadcrumb::BREADCRUMB => [
                'user.page_title_h1' => 'admin_user_index',
                'user.page_add_title_h1' => '#'
            ]
        ];

        $user = new User();
        $form = $this->createForm(UserAddType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $form->getData();
            $user = $userService->addUser($user);

            $optionUserService->createOptionsUser($user);

            $key = ByteString::fromRandom(48)->toString();
            $userDataService->update(UserDataKey::KEY_RESET_PASSWORD, $key, $user);


            $mail = $mailService->getByKey(MailKey::MAIL_CREATE_ACCOUNT_ADM);
            $keyWord = new KeyWord($mail->getKey());
            $tabKeyWord = $keyWord->getTabMailCreateAccountAdm(
                $user,
                $this->getUser(),
                $this->generateUrl('auth_change_new_password_user', ['key' => $key]),
                $optionSystemService
            );
            $params = $mailService->getDefaultParams($mail, $tabKeyWord);
            $params[MailService::TO] = $user->getEmail();

            $mailService->sendMail($params);

            $this->addFlash(
                FlashKey::FLASH_SUCCESS,
                $translator->trans('user.page_add.success', ['email' => $user->getEmail()], domain: 'user'));

            if ($optionSystemService->canNotification()) {
                $notificationService->add(
                    $user,
                    NotificationKey::NOTIFICATION_WELCOME,
                    [
                        'login' => $user->getLogin(),
                        'url_aide' => 'url_aide',
                        'role' => $user->getRoles()[0]
                    ]
                );
            }

            return $this->redirectToRoute('admin_user_index');
        }

        return $this->render('admin/system/user/add.html.twig', [
            'breadcrumb' => $breadcrumb,
            'form' => $form
        ]);
    }

    /**
     * Permet de prendre le contrôle du compte d'un utilisateur
     * @param Request $request
     * @param LoggerService $loggerService
     * @return RedirectResponse
     */
    #[Route('/switch', name: 'switch', methods: ['GET'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function switch(Request $request, LoggerService $loggerService): RedirectResponse
    {
        $email = $request->query->get('user');
        $loggerService->logSwitchUser($this->getUser()->getEmail(), $email);
        return $this->redirectToRoute('admin_dashboard_index', ['_switch_user' => $email]);
    }

    /**
     * Envoi un email pour reset le password de l'utilisateur
     * @param User $user
     * @param MailService $mailService
     * @param OptionSystemService $optionSystemService
     * @param UserDataService $userDataService
     * @param TranslatorInterface $translator
     * @return RedirectResponse
     * @throws CommonMarkException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws TransportExceptionInterface
     */
    #[Route('/reset-password/{id}', name: 'reset_password', methods: ['GET'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function sendResetPassword(
        #[MapEntity(id: 'id')] User $user,
        MailService                 $mailService,
        OptionSystemService         $optionSystemService,
        UserDataService             $userDataService,
        TranslatorInterface         $translator
    ): RedirectResponse
    {
        $key = ByteString::fromRandom(48)->toString();
        $userDataService->update(UserDataKey::KEY_RESET_PASSWORD, $key, $user);


        $mail = $mailService->getByKey(MailKey::MAIL_RESET_PASSWORD);
        $keyWord = new KeyWord($mail->getKey());
        $tabKeyWord = $keyWord->getTabMailResetPassword(
            $user,
            $this->getUser(),
            $this->generateUrl('auth_change_password_user', ['key' => $key]),
            $optionSystemService
        );
        $params = $mailService->getDefaultParams($mail, $tabKeyWord);
        $params[MailService::TO] = $user->getEmail();

        $mailService->sendMail($params);

        $this->addFlash(
            FlashKey::FLASH_SUCCESS,
            $translator->trans('user.page_my_account.reset_password_success', domain: 'user'));

        return $this->redirectToRoute('admin_user_update', ['id' => $user->getId()]);
    }

    /**
     * Met à jour une userData en fonction de sa clé et sa valeur
     * @param Request $request
     * @param UserDataService $userDataService
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/update-user-data', name: 'update_user_data', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function updateUserdata(
        Request         $request,
        UserDataService $userDataService
    ): Response
    {
        $data = json_decode($request->getContent(), true);
        $userDataService->update($data['key'], $data['value'], $this->getUser());
        return $this->json($userDataService->getResponseAjax());
    }
}
