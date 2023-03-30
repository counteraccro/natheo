<?php

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Service lier à l'objet user
 */
namespace App\Service\Admin;

use App\Entity\Admin\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
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

    public function __construct(EntityManagerInterface $entityManager, ContainerBagInterface $containerBag,
                                TranslatorInterface    $translator, UrlGeneratorInterface $router, GridService $gridService,
                                OptionSystemService    $optionSystemService, Security $security, RequestStack $requestStack
    )
    {
        $this->gridService = $gridService;
        $this->optionSystemService = $optionSystemService;
        parent::__construct($entityManager, $containerBag, $translator, $router, $security, $requestStack);
    }

    /**
     * Retourne une liste de sidebarElement paginé
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
            $this->translator->trans('user.grid.id'),
            $this->translator->trans('user.grid.login'),
            $this->translator->trans('user.grid.email'),
            $this->translator->trans('user.grid.name'),
            $this->translator->trans('user.grid.role'),
            $this->translator->trans('user.grid.created_at'),
            $this->translator->trans('user.grid.update_at'),
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
                $this->translator->trans('user.grid.id') => $user->getId() . ' ' . $isDisabled,
                $this->translator->trans('user.grid.login') => $user->getLogin(),
                $this->translator->trans('user.grid.email') => $user->getEmail(),
                $this->translator->trans('user.grid.name') => $user->getFirstname() . ' ' . $user->getLastname(),
                $this->translator->trans('user.grid.role') => $roles,
                $this->translator->trans('user.grid.created_at') => $user->getCreatedAt()->format('d/m/y H:i'),
                $this->translator->trans('user.grid.update_at') => $user->getUpdateAt()->format('d/m/y H:i'),
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

        // Bouton disabled
        $actionDisabled = ['label' => '<i class="bi bi-eye-slash-fill"></i>',
            'url' => $this->router->generate('admin_user_update_disabled', ['id' => $user->getId()]),
            'ajax' => true,
            'confirm' => true,
            'msgConfirm' => $this->translator->trans('user.confirm.disabled.msg', ['{login}' => $user->getLogin()])];
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

            $msgConfirm = $this->translator->trans('user.confirm.delete.msg', ['{login}' => $user->getLogin()]);
            if ($replaceUser === '1') {
                $msgConfirm = $this->translator->trans('user.confirm.replace.msg', ['{login}' => $user->getLogin()]);
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

        // Bouton edit
        $actions[] = ['label' => '<i class="bi bi-pencil"></i>',
            'id' => $user->getId(),
            'url' => $this->router->generate('admin_user_update', ['id' => $user->getId()]),
            'ajax' => false];
        return $actions;

    }
}
