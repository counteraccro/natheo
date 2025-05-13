<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Service lier à l'objet apiToken
 */

namespace App\Service\Admin\System;

use App\Entity\Admin\System\ApiToken;
use App\Service\Admin\AppAdminService;
use App\Service\Admin\GridService;
use App\Utils\System\ApiToken\ApiTokenConst;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\String\ByteString;

class ApiTokenService extends AppAdminService
{
    /**
     * Retourne une liste de apiToken paginé
     * @param int $page
     * @param int $limit
     * @param string|null $search
     * @return Paginator
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAllPaginate(int $page, int $limit, ?string $search = null): Paginator
    {
        $repo = $this->getRepository(ApiToken::class);
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
    public function getAllFormatToGrid(int $page, int $limit, ?string $search = null): array
    {
        $translator = $this->getTranslator();
        $gridService = $this->getGridService();

        $column = [
            $translator->trans('api_token.grid.id', domain: 'api_token'),
            $translator->trans('api_token.grid.name', domain: 'api_token'),
            $translator->trans('api_token.grid.comment', domain: 'api_token'),
            $translator->trans('api_token.grid.token', domain: 'api_token'),
            $translator->trans('api_token.grid.roles', domain: 'api_token'),
            $translator->trans('api_token.grid.created_at', domain: 'api_token'),
            $translator->trans('api_token.grid.update_at', domain: 'api_token'),
            GridService::KEY_ACTION,
        ];

        $dataPaginate = $this->getAllPaginate($page, $limit, $search);

        $nb = $dataPaginate->count();
        $data = [];
        foreach ($dataPaginate as $apiToken) {
            /* @var ApiToken $apiToken */

            $isDisabled = '';
            if ($apiToken->isDisabled()) {
                $isDisabled = '<i class="bi bi-eye-slash"></i>';
            }

            $actions = $this->generateTabAction($apiToken);
            $data[] = [
                $translator->trans('api_token.grid.id', domain: 'api_token') => $apiToken->getId() . ' ' . $isDisabled,
                $translator->trans('api_token.grid.name', domain: 'api_token') => $apiToken->getName(),
                $translator->trans('api_token.grid.comment', domain: 'api_token') => $apiToken->getComment(),
                $translator->trans('api_token.grid.token', domain: 'api_token') => "******",
                $translator->trans('api_token.grid.roles', domain: 'api_token') => implode(", ", $apiToken->getRoles()),
                $translator->trans('api_token.grid.created_at', domain: 'api_token') => $apiToken->getCreatedAt()->
                format('d/m/y H:i'),
                $translator->trans('api_token.grid.update_at', domain: 'api_token') => $apiToken->getUpdateAt()->
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
     * Génère le tableau d'action pour le Grid des apiTokens
     * @return array[]|string[]
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function generateTabAction(ApiToken $apiToken): array
    {
        $translator = $this->getTranslator();
        $router = $this->getRouter();
        $optionSystemService = $this->getOptionSystemService();

        $label = $apiToken->getName();

        $actionDisabled = ['label' => '<i class="bi bi-eye-slash-fill"></i>',
            'type' => 'put',
            'url' => $router->generate('admin_api_token_update_disabled', ['id' => $apiToken->getId()]),
            'ajax' => true,
            'confirm' => true,
            'msgConfirm' => $translator->trans('api_token.confirm.disabled.msg', ['label' => $label], 'api_token')];
        if ($apiToken->isDisabled()) {
            $actionDisabled = [
                'label' => '<i class="bi bi-eye-fill"></i>',
                'type' => 'put',
                'url' => $router->generate('admin_api_token_update_disabled', ['id' => $apiToken->getId()]),
                'ajax' => true
            ];
        }

        $actionDelete = '';
        if ($optionSystemService->canDelete()) {

            $actionDelete = [
                'label' => '<i class="bi bi-trash"></i>',
                'type' => 'delete',
                'url' => $router->generate('admin_api_token_delete', ['id' => $apiToken->getId()]),
                'ajax' => true,
                'confirm' => true,
                'msgConfirm' => $translator->trans('api_token.confirm.delete.msg', ['label' =>
                    $label], 'api_token')
            ];
        }

        $actions = [];
        $actions[] = $actionDisabled;
        if ($actionDelete != '') {
            $actions[] = $actionDelete;
        }

        // Bouton edit
        $actions[] = ['label' => '<i class="bi bi-pencil-fill"></i>',
            'type' => 'post',
            'id' => $apiToken->getId(),
            'url' => $router->generate('admin_api_token_update', ['id' => $apiToken->getId()]),
            'ajax' => false];

        return $actions;
    }

    /**
     * Génère un nouveau token
     * @return string
     */
    public function generateToken(): string
    {
        $token = '';
        for ($i = 0; $i < ApiTokenConst::API_TOKEN_SEGMENT; $i++) {
            $token .= ByteString::fromRandom(ApiTokenConst::API_TOKEN_LENGTH)->toString() . '.';
        }
        return substr($token, 0, -1);

    }

    /**
     * Retourne l'ensemble des roles lié aux ApiToken
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getRolesApi(): array
    {
        $translator = $this->getTranslator();
        $roles = ApiTokenConst::API_TOKEN_ROLES;
        $roles[ApiTokenConst::API_TOKEN_ROLE_READ] = $translator->trans('api_token.role.read', domain: 'api_token');
        $roles[ApiTokenConst::API_TOKEN_ROLE_WRITE] = $translator->trans('api_token.role.write', domain: 'api_token');
        $roles[ApiTokenConst::API_TOKEN_ROLE_ADMIN] = $translator->trans('api_token.role.admin', domain: 'api_token');
        return $roles;
    }

    /**
     * Edite un APi Token si il existe ou le crée dans le cas contraire en fonction de $data
     * @param array $data
     * @return int
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function createUpdateApiToken(array $data): int
    {
        $apiToken = new ApiToken();
        if($data['id'] !== null || $data['id'] > 0)
        {
            $apiToken = $this->findOneById(ApiToken::class, $data['id']);
        }
        $apiToken->setDisabled(false);
        $apiToken->setName($data['name']);
        $apiToken->setRoles($data['roles']);
        $apiToken->setComment($data['comment']);
        $apiToken->setToken($data['token']);

        $this->save($apiToken);
        return $apiToken->getId();
    }

    /**
     * Retourne un token valide pour la préview
     * @return string|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getTokenForPreview(): ?string
    {
        /** @var ApiToken $apiToken */
        $apiToken = $this->findBy(ApiToken::class, ['disabled' => false], ['id' => 'DESC'], 1);
        if(!empty($apiToken))
        {
            return $apiToken[0]->getToken();
        }
        return null;
    }
}
