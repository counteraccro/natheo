<?php

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Service lier à l'objet user
 */

namespace App\Service\Admin;

use App\Entity\Admin\User;
use App\Repository\Admin\UserRepository;
use App\Utils\Role;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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

    public function __construct(EntityManagerInterface      $entityManager, ContainerBagInterface $containerBag,
                                TranslatorInterface         $translator, UrlGeneratorInterface $router, GridService $gridService,
                                OptionSystemService         $optionSystemService, Security $security, RequestStack $requestStack,
                                UserPasswordHasherInterface $userPasswordHasher
    )
    {
        $this->gridService = $gridService;
        $this->optionSystemService = $optionSystemService;
        $this->passwordHasher = $userPasswordHasher;
        parent::__construct($entityManager, $containerBag, $translator, $router, $security, $requestStack);
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

            $roles = '';
            foreach ($user->getRoles() as $role) {
                $roles .= $this->gridService->renderRole($role) . ', ';
            }
            $roles = substr($roles, 0, -2);

            $actions = $this->generateTabAction($user);
            $data[] = [
                $this->translator->trans('user.grid.id', domain: 'user') => $user->getId() . ' ' . $isDisabled,
                $this->translator->trans('user.grid.login', domain: 'user') => $user->getLogin(),
                $this->translator->trans('user.grid.email', domain: 'user') => $user->getEmail(),
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

        $role = new Role($user);
        $isSuperAdmin = $role->isSuperAdmin();
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

            $canDelete = $this->optionSystemService->getValueByKey(OptionSystemService::OS_ALLOW_DELETE_DATA);
            $replaceUser = $this->optionSystemService->getValueByKey(OptionSystemService::OS_REPLACE_DELETE_USER);

            // Bouton Delete
            $actionDelete = '';
            if ($canDelete === '1') {

                $msgConfirm = $this->translator->trans('user.confirm.delete.msg', ['{login}' =>
                    $user->getLogin()], 'user');
                if ($replaceUser === '1') {
                    $msgConfirm = $this->translator->trans('user.confirm.replace.msg', ['{login}' =>
                        $user->getLogin()], 'user');
                }

                $actionDelete = [
                    'label' => '<i class="bi bi-trash"></i>',
                    'url' => $this->router->generate('admin_user_delete', ['id' => $user->getId()]),
                    'ajax' => true,
                    'confirm' => true,
                    'msgConfirm' => $msgConfirm
                ];
            }

            if ($actionDelete != '') {
                $actions[] = $actionDelete;
            }
        }

        // Bouton edit
        $actions[] = ['label' => '<i class="bi bi-pencil"></i>',
            'id' => $user->getId(),
            'url' => $this->router->generate('admin_user_update', ['id' => $user->getId()]),
            'ajax' => false];
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
            'force_character_spe' => $this->translator->trans('user.change_password.force.character_spe',
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
        ];
    }

    /**
     * Met à jour le mot de passe de l'utilisateur passé en paramètre
     * @param User $user
     * @param $password
     * @return void
     */
    public function updatePassword(User $user, $password)
    {
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $this->save($user);
    }
}
