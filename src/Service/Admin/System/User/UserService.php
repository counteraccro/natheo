<?php

/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Service lier à l'objet user
 */

namespace App\Service\Admin\System\User;

use App\Entity\Admin\System\User;
use App\Service\Admin\AppAdminService;
use App\Service\Admin\GridService;
use App\Utils\System\User\Anonymous;
use App\Utils\System\User\Role;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\String\ByteString;

class UserService extends AppAdminService
{

    /**
     * Retourne une liste de user paginé
     * @param int $page
     * @param int $limit
     * @param string|null $search
     * @return Paginator
     */
    public function getAllPaginate(int $page, int $limit, string $search = null): Paginator
    {
        $repo = $this->getRepository(User::class);
        return $repo->getAllPaginate($page, $limit, $search);
    }

    /**
     * Construit le tableau de donnée à envoyer au tableau GRID
     * @param int $page
     * @param int $limit
     * @param string|null $search
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAllFormatToGrid(int $page, int $limit, string $search = null): array
    {
        $translator = $this->getTranslator();
        $gridService = $this->getGridService();

        $column = [
            $translator->trans('user.grid.id', domain: 'user'),
            $translator->trans('user.grid.login', domain: 'user'),
            $translator->trans('user.grid.email', domain: 'user'),
            $translator->trans('user.grid.name', domain: 'user'),
            $translator->trans('user.grid.role', domain: 'user'),
            $translator->trans('user.grid.created_at', domain: 'user'),
            $translator->trans('user.grid.update_at', domain: 'user'),
            GridService::KEY_ACTION,
        ];

        $dataPaginate = $this->getAllPaginate($page, $limit, $search);

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
                $roles = $translator->trans('user.form_update.role.founder', domain: 'user');
            } else {
                $roles = '';
                foreach ($user->getRoles() as $role) {
                    $roles .= $gridService->renderRole($role) . ', ';
                }
                $roles = substr($roles, 0, -2);
            }


            $actions = $this->generateTabAction($user);
            $data[] = [
                $translator->trans('user.grid.id', domain: 'user') => $user->getId() . ' ' . $isDisabled,
                $translator->trans('user.grid.login', domain: 'user') => $user->getLogin(),
                $translator->trans('user.grid.email', domain: 'user') => $email,
                $translator->trans('user.grid.name', domain: 'user') => $user->getFirstname() . ' ' . $user->
                    getLastname(),
                $translator->trans('user.grid.role', domain: 'user') => $roles,
                $translator->trans('user.grid.created_at', domain: 'user') => $user->getCreatedAt()->
                format('d/m/y H:i'),
                $translator->trans('user.grid.update_at', domain: 'user') => $user->getUpdateAt()->
                format('d/m/y H:i'),
                GridService::KEY_ACTION => $actions
            ];
        }

        $tabReturn = [
            GridService::KEY_NB => $nb,
            GridService::KEY_DATA => $data,
            GridService::KEY_COLUMN => $column,
            GridService::KEY_RAW_SQL => $gridService->getFormatedSQLQuery($dataPaginate)
        ];
        return $gridService->addAllDataRequiredGrid($tabReturn);

    }

    /**
     * Génère le tableau d'action pour le Grid des users
     * @param User $user
     * @return array[]|string[]
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function generateTabAction(User $user): array
    {
        $translator = $this->getTranslator();
        $router = $this->getRouter();
        $optionSystemService = $this->getOptionSystemService();
        $security = $this->getSecurity();

        $actions = [];

        if ($user->isAnonymous()) {
            return $actions;
        }

        $role = new Role($user);
        $isSuperAdmin = $user->isFounder() && $role->isSuperAdmin();
        if (!$isSuperAdmin) {
            // Bouton disabled
            $actionDisabled = ['label' => '<i class="bi bi-eye-slash-fill"></i>',
                'type' => 'put',
                'url' => $router->generate('admin_user_update_disabled', ['id' => $user->getId()]),
                'ajax' => true,
                'confirm' => true,
                'msgConfirm' =>
                    $translator->trans('user.confirm.disabled.msg', ['{login}' => $user->getLogin()], 'user')];
            if ($user->isDisabled()) {
                $actionDisabled = ['label' => '<i class="bi bi-eye-fill"></i>', 'type' => 'put', 'url' =>
                    $router->generate('admin_user_update_disabled', ['id' => $user->getId()]), 'ajax' => true];
            }

            $actions[] = $actionDisabled;

            // Bouton Delete
            $actionDelete = '';
            if ($optionSystemService->canDelete()) {

                $msgConfirm = $translator->trans('user.confirm.delete.msg', ['{login}' =>
                    $user->getLogin()], 'user');
                $label = '<i class="bi bi-trash"></i>';
                $type = 'delete';
                if ($optionSystemService->canReplace()) {
                    $msgConfirm = $translator->trans('user.confirm.replace.msg', ['{login}' =>
                        $user->getLogin()], 'user');
                    $label = '<i class="bi bi-person-fill-slash"></i>';
                    $type = 'put';
                }

                $actionDelete = [
                    'label' => $label,
                    'type' => $type,
                    'url' => $router->generate('admin_user_delete', ['id' => $user->getId()]),
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
                    'url' => $router->generate('admin_user_switch', ['user' => $user->getEmail()]),
                    'ajax' => false];
            }

        }

        // Bouton édition affiché sauf pour le fondateur ou si l'utilisateur courant est le fondateur
        if (($user->isFounder() && $security->getUser()->isFounder()) || !$user->isFounder()) {
            // Bouton edit
            $actions[] = ['label' => '<i class="bi bi-pencil-fill"></i>',
                'id' => $user->getId(),
                'url' => $router->generate('admin_user_update', ['id' => $user->getId()]),
                'ajax' => false];
        }

        return $actions;
    }

    /**
     * Met à jour le mot de passe de l'utilisateur passé en paramètre
     * @param User $user
     * @param $password
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function updatePassword(User $user, $password): void
    {
        $passwordHasher = $this->getUserPasswordHasher();

        $user->setPassword($passwordHasher->hashPassword($user, $password));
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
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
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
