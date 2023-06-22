<?php

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Service lier à l'objet user
 */

namespace App\Service\Admin\User;

use App\Entity\Admin\User;
use App\Service\Admin\AppAdminService;
use App\Service\Admin\GridService;
use App\Service\Admin\OptionSystemService;
use App\Utils\User\Anonymous;
use App\Utils\User\Role;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Exception;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\String\ByteString;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserService extends AppAdminService
{

    /**
     * @var GridService
     */
    private GridService $gridService;

    /**
     * @var OptionSystemService
     */
    private OptionSystemService $optionSystemService;

    /**
     * @var UserPasswordHasherInterface
     */
    private UserPasswordHasherInterface $passwordHasher;

    /**
     * @param EntityManagerInterface $entityManager
     * @param ContainerBagInterface $containerBag
     * @param TranslatorInterface $translator
     * @param UrlGeneratorInterface $router
     * @param GridService $gridService
     * @param OptionSystemService $optionSystemService
     * @param Security $security
     * @param RequestStack $requestStack
     * @param UserPasswordHasherInterface $userPasswordHasher
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(
        EntityManagerInterface      $entityManager,
        ContainerBagInterface       $containerBag,
        TranslatorInterface         $translator,
        UrlGeneratorInterface       $router,
        GridService                 $gridService,
        OptionSystemService         $optionSystemService,
        Security                    $security,
        RequestStack                $requestStack,
        UserPasswordHasherInterface $userPasswordHasher,
        ParameterBagInterface  $parameterBag
    )
    {
        $this->gridService = $gridService;
        $this->optionSystemService = $optionSystemService;
        $this->passwordHasher = $userPasswordHasher;
        parent::__construct($entityManager, $containerBag, $translator, $router, $security, $requestStack, $parameterBag);
    }

    /**
     * Retourne une liste de user paginé
     * @param int $page
     * @param int $limit
     * @return Paginator
     */
    public function getAllPaginate(int $page, int $limit): Paginator
    {
        $repo = $this->getRepository(User::class);
        return $repo->getAllPaginate($page, $limit);
    }

    /**
     * Construit le tableau de donnée à envoyer au tableau GRID
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getAllFormatToGrid(int $page, int $limit): array
    {
        $column = [
            $this->translator->trans('user.grid.id', domain: 'user'),
            $this->translator->trans('user.grid.login', domain: 'user'),
            $this->translator->trans('user.grid.email', domain: 'user'),
            $this->translator->trans('user.grid.name', domain: 'user'),
            $this->translator->trans('user.grid.role', domain: 'user'),
            $this->translator->trans('user.grid.created_at', domain: 'user'),
            $this->translator->trans('user.grid.update_at', domain: 'user'),
            GridService::KEY_ACTION,
        ];

        $dataPaginate = $this->getAllPaginate($page, $limit);

        $nb = $dataPaginate->count();
        $data = [];
        foreach ($dataPaginate as $user) {
            /* @var User $user */

            $isDisabled = '';
            if ($user->isDisabled()) {
                $isDisabled = '<i class="bi bi-eye-slash"></i>';
            }

            $email = $user->getEmail();
            if ($user->isAnonymous()) {
                $isDisabled = '<i class="bi bi-recycle"></i>';
                $email = '--@anonyme.com';
            }

            if ($user->isFounder()) {
                $roles = $this->translator->trans('user.form_update.role.founder', domain: 'user');
            } else {
                $roles = '';
                foreach ($user->getRoles() as $role) {
                    $roles .= $this->gridService->renderRole($role) . ', ';
                }
                $roles = substr($roles, 0, -2);
            }


            $actions = $this->generateTabAction($user);
            $data[] = [
                $this->translator->trans('user.grid.id', domain: 'user') => $user->getId() . ' ' . $isDisabled,
                $this->translator->trans('user.grid.login', domain: 'user') => $user->getLogin(),
                $this->translator->trans('user.grid.email', domain: 'user') => $email,
                $this->translator->trans('user.grid.name', domain: 'user') => $user->getFirstname() . ' ' . $user->
                    getLastname(),
                $this->translator->trans('user.grid.role', domain: 'user') => $roles,
                $this->translator->trans('user.grid.created_at', domain: 'user') => $user->getCreatedAt()->
                format('d/m/y H:i'),
                $this->translator->trans('user.grid.update_at', domain: 'user') => $user->getUpdateAt()->
                format('d/m/y H:i'),
                GridService::KEY_ACTION => json_encode($actions)
            ];
        }

        $tabReturn = [
            GridService::KEY_NB => $nb,
            GridService::KEY_DATA => $data,
            GridService::KEY_COLUMN => $column,
        ];
        return $this->gridService->addAllDataRequiredGrid($tabReturn);

    }

    /**
     * Génère le tableau d'action pour le Grid des users
     * @param User $user
     * @return array[]|string[]
     */
    private function generateTabAction(User $user): array
    {

        $actions = [];

        if ($user->isAnonymous()) {
            return $actions;
        }

        $role = new Role($user);
        $isSuperAdmin = $user->isFounder() && $role->isSuperAdmin();
        if (!$isSuperAdmin) {
            // Bouton disabled
            $actionDisabled = ['label' => '<i class="bi bi-eye-slash-fill"></i>',
                'url' => $this->router->generate('admin_user_update_disabled', ['id' => $user->getId()]),
                'ajax' => true,
                'confirm' => true,
                'msgConfirm' =>
                    $this->translator->trans('user.confirm.disabled.msg', ['{login}' => $user->getLogin()], 'user')];
            if ($user->isDisabled()) {
                $actionDisabled = ['label' => '<i class="bi bi-eye-fill"></i>', 'url' =>
                    $this->router->generate('admin_user_update_disabled', ['id' => $user->getId()]), 'ajax' => true];
            }

            $actions[] = $actionDisabled;

            // Bouton Delete
            $actionDelete = '';
            if ($this->optionSystemService->canDelete()) {

                $msgConfirm = $this->translator->trans('user.confirm.delete.msg', ['{login}' =>
                    $user->getLogin()], 'user');
                $label = '<i class="bi bi-trash"></i>';
                if ($this->optionSystemService->canReplace()) {
                    $msgConfirm = $this->translator->trans('user.confirm.replace.msg', ['{login}' =>
                        $user->getLogin()], 'user');
                    $label = '<i class="bi bi-person-fill-slash"></i>';
                }

                $actionDelete = [
                    'label' => $label,
                    'url' => $this->router->generate('admin_user_delete', ['id' => $user->getId()]),
                    'ajax' => true,
                    'confirm' => true,
                    'msgConfirm' => $msgConfirm
                ];
            }

            if ($actionDelete != '') {
                $actions[] = $actionDelete;
            }

            if (!$user->isDisabled()) {
                $actions[] = ['label' => '<i class="bi bi-arrow-left-right"></i>',
                    'id' => $user->getId(),
                    'url' => $this->router->generate('admin_user_switch', ['user' => $user->getEmail()]),
                    'ajax' => false];
            }

        }

        // Bouton édition affiché sauf pour le fondateur ou si l'utilisateur courant est le fondateur
        if (($user->isFounder() && $this->security->getUser()->isFounder()) || !$user->isFounder()) {
            // Bouton edit
            $actions[] = ['label' => '<i class="bi bi-pencil-fill"></i>',
                'id' => $user->getId(),
                'url' => $this->router->generate('admin_user_update', ['id' => $user->getId()]),
                'ajax' => false];
        }

        return $actions;

    }

    /**
     * Renvoi les traductions pour le changement de mot de passe
     * @return array
     */
    public function getTranslateChangePassword(): array
    {
        return [
            'password' => $this->translator->trans('user.change_password.input.password_1', domain: 'user'),
            'password_2' => $this->translator->trans('user.change_password.input.password_2', domain: 'user'),
            'force' => $this->translator->trans('user.change_password.force', domain: 'user'),
            'force_nb_character' => $this->translator->trans('user.change_password.force.nb_character', domain: 'user'),
            'force_majuscule' => $this->translator->trans('user.change_password.force.majuscule', domain: 'user'),
            'force_minuscule' => $this->translator->trans('user.change_password.force.minuscule', domain: 'user'),
            'force_chiffre' => $this->translator->trans('user.change_password.force.chiffre', domain: 'user'),
            'force_character_spe' => $this->translator->trans(
                'user.change_password.force.character_spe',
                domain: 'user'
            ),
            'error_password_2' => $this->translator->trans('user.error.password_2', domain: 'user'),
        ];
    }

    /**
     * Renvoi les traductions pour la danger-zone de mon profil
     * @return array
     */
    public function getTranslateDangerZone(): array
    {
        return [
            'loading' => $this->translator->trans('user.danger_zone.loading', domain: 'user'),
            'btn_disabled' => $this->translator->trans('user.danger_zone.btn_disabled', domain: 'user'),
            'btn_delete' => $this->translator->trans('user.danger_zone.btn_delete', domain: 'user'),
            'btn_replace' => $this->translator->trans('user.danger_zone.btn_replace', domain: 'user'),
            'disabled_title' => $this->translator->trans('user.danger_zone.title_disabled', domain: 'user'),
            'disabled_content_1' => $this->translator->trans('user.danger_zone.content_disabled_1', domain: 'user'),
            'disabled_content_2' => $this->translator->trans('user.danger_zone.content_disabled_2', domain: 'user'),
            'disabled_btn_cancel' => $this->translator->trans('user.danger_zone.btn_cancel_disabled', domain: 'user'),
            'disabled_btn_confirm' => $this->translator->trans('user.danger_zone.btn_confirm_disabled', domain: 'user'),
            'delete_btn_cancel' => $this->translator->trans('user.danger_zone.btn_cancel_delete', domain: 'user'),
            'delete_btn_confirm' => $this->translator->trans('user.danger_zone.btn_confirm_delete', domain: 'user'),
            'delete_title' => $this->translator->trans('user.danger_zone.title_delete', domain: 'user'),
            'delete_content_1' => $this->translator->trans('user.danger_zone.content_delete_1', domain: 'user'),
            'delete_content_2' => $this->translator->trans('user.danger_zone.content_delete_2', domain: 'user'),
            'replace_btn_cancel' => $this->translator->trans('user.danger_zone.btn_cancel_replace', domain: 'user'),
            'replace_btn_confirm' => $this->translator->trans('user.danger_zone.btn_confirm_replace', domain: 'user'),
            'replace_title' => $this->translator->trans('user.danger_zone.title_replace', domain: 'user'),
            'replace_content_1' => $this->translator->trans('user.danger_zone.content_replace_1', domain: 'user'),
            'replace_content_2' => $this->translator->trans('user.danger_zone.content_replace_2', domain: 'user'),
        ];
    }

    /**
     * Met à jour le mot de passe de l'utilisateur passé en paramètre
     * @param User $user
     * @param $password
     * @return void
     */
    public function updatePassword(User $user, $password): void
    {
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $this->save($user);
    }

    /**
     * Permet d'anonymiser un user
     * @param User $user
     * @return void
     * @throws Exception
     */
    public function anonymizer(User $user): void
    {
        $anonymous = new Anonymous($user);
        $user = $anonymous->anonymizer();
        $this->save($user);
    }

    /**
     * Retourne une liste d'utilisateur en fonction de son role
     * @param string $role
     * @return mixed
     */
    public function getByRole(string $role): mixed
    {
        $repo = $this->getRepository(User::class);
        return $repo->findByRole($role);
    }

    /**
     * Retourne une liste de mail sous la forme d'un tableau d'une liste de user
     * @param array $liste
     * @return array
     */
    public function getTabMailByListeUser(array $liste): array
    {
        $return = [];
        /** @var User $user */
        foreach ($liste as $user) {
            $return[] = $user->getEmail();
        }
        return $return;
    }

    /**
     * Créer un nouvel user et génère un mot de passe
     * Si le champ login est null, la valeur avant le @ de l'email sera prise.
     * @param User $user
     * @return User
     */
    public function addUser(User $user): User
    {
        $password = ByteString::fromRandom(20)->toString();
        if ($user->getLogin() === null) {
            $user->setLogin(explode('@', $user->getEmail())[0]);
        }
        $this->updatePassword($user, $password);
        $this->save($user);
        return $user;
    }
}
