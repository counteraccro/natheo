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
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Symfony\Component\String\ByteString;

class UserService extends AppAdminService
{
    /**
     * Retourne une liste de user paginé
     * @param int $page
     * @param int $limit
     * @param array $queryParams
     * @return Paginator
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAllPaginate(int $page, int $limit, array $queryParams): Paginator
    {
        $repo = $this->getRepository(User::class);
        return $repo->getAllPaginate($page, $limit, $queryParams);
    }

    /**
     * Construit le tableau de donnée à envoyer au tableau GRID
     * @param int $page
     * @param int $limit
     * @param array $queryParams
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAllFormatToGrid(int $page, int $limit, array $queryParams): array
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

        $dataPaginate = $this->getAllPaginate($page, $limit, $queryParams);

        $nb = $dataPaginate->count();
        $data = [];
        foreach ($dataPaginate as $user) {
            /* @var User $user */

            $email = $user->getEmail();
            if ($user->isAnonymous()) {
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

            $paramBag = $this->getParameterBag();

            $avatar = '';
            if ($user->getAvatar() !== null) {
                $avatar =
                    '<span class="flex flex-col items-center"><img src="/' .
                    $paramBag->get('app.path.avatar') .
                    $user->getAvatar() .
                    '" style="width: 40px; height: 40px; border-radius: 50%;" class="me-2" />';
            }

            $actions = $this->generateTabAction($user);
            $data[] = [
                $translator->trans('user.grid.id', domain: 'user') => $user->getId(),
                $translator->trans('user.grid.login', domain: 'user') => $avatar . $user->getLogin() . '</span>',
                $translator->trans('user.grid.email', domain: 'user') => $email,
                $translator->trans('user.grid.name', domain: 'user') =>
                    $user->getFirstname() . ' ' . $user->getLastname(),
                $translator->trans('user.grid.role', domain: 'user') => $roles,
                $translator->trans('user.grid.created_at', domain: 'user') => $user
                    ->getCreatedAt()
                    ->format('d/m/y H:i'),
                $translator->trans('user.grid.update_at', domain: 'user') => $user->getUpdateAt()->format('d/m/y H:i'),
                GridService::KEY_ACTION => $actions,
                'isDisabled' => $user->isDisabled(),
            ];
        }

