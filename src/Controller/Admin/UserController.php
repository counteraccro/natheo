<?php
/**
 * Controller qui va gérer les users
 * @author Gourdon Aymeric
 * @version 1.0
 * @package App\Controller\Admin;
 */

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\Admin\UserType;
use App\Repository\UserRepository;
use App\Service\Admin\System\AccessService;
use App\Service\Admin\System\FileService;
use App\Service\Admin\System\FileUploaderService;
use App\Service\Admin\System\OptionService;
use App\Service\Admin\UserService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/user', name: 'admin_user_')]
class UserController extends AppAdminController
{

    const SESSION_KEY_FILTER = 'session_user_filter';
    const SESSION_KEY_PAGE = 'session_user_page';

    /**
     * Point d'entrée de la gestion des users
     * @param int $page
     * @return Response
     */
    #[Route('/index/{page}', name: 'index')]
    public function index(int $page = 1): Response
    {
        $breadcrumb = [
            $this->translator->trans('admin_dashboard#Dashboard') => 'admin_dashboard_index',
            $this->translator->trans('admin_user#Gestion des utilisateurs') => '',
        ];

        $fieldSearch = [
            'email' => $this->translator->trans("admin_user#Email"),
        ];

        return $this->render('admin/user/index.html.twig', [
            'breadcrumb' => $breadcrumb,
            'fieldSearch' => $fieldSearch,
            'page' => $page
        ]);
    }

    /**
     * Permet de lister les users
     * @param int $page
     */
    #[Route('/ajax/listing/{page}', name: 'ajax_listing')]
    public function listing(int $page = 1): Response
    {

        $this->setPageInSession(self::SESSION_KEY_PAGE, $page);
        $limit = $this->optionService->getOptionElementParPage();
        $dateFormat = $this->optionService->getOptionShortFormatDate();
        $timeFormat = $this->optionService->getOptionTimeFormat();

        $filter = $this->getCriteriaGeneriqueSearch(self::SESSION_KEY_FILTER);

        /** @var UserRepository $routeRepo */
        $userRepo = $this->doctrine->getRepository(User::class);
        $listeUsers = $userRepo->listeUserPaginate($page, $limit, $filter);

        return $this->render('admin/user/ajax/ajax-listing.html.twig', [
            'listeUsers' => $listeUsers,
            'page' => $page,
            'limit' => $limit,
            'dateFormat' => $dateFormat,
            'timeFormat' => $timeFormat,
            'route' => 'admin_user_ajax_listing',
        ]);
    }

