<?php

declare(strict_types=1);

/**
 * User
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Controller\Admin\System;

use App\Controller\Admin\AppAdminController;
use App\Entity\Admin\System\User;
use App\Enum\Admin\Global\Breadcrumb;
use App\Enum\Admin\Global\Notification\Notification;
use App\Enum\Admin\System\Options\OptionSystem;
use App\Enum\Admin\System\Options\OptionUser;
use App\Form\Admin\User\MyAccountType;
use App\Form\Admin\User\UserAddType;
use App\Form\Admin\User\UserType;
use App\Service\Admin\NotificationService;
use App\Service\Admin\System\MailService;
use App\Service\Admin\System\OptionSystemService;
use App\Service\Admin\System\OptionUserService;
use App\Service\Admin\System\User\UserDataService;
use App\Service\Admin\System\User\UserService;
use App\Service\Admin\System\Mail\KeyWord;
use App\Enum\Admin\System\User\Role;
use App\Enum\Admin\System\User\UserCsrfToken;
use App\Enum\Admin\System\User\UserDataKey;
use App\Translate\Admin\System\UserTranslate;
use Exception;
use League\CommonMark\Exception\CommonMarkException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Enum\Admin\Global\FlashKey;
use App\Enum\Admin\System\Mail\MailKey;

#[Route('/admin/{_locale}/user', name: 'admin_user_', requirements: ['_locale' => '%app.supported_locales%'])]
class UserController extends AppAdminController
{
    /**
     * Clés de UserData modifiables par l'utilisateur lui-même
     */
    private const USER_DATA_KEYS_EDITABLE = [UserDataKey::HELP_FIRST_CONNEXION->value];

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
            Breadcrumb::DOMAIN->value => 'user',
            Breadcrumb::BREADCRUMB->value => [
                'user.index.title' => '#',
            ],
        ];

        return $this->render('admin/system/user/index.html.twig', [
            'breadcrumb' => $breadcrumb,
            'page' => 1,
            'limit' => $this->optionUserService->getValueByKey(OptionUser::OU_NB_ELEMENT->value),
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
            Breadcrumb::DOMAIN->value => 'user',
            Breadcrumb::BREADCRUMB->value => [
                'user.my_options.title' => '#',
            ],
        ];

        return $this->render('admin/system/user/my_options.html.twig', [
            'breadcrumb' => $breadcrumb,
            'csrfTokenId' => UserCsrfToken::UPDATE_MY_OPTION->value,
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
        $csrfError = $this->checkCsrf(UserCsrfToken::UPDATE_MY_OPTION->value, $request);
        if ($csrfError !== null) {
            return $csrfError;
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data) || !is_string($data['key'] ?? null) || !is_scalar($data['value'] ?? null)) {
            return $this->json(['success' => false], Response::HTTP_BAD_REQUEST);
        }

        $success = $this->optionUserService->updateValueFromAdmin($data['key'], strval($data['value']));
        return $this->json(['success' => $success]);
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
        Request $request,
        int $page = 1,
        int $limit = 20,
    ): JsonResponse {
        $queryParams = [
            'search' => $request->query->get('search'),
            'orderField' => $request->query->get('orderField'),
            'order' => $request->query->get('order'),
            'locale' => $request->getLocale(),
        ];

        $grid = $userService->getAllFormatToGrid($page, $limit, $queryParams);
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
        UserService $userService,
        TranslatorInterface $translator,
    ): JsonResponse {
        if (!$userService->canManage($user)) {
            return $this->json([
                'success' => 'false',
                'msg' => $translator->trans('user.error.not_disabled', domain: 'user'),
            ]);
        }

        $user->setDisabled(!$user->isDisabled());
        $userService->save($user);

        $msg = $translator->trans('user.success.enabled', ['login' => $user->getLogin()], 'user');
        if ($user->isDisabled()) {
            $msg = $translator->trans('user.success.disabled', ['login' => $user->getLogin()], 'user');
        }
        return $this->json($userService->getResponseAjax($msg));
    }

    #[Route('/ajax/delete/{id}', name: 'delete', methods: ['DELETE', 'PUT'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function delete(
        #[MapEntity(id: 'id')] User $user,
        UserService $userService,
        TranslatorInterface $translator,
        OptionSystemService $optionSystemService,
        #[Autowire('%app.folder.upload.avatar%')] string $avatarDirectory,
    ): JsonResponse {
        if (!$userService->canManage($user)) {
            return $this->json([
                'success' => 'false',
                'msg' => $translator->trans('user.error.not_allowed', domain: 'user'),
            ]);
        }

        $canDelete = $optionSystemService->getValueByKey(OptionSystem::OS_ALLOW_DELETE_DATA->value);
        $canReplace = $optionSystemService->getValueByKey(OptionSystem::OS_REPLACE_DELETE_USER->value);

        $msg = $msg_error = null;
        if ($canDelete === '1') {
            if ($canReplace === '1') {
                $userService->anonymizer($user);
                $msg = $translator->trans('user.success.anonymous', domain: 'user');
            } else {
                if ($user->getAvatar() !== null) {
                    $fileSystem = new Filesystem();
                    $fileSystem->remove($avatarDirectory . DIRECTORY_SEPARATOR . $user->getAvatar());
                }

                $userService->remove($user);
                $msg = $translator->trans('user.success.remove', domain: 'user');
            }
        } else {
            $msg = $translator->trans('user.error.not_allowed', domain: 'user');
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
        UserService $userService,
        Request $request,
        TranslatorInterface $translator,
        UserDataService $userDataService,
        #[MapEntity(id: 'id')] ?User $user = null,
    ): Response {
        if ($user === null) {
            return $this->redirectToRoute('admin_user_index');
        }

        $breadcrumb = [
            Breadcrumb::DOMAIN->value => 'user',
            Breadcrumb::BREADCRUMB->value => [
                'user.index.title' => 'admin_user_index',
                'user.update.title' => '#',
            ],
        ];

        $canManage = $userService->canManage($user);
        if (!$canManage && !$userService->isCurrentUser($user)) {
            return $this->redirectToRoute('admin_user_index');
        }

        // Rôle et désactivation non modifiables sur son propre compte
        $isSuperAdmin = !$canManage;

        $form = $this->createForm(UserType::class, $user, ['is_super_adm' => $isSuperAdmin]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $userService->save($user);
            $this->addFlash(
                FlashKey::SUCCESS->value,
                $translator->trans('user.update.success', ['email' => $user->getEmail()], domain: 'user'),
            );
        }

        $lastConnexion = $userDataService->getLastConnexion($user);

        return $this->render('admin/system/user/update.html.twig', [
            'breadcrumb' => $breadcrumb,
            'form' => $form,
            'user' => $user,
            'lastConnexion' => $lastConnexion,
            'isSuperAdmin' => $isSuperAdmin,
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
        UserService $userService,
        UserDataService $userDataService,
        UserTranslate $userTranslate,
        Request $request,
        OptionSystemService $optionSystemService,
        TranslatorInterface $translator,
        SluggerInterface $slugger,
        ParameterBagInterface $parameterBag,
        #[Autowire('%app.folder.upload.avatar%')] string $avatarDirectory,
    ): Response {
        $breadcrumb = [
            Breadcrumb::DOMAIN->value => 'user',
            Breadcrumb::BREADCRUMB->value => [
                'user.my_account.title' => '#',
            ],
        ];

        /** @var User $user */
        $user = $this->getUser();
        $form = $this->createForm(MyAccountType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            /** @var UploadedFile $avatarFile */
            $avatarFile = $form->get('avatar')->getData();

            if ($avatarFile) {
                if ($user->getAvatar() !== null) {
                    $fileSystem = new Filesystem();
                    $fileSystem->remove($avatarDirectory . DIRECTORY_SEPARATOR . $user->getAvatar());
                }

                $originalFilename = pathinfo($avatarFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $avatarFile->guessExtension();

                try {
                    $avatarFile->move($avatarDirectory, $newFilename);
                } catch (FileException $e) {
                    $this->addFlash(
                        FlashKey::DANGER->value,
                        $translator->trans('user.my_account.upload.avatar.error', domain: 'user'),
                    );
                }

                $user->setAvatar($newFilename);
            }

            $userService->save($user);
            $this->addFlash(
                FlashKey::SUCCESS->value,
                $translator->trans('user.my_account.success', domain: 'user'),
            );
        }

        $canDelete = $optionSystemService->getValueByKey(OptionSystem::OS_ALLOW_DELETE_DATA->value);
        $canReplace = $optionSystemService->getValueByKey(OptionSystem::OS_REPLACE_DELETE_USER->value);

        return $this->render('admin/system/user/my_account.html.twig', [
            'breadcrumb' => $breadcrumb,
            'form' => $form,
            'user' => $user,
            'avatarDirectory' => $parameterBag->get('app.path.avatar'),
            'changePasswordTranslate' => $userTranslate->getTranslateChangePassword(),
            'dangerZoneTranslate' => $userTranslate->getTranslateDangerZone(),
            'moreOptionsTranslate' => $userTranslate->getTranslateMoreOptions(),
            'moreOptionsDatas' => [
                'help_first_connexion' => $userDataService->getHelpFirstConnexion($this->getUser()),
                'user_data_key_first_connexion' => UserDataKey::HELP_FIRST_CONNEXION->value,
            ],
            'canDelete' => $canDelete,
            'canReplace' => $canReplace,
            'csrfTokenSelfDisabled' => UserCsrfToken::SELF_DISABLED->value,
            'csrfTokenSelfDelete' => UserCsrfToken::SELF_DELETE->value,
            'csrfTokenUpdateUserData' => UserCsrfToken::UPDATE_USER_DATA->value,
        ]);
    }

    /**
     * Supprime l'avatar de l'utilisateur courant
     * @param Request $request
     * @param UserService $userService
     * @param TranslatorInterface $translator
     * @param string $avatarDirectory
     * @return RedirectResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/delete-avatar', name: 'delete_avatar', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function deleteAvatar(
        Request $request,
        UserService $userService,
        TranslatorInterface $translator,
        #[Autowire('%app.folder.upload.avatar%')] string $avatarDirectory,
    ): RedirectResponse {
        if (!$this->isCsrfTokenValid('delete_avatar', $request->request->getString('_token'))) {
            $this->addFlash(FlashKey::DANGER->value, $translator->trans('user.error.not_allowed', domain: 'user'));
            return $this->redirectToRoute('admin_user_my_account');
        }

        /** @var User $user */
        $user = $this->getUser();

        if ($user->getAvatar() !== null) {
            $fileSystem = new Filesystem();
            $fileSystem->remove($avatarDirectory . DIRECTORY_SEPARATOR . $user->getAvatar());

            $user->setAvatar(null);
            $userService->save($user);
        }

        $this->addFlash(FlashKey::SUCCESS->value, $translator->trans('user.delete_avatar.success', domain: 'user'));

        return $this->redirectToRoute('admin_user_my_account');
    }

    /**
     * Changement de mot passe, le mot de passe actuel est demandé
     * @param UserService $userService
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param UserPasswordHasherInterface $passwordHasher
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/change-my-password', name: 'change_my_password', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function updatePassword(
        UserService $userService,
        Request $request,
        TranslatorInterface $translator,
        UserPasswordHasherInterface $passwordHasher,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        /** @var User $user */
        $user = $this->getUser();

        if (!is_array($data) || !$passwordHasher->isPasswordValid($user, strval($data['current'] ?? ''))) {
            return $this->json(
                [
                    'status' => 'error',
                    'msg' => $translator->trans('user.change_password.error.current_password', domain: 'user'),
                    'redirect' => false,
                ],
                Response::HTTP_BAD_REQUEST,
            );
        }

        $password = strval($data['data'] ?? '');
        if (!$userService->isValidPassword($password)) {
            return $this->json(
                [
                    'status' => 'error',
                    'msg' => $translator->trans('user.change_password.error.rule', domain: 'user'),
                    'redirect' => false,
                ],
                Response::HTTP_BAD_REQUEST,
            );
        }

        $userService->updatePassword($user, $password);

        return $this->json([
            'status' => 'success',
            'msg' => $translator->trans('user.update_password.success', domain: 'user'),
            'redirect' => false,
        ]);
    }

    /**
     * Permet à l'utilisateur de s'auto désactivé
     * @param Request $request
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
        Request $request,
        TranslatorInterface $translator,
        UserService $userService,
        MailService $mailService,
        OptionSystemService $optionSystemService,
        NotificationService $notificationService,
    ): JsonResponse {
        $csrfError = $this->checkCsrf(UserCsrfToken::SELF_DISABLED->value, $request);
        if ($csrfError !== null) {
            return $csrfError;
        }

        /** @var User $user */
        $user = $this->getUser();
        $isSuperAdmin = Role::SUPER_ADMIN->isGrantedTo($user);
        if ($isSuperAdmin) {
            $msg = $translator->trans('user.error.not_disabled', domain: 'user');
            $url = '';
        } else {
            $user->setDisabled(true);
            $userService->save($user);
            $msg = $translator->trans('user.success.self_disabled', domain: 'user');
            $url = $this->generateUrl('auth_logout');
        }

        if ($optionSystemService->canSendMailNotification() && !$isSuperAdmin) {
            $mail = $mailService->getByKey(MailKey::SELF_DISABLED_ACCOUNT->value);
            $keyWord = new KeyWord($mail->getKey());
            $tabKeyWord = $keyWord->getTabMailSelfDisabled($user, $optionSystemService);
            $params = $mailService->getDefaultParams($mail, $tabKeyWord);
            $tabTo = $userService->getTabMailByListeUser($userService->getByRole(Role::SUPER_ADMIN->value));

            try {
                foreach ($tabTo as $to) {
                    $params[MailService::TO] = $to;
                    $mailService->sendMail($params);
                }
            } catch (CommonMarkException | TransportExceptionInterface $e) {
                $this->logger->error($e->getMessage());
            }
        }

        // Notifications
        if ($optionSystemService->canNotification() && !$isSuperAdmin) {
            $users = $userService->getByRole(Role::SUPER_ADMIN->value);

            foreach ($users as $user_notification) {
                $notificationService->add($user_notification, Notification::SELF_DISABLED->value, [
                    'login' => $user->getLogin(),
                ]);
            }
        }

        return $this->json([
            'status' => 'success',
            'msg' => $msg,
            'redirect' => $url,
        ]);
    }

    /**
     * Permet à l'utilisateur de s'auto supprimer
     * @param Request $request
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
        Request $request,
        TranslatorInterface $translator,
        UserService $userService,
        MailService $mailService,
        OptionSystemService $optionSystemService,
        NotificationService $notificationService,
        #[Autowire('%app.folder.upload.avatar%')] string $avatarDirectory,
    ): JsonResponse {
        $csrfError = $this->checkCsrf(UserCsrfToken::SELF_DELETE->value, $request);
        if ($csrfError !== null) {
            return $csrfError;
        }

        $status = 0;

        /** @var User $user */
        $user = $this->getUser();
        $user2 = clone $user;
        $isSuperAdmin = Role::SUPER_ADMIN->isGrantedTo($user);
        $url = '';
        if ($isSuperAdmin) {
            $msg = $translator->trans('user.error.not_disabled', domain: 'user');
        } else {
            if ($optionSystemService->canDelete()) {
                if ($optionSystemService->canReplace()) {
                    $status = 1;
                    $userService->anonymizer($user);
                    $msg = $translator->trans('user.danger_zone.success_anonymous', domain: 'user');
                    $url = $this->generateUrl('auth_logout');
                } else {
                    $status = 2;

                    if ($user->getAvatar() !== null) {
                        $fileSystem = new Filesystem();
                        $fileSystem->remove($avatarDirectory . DIRECTORY_SEPARATOR . $user->getAvatar());
                    }

                    $userService->remove($user);
                    $msg = $translator->trans('user.danger_zone.success_remove', domain: 'user');
                    $url = $this->generateUrl('front_no_local');
                }
            } else {
                $msg = $translator->trans('user.error.not_allowed', domain: 'user');
            }
        }

        // Envoi emails
        if (
            $optionSystemService->canSendMailNotification() &&
            !$isSuperAdmin &&
            $optionSystemService->canDelete()
        ) {
            if ($status === 1) {
                // anonymisation
                $mail = $mailService->getByKey(MailKey::SELF_ANONYMOUS_ACCOUNT->value);
                $keyWord = new KeyWord($mail->getKey());
                $tabKeyWord = $keyWord->getTabMailSelfAnonymous($user2, $optionSystemService);
            } else {
                // delete
                $mail = $mailService->getByKey(MailKey::SELF_DELETE_ACCOUNT->value);
                $keyWord = new KeyWord($mail->getKey());
                $tabKeyWord = $keyWord->getTabMailSelfDelete($user2, $optionSystemService);
            }
            $params = $mailService->getDefaultParams($mail, $tabKeyWord);
            $tabTo = $userService->getTabMailByListeUser($userService->getByRole(Role::SUPER_ADMIN->value));

            foreach ($tabTo as $to) {
                $params[MailService::TO] = $to;
                $mailService->sendMail($params);
            }
        }

        // Notifications
        if ($optionSystemService->canNotification() && !$isSuperAdmin && $optionSystemService->canDelete()) {
            $users = $userService->getByRole(Role::SUPER_ADMIN->value);
            if ($status === 1) {
                $key = Notification::SELF_ANONYMOUS->value;
            } else {
                $key = Notification::SELF_DELETE->value;
            }

            foreach ($users as $user_notification) {
                $notificationService->add($user_notification, $key, ['login' => $user2->getLogin()]);
            }
        }

        return $this->json([
            'status' => 'success',
            'msg' => $msg,
            'redirect' => $url,
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
        Request $request,
        UserService $userService,
        OptionUserService $optionUserService,
        OptionSystemService $optionSystemService,
        TranslatorInterface $translator,
        UserDataService $userDataService,
        NotificationService $notificationService,
        MailService $mailService,
    ): Response {
        $breadcrumb = [
            Breadcrumb::DOMAIN->value => 'user',
            Breadcrumb::BREADCRUMB->value => [
                'user.index.title' => 'admin_user_index',
                'user.add.title' => '#',
            ],
        ];

        $user = new User();
        $form = $this->createForm(UserAddType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $form->getData();
            $user = $userService->addUser($user);

            $optionUserService->createOptionsUser($user);

            $key = $userDataService->generateResetPasswordKey($user);

            $mail = $mailService->getByKey(MailKey::CREATE_ACCOUNT_ADM->value);
            $keyWord = new KeyWord($mail->getKey());
            $tabKeyWord = $keyWord->getTabMailCreateAccountAdm(
                $user,
                $this->getUser(),
                $this->generateUrl('auth_change_new_password_user', ['key' => $key]),
                $optionSystemService,
            );
            $params = $mailService->getDefaultParams($mail, $tabKeyWord);
            $params[MailService::TO] = $user->getEmail();

            $mailService->sendMail($params);

            $this->addFlash(
                FlashKey::SUCCESS->value,
                $translator->trans('user.add.success', ['email' => $user->getEmail()], domain: 'user'),
            );

            if ($optionSystemService->canNotification()) {
                $notificationService->add($user, Notification::WELCOME->value, [
                    'login' => $user->getLogin(),
                    'url_aide' => 'url_aide',
                    'role' => $user->getRoles()[0],
                ]);
            }

            return $this->redirectToRoute('admin_user_index');
        }

        return $this->render('admin/system/user/add.html.twig', [
            'breadcrumb' => $breadcrumb,
            'form' => $form,
        ]);
    }

    /**
     * Permet de prendre le contrôle du compte d'un utilisateur
     * Le contrôle des droits et le log sont faits dans SwitchUserSubscriber
     * @param Request $request
     * @return RedirectResponse
     */
    #[Route('/switch', name: 'switch', methods: ['GET'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function switch(Request $request): RedirectResponse
    {
        $email = $request->query->get('user');
        return $this->redirectToRoute('admin_dashboard_index', ['_switch_user' => $email]);
    }

    /**
     * Envoi un email pour reset le password de l'utilisateur
     * @param User $user
     * @param MailService $mailService
     * @param OptionSystemService $optionSystemService
     * @param UserDataService $userDataService
     * @param TranslatorInterface $translator
     * @param UserService $userService
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
        MailService $mailService,
        OptionSystemService $optionSystemService,
        UserDataService $userDataService,
        TranslatorInterface $translator,
        UserService $userService,
    ): RedirectResponse {
        if (!$userService->canManage($user) && !$userService->isCurrentUser($user)) {
            return $this->redirectToRoute('admin_user_index');
        }

        $key = $userDataService->generateResetPasswordKey($user);

        $mail = $mailService->getByKey(MailKey::RESET_PASSWORD->value);
        $keyWord = new KeyWord($mail->getKey());
        $tabKeyWord = $keyWord->getTabMailResetPassword(
            $user,
            $this->getUser(),
            $this->generateUrl('auth_change_password_user', ['key' => $key]),
            $optionSystemService,
        );
        $params = $mailService->getDefaultParams($mail, $tabKeyWord);
        $params[MailService::TO] = $user->getEmail();

        $mailService->sendMail($params);

        $this->addFlash(
            FlashKey::SUCCESS->value,
            $translator->trans('user.my_account.reset_password_success', domain: 'user'),
        );

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
    public function updateUserdata(Request $request, UserDataService $userDataService): Response
    {
        $csrfError = $this->checkCsrf(UserCsrfToken::UPDATE_USER_DATA->value, $request);
        if ($csrfError !== null) {
            return $csrfError;
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data) || !in_array($data['key'] ?? null, self::USER_DATA_KEYS_EDITABLE, true)) {
            return $this->json(['success' => false, 'msg' => ''], Response::HTTP_BAD_REQUEST);
        }

        $userDataService->update($data['key'], strval($data['value'] ?? ''), $this->getUser());
        return $this->json($userDataService->getResponseAjax());
    }
}