        $tabReturn = [
            GridService::KEY_NB => $nb,
            GridService::KEY_DATA => $data,
            GridService::KEY_COLUMN => $column,
            GridService::KEY_RAW_SQL => $gridService->getFormatedSQLQuery($dataPaginate),
            GridService::KEY_LIST_ORDER_FIELD => [
                'id' => $translator->trans('user.grid.id', domain: 'user'),
                'login' => $translator->trans('user.grid.login', domain: 'user'),
                'email' => $translator->trans('user.grid.email', domain: 'user'),
                'firstname' => $translator->trans('user.grid.name', domain: 'user'),
                'roles' => $translator->trans('user.grid.role', domain: 'user'),
                'createdAt' => $translator->trans('user.grid.created_at', domain: 'user'),
                'updateAt' => $translator->trans('user.grid.update_at', domain: 'user'),
            ],
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
            $actionDisabled = [
                'label' => [
                    'M3.933 13.909A4.357 4.357 0 0 1 3 12c0-1 4-6 9-6m7.6 3.8A5.068 5.068 0 0 1 21 12c0 1-3 6-9 6-.314 0-.62-.014-.918-.04M5 19 19 5m-4 7a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
                ],
                'type' => 'put',
                'color' => 'primary',
                'url' => $router->generate('admin_user_update_disabled', ['id' => $user->getId()]),
                'ajax' => true,
                'confirm' => true,
                'msgConfirm' => $translator->trans(
                    'user.confirm.disabled.msg',
                    ['{login}' => $user->getLogin()],
                    'user',
                ),
            ];
            if ($user->isDisabled()) {
                $actionDisabled = [
                    'label' => [
                        'M21 12c0 1.2-4.03 6-9 6s-9-4.8-9-6c0-1.2 4.03-6 9-6s9 4.8 9 6Z',
                        'M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
                    ],
                    'color' => 'primary',
                    'type' => 'put',
                    'url' => $router->generate('admin_user_update_disabled', ['id' => $user->getId()]),
                    'ajax' => true,
                ];
            }

            $actions[] = $actionDisabled;

            // Bouton Delete
            $actionDelete = '';
            if ($optionSystemService->canDelete()) {
                $msgConfirm = $translator->trans('user.confirm.delete.msg', ['{login}' => $user->getLogin()], 'user');
                $label = [
                    'M5 7h14m-9 3v8m4-8v8M10 3h4a1 1 0 0 1 1 1v3H9V4a1 1 0 0 1 1-1ZM6 7h12v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7Z',
                ];
                $type = 'delete';
                if ($optionSystemService->canReplace()) {
                    $msgConfirm = $translator->trans(
                        'user.confirm.replace.msg',
                        ['{login}' => $user->getLogin()],
                        'user',
                    );
                    $label = [
                        'M16 12h4M4 18v-1a3 3 0 0 1 3-3h4a3 3 0 0 1 3 3v1a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1Zm8-10a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
                    ];
                    $type = 'put';
                }

                $actionDelete = [
                    'label' => $label,
                    'type' => $type,
                    'color' => 'danger',
                    'url' => $router->generate('admin_user_delete', ['id' => $user->getId()]),
                    'ajax' => true,
                    'confirm' => true,
                    'msgConfirm' => $msgConfirm,
                ];
            }

            if ($actionDelete != '') {
                $actions[] = $actionDelete;
            }

            if (!$user->isDisabled()) {
                $actions[] = [
                    'label' => [
                        'M16 19h4a1 1 0 0 0 1-1v-1a3 3 0 0 0-3-3h-2m-2.236-4a3 3 0 1 0 0-4M3 18v-1a3 3 0 0 1 3-3h4a3 3 0 0 1 3 3v1a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1Zm8-10a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
                    ],
                    'color' => 'success',
                    'id' => $user->getId(),
                    'url' => $router->generate('admin_user_switch', ['user' => $user->getEmail()]),
                    'ajax' => false,
                ];
            }
        }

        $isCurrentFounder = false;
        if ($security->getUser() !== null && $security->getUser()->isFounder()) {
            $isCurrentFounder = true;
        }

        // Bouton édition affiché sauf pour le fondateur ou si l'utilisateur courant est le fondateur
        if (($user->isFounder() && $isCurrentFounder) || !$user->isFounder()) {
            // Bouton edit
            $actions[] = [
                'label' => [
                    'M10.779 17.779 4.36 19.918 6.5 13.5m4.279 4.279 8.364-8.643a3.027 3.027 0 0 0-2.14-5.165 3.03 3.03 0 0 0-2.14.886L6.5 13.5m4.279 4.279L6.499 13.5m2.14 2.14 6.213-6.504M12.75 7.04 17 11.28',
                ],
                'color' => 'primary',
                'id' => $user->getId(),
                'url' => $router->generate('admin_user_update', ['id' => $user->getId()]),
                'ajax' => false,
            ];
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
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
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
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
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

    /**
     * Retourne un user en fonction de son login et mot de passe
     * @param string $email
     * @param string $password
     * @return User|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getUserByEmailAndPassword(string $email, string $password): ?User
    {
        $repo = $this->getRepository(User::class);
        /** @var User $user */

        $user = $repo->loadUserByIdentifier($email);
        if ($user === null) {
            return null;
        }

        $factory = new PasswordHasherFactory([
            'common' => ['algorithm' => 'auto'],
        ]);

        $hasher = $factory->getPasswordHasher('common');
        if ($hasher->verify($user->getPassword(), $password)) {
            return $user;
        }
        return null;
    }
}