    /**
     * Permet de créer / éditer un user
     * @param User|null $user
     */
    #[Route('/add/', name: 'add')]
    #[Route('/edit/{id}', name: 'edit')]
    #[Route('/me/{id}', name: 'me')]
    public function createUpdate(FileUploaderService $fileUploader, UserPasswordHasherInterface $passwordHarsher, User $user = null): RedirectResponse|Response
    {
        // Vérification si l'action est bonne et conforme aux données
        $action_me = explode('_', $this->request->getCurrentRequest()->attributes->get('_route'))[2];
        if ($user != null && $this->getUser()->getId() != $user->getId() && $action_me == "me") {
            return $this->redirectToRoute('admin_user_edit', ['id' => $user->getId()]);
        } else if ($user == null && ($action_me == "edit" || $action_me == "me")) {
            return $this->redirectToRoute('admin_user_index', ['page' => $this->getPageInSession(self::SESSION_KEY_PAGE)]);
        }

        $breadcrumb = [
            $this->translator->trans('admin_dashboard#Dashboard') => 'admin_dashboard_index',
            $this->translator->trans('admin_user#Gestion des utilisateurs') => ['admin_user_index', ['page' => $this->getPageInSession(self::SESSION_KEY_PAGE)]]
        ];

        $dateFormat = $this->optionService->getOptionFormatDate();
        $timeFormat = $this->optionService->getOptionTimeFormat();

        if ($user == null) {
            $action = 'add';
            $user = new User();
            $title = $this->translator->trans('admin_user#Créer un utilisateur');
            $breadcrumb[$title] = '';
            $flashMsg = $this->translator->trans('admin_user#Utilisateur créé avec succès');
        } else {

            // Si on tente d'éditer un user spécificque, on rejette la demande
            if ($user->getName() == UserService::ROOT_NAME || $user->getName() == UserService::GHOST_NAME) {
                return $this->redirectToRoute('admin_user_index');
            }
            $action = 'edit';
            $title = $this->translator->trans('admin_user#Edition de l\'utilisateur ') . '#' . $user->getId();
            $breadcrumb[$title] = '';
            $flashMsg = $this->translator->trans('admin_user#Utilisateur édité avec succès');

            // Cas edition de son compte
            if ($action_me == 'me') {
                unset($breadcrumb[$this->translator->trans('admin_user#Gestion des utilisateurs')]);
                unset($breadcrumb[$title]);
                $title = $this->translator->trans('admin_user#Mon compte');
                $breadcrumb[$title] = '';
                $flashMsg = $this->translator->trans('admin_user#Votre compte à été modifié avec succès');
            }
        }

        $form = $this->createForm(UserType::class, $user, ['custom_action' => $action, 'self_action' => $action_me]);

        $form->handleRequest($this->request->getCurrentRequest());
        if ($form->isSubmitted() && $form->isValid()) {

            $avatar = $form->get('avatar')->getData();
            if ($avatar) {
                $avatarName = $fileUploader->upload($avatar, $fileUploader->getAvatarDirectory());
                if ($user->getId() != null) {
                    $this->fileService->delete($user->getAvatar(), $fileUploader->getAvatarDirectory());
                }
                $user->setAvatar($avatarName);
            } else if ($action == 'add') {
                $user->setAvatar(UserService::DEFAULT_AVATAR);
            }

            $password = $form->get('password')->getData();
            if ($password != "") {
                $user->setPassword($passwordHarsher->hashPassword(
                    $user,
                    $password
                ));

                $user->setLastPasswordUpdae(new \DateTime());
            }

            $this->doctrine->getManager()->persist($user);
            $this->doctrine->getManager()->flush();

            $param = [];
            $this->addFlash('success', $flashMsg);
            if ($action_me == "me") {

                return $this->redirectToRoute('admin_dashboard_index');
            }

            if ($action == 'edit') {
                $param = ['page' => $this->getPageInSession(self::SESSION_KEY_PAGE)];
            }

            if($user->getId() === $this->security->getUser()->getId())
            {
                // Avant la redirection raffraichissement des roles pour le user
                $tabRouteAccess = [];
                foreach ($user->getRolesCms() as $rolesCm) {
                    foreach ($rolesCm->getRouteRights() as $routeRight) {
                        $tabRouteAccess[] = $routeRight->getRoute()->getRoute();
                    }
                }
                $this->session->set(AccessService::KEY_SESSION_LISTE_ROUTE_ACCESS, $tabRouteAccess);
            }

            return $this->redirectToRoute('admin_user_index', $param);
        }

        return $this->render('admin/user/create-update.html.twig', [
            'breadcrumb' => $breadcrumb,
            'form' => $form->createView(),
            'title' => $title,
            'user' => $user,
            'dateFormat' => $dateFormat,
            'timeFormat' => $timeFormat,
            'action' => $action,
            'self_action' => $action_me
        ]);
    }

    /**
     * Permet de supprimer un utilisateur
     * @param User $user
     * @param FileUploaderService $fileUploaderService
     * @return RedirectResponse
     */
    #[Route('/delete/{id}', name: 'delete')]
    public function delete(User $user, FileUploaderService $fileUploaderService): RedirectResponse
    {
        $this->fileService->delete($user->getAvatar(), $fileUploaderService->getAvatarDirectory());
        $flashMsg = $this->translator->trans('admin_user#Utilisateur supprimé avec succès');

        if ($this->optionService->getOptionReplaceUser() == OptionService::GO_ADM_REPLACE_USER_YES_VALUE) {
            $id_user = $user->getId();
            // TODO Code pour remplacer les données du user par john doe
        }

        $this->doctrine->getManager()->remove($user);
        $this->doctrine->getManager()->flush();
        $this->addFlash('success', $flashMsg);
        return $this->redirectToRoute('admin_user_index');
    }


    /**
     * Met à jour le champ isDisabled
     * @param User $user
     * @return RedirectResponse
     */
    #[Route('/update/disabled/{id}', name: 'disabled')]
    public function updateIsDisabled(User $user): RedirectResponse
    {
        if ($user->getIsDisabled() === false) {
            $flashMsg = $this->translator->trans('admin_user#Utilisateur désactivé avec succès');
            $user->setIsDisabled(true);
        } else {
            $flashMsg = $this->translator->trans('admin_user#Utilisateur activé avec succès');
            $user->setIsDisabled(false);
        }

        $this->doctrine->getManager()->persist($user);
        $this->doctrine->getManager()->flush();
        $this->addFlash('success', $flashMsg);
        return $this->redirectToRoute('admin_user_index', ['page' => $this->getPageInSession(self::SESSION_KEY_PAGE)]);
    }
}
