<?php

declare(strict_types=1);
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
use App\Utils\System\ApiToken\TokenHasher;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\String\ByteString;

class ApiTokenService extends AppAdminService
{
    /**
     * Identifiant du jeton CSRF pour l'activation / désactivation d'un token
     * @var string
     */
    public const string CSRF_TOKEN_UPDATE_DISABLED = 'api_token_update_disabled';

    /**
     * Identifiant du jeton CSRF pour la suppression d'un token
     * @var string
     */
    public const string CSRF_TOKEN_DELETE = 'api_token_delete';

    /**
     * Identifiant du jeton CSRF pour la sauvegarde d'un token
     * @var string
     */
    public const string CSRF_TOKEN_SAVE = 'api_token_save';

    /**
     * Identifiant du jeton CSRF pour la régénération d'un token
     * @var string
     */
    public const string CSRF_TOKEN_REGENERATE = 'api_token_regenerate';

    /**
     * Retourne une liste de apiToken paginé
     * @param int $page
     * @param int $limit
     * @param string|null $search
     * @return Paginator
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAllPaginate(int $page, int $limit, array $queryParams): Paginator
    {
        $repo = $this->getRepository(ApiToken::class);
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
            $translator->trans('api_token.grid.id', domain: 'api_token'),
            $translator->trans('api_token.grid.name', domain: 'api_token'),
            $translator->trans('api_token.grid.comment', domain: 'api_token'),
            $translator->trans('api_token.grid.token', domain: 'api_token'),
            $translator->trans('api_token.grid.roles', domain: 'api_token'),
            $translator->trans('api_token.grid.expires_at', domain: 'api_token'),
            $translator->trans('api_token.grid.last_used_at', domain: 'api_token'),
            $translator->trans('api_token.grid.created_at', domain: 'api_token'),
            $translator->trans('api_token.grid.update_at', domain: 'api_token'),
            GridService::KEY_ACTION,
        ];

        $dataPaginate = $this->getAllPaginate($page, $limit, $queryParams);

        $nb = $dataPaginate->count();
        $data = [];
        foreach ($dataPaginate as $apiToken) {
            /* @var ApiToken $apiToken */

            $actions = $this->generateTabAction($apiToken);
            $data[] = [
                $translator->trans('api_token.grid.id', domain: 'api_token') => $apiToken->getId(),
                $translator->trans('api_token.grid.name', domain: 'api_token') => $apiToken->getName(),
                $translator->trans('api_token.grid.comment', domain: 'api_token') => $apiToken->getComment(),
                $translator->trans('api_token.grid.token', domain: 'api_token') => '******',
                $translator->trans('api_token.grid.roles', domain: 'api_token') => implode(', ', $apiToken->getRoles()),
                $translator->trans('api_token.grid.expires_at', domain: 'api_token') => $this->formatExpiresAt(
                    $apiToken,
                ),
                $translator->trans('api_token.grid.last_used_at', domain: 'api_token') =>
                    $apiToken->getLastUsedAt()?->format('d/m/y H:i') ??
                    $translator->trans('api_token.grid.never', domain: 'api_token'),
                $translator->trans('api_token.grid.created_at', domain: 'api_token') => $apiToken
                    ->getCreatedAt()
                    ->format('d/m/y H:i'),
                $translator->trans('api_token.grid.update_at', domain: 'api_token') => $apiToken
                    ->getUpdateAt()
                    ->format('d/m/y H:i'),
                GridService::KEY_ACTION => $actions,
                'isDisabled' => $apiToken->isDisabled() || $apiToken->isExpired(),
            ];
        }

        $tabReturn = [
            GridService::KEY_NB => $nb,
            GridService::KEY_DATA => $data,
            GridService::KEY_COLUMN => $column,
            GridService::KEY_RAW_SQL => $gridService->getFormatedSQLQuery($dataPaginate),
            GridService::KEY_LIST_ORDER_FIELD => [
                'id' => $translator->trans('api_token.grid.id', domain: 'api_token'),
                'name' => $translator->trans('api_token.grid.name', domain: 'api_token'),
                'createdAt' => $translator->trans('api_token.grid.created_at', domain: 'api_token'),
                'updateAt' => $translator->trans('api_token.grid.update_at', domain: 'api_token'),
            ],
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
        $csrfTokenManager = $this->getCsrfTokenManager();
        $csrfDisabled = $csrfTokenManager->getToken(self::CSRF_TOKEN_UPDATE_DISABLED)->getValue();

        $actionDisabled = [
            'label' => [
                'M3.933 13.909A4.357 4.357 0 0 1 3 12c0-1 4-6 9-6m7.6 3.8A5.068 5.068 0 0 1 21 12c0 1-3 6-9 6-.314 0-.62-.014-.918-.04M5 19 19 5m-4 7a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
            ],
            'color' => 'primary',
            'type' => 'put',
            'url' => $router->generate('admin_api_token_update_disabled', ['id' => $apiToken->getId()]),
            'ajax' => true,
            'confirm' => true,
            'msgConfirm' => $translator->trans('api_token.confirm.disabled.msg', ['label' => $label], 'api_token'),
            'csrf' => $csrfDisabled,
        ];
        if ($apiToken->isDisabled()) {
            $actionDisabled = [
                'label' => [
                    'M21 12c0 1.2-4.03 6-9 6s-9-4.8-9-6c0-1.2 4.03-6 9-6s9 4.8 9 6Z',
                    'M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
                ],
                'color' => 'primary',
                'type' => 'put',
                'url' => $router->generate('admin_api_token_update_disabled', ['id' => $apiToken->getId()]),
                'ajax' => true,
                'csrf' => $csrfDisabled,
            ];
        }

        $actionDelete = '';
        if ($optionSystemService->canDelete()) {
            $actionDelete = [
                'label' => [
                    'M5 7h14m-9 3v8m4-8v8M10 3h4a1 1 0 0 1 1 1v3H9V4a1 1 0 0 1 1-1ZM6 7h12v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7Z',
                ],
                'color' => 'danger',
                'type' => 'delete',
                'url' => $router->generate('admin_api_token_delete', ['id' => $apiToken->getId()]),
                'ajax' => true,
                'confirm' => true,
                'msgConfirm' => $translator->trans('api_token.confirm.delete.msg', ['label' => $label], 'api_token'),
                'csrf' => $csrfTokenManager->getToken(self::CSRF_TOKEN_DELETE)->getValue(),
            ];
        }

        $actions = [];
        $actions[] = $actionDisabled;
        if ($actionDelete != '') {
            $actions[] = $actionDelete;
        }

        // Bouton edit
        $actions[] = [
            'label' => [
                'M10.779 17.779 4.36 19.918 6.5 13.5m4.279 4.279 8.364-8.643a3.027 3.027 0 0 0-2.14-5.165 3.03 3.03 0 0 0-2.14.886L6.5 13.5m4.279 4.279L6.499 13.5m2.14 2.14 6.213-6.504M12.75 7.04 17 11.28',
            ],
            'color' => 'primary',
            'type' => 'post',
            'id' => $apiToken->getId(),
            'url' => $router->generate('admin_api_token_update', ['id' => $apiToken->getId()]),
            'ajax' => false,
        ];

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
     * Crée un ApiToken à partir de $data. Le token est généré côté serveur et seul son hash est stocké
     * @param array $data
     * @return array{apiToken: ApiToken, token: string} l'entité créée et le token en clair, à n'afficher qu'une fois
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function createApiToken(array $data): array
    {
        $apiToken = new ApiToken();
        $apiToken->setDisabled(false);
        $this->hydrateApiToken($apiToken, $data);
        $token = $this->generateToken();
        $apiToken->setToken(TokenHasher::hash($token));

        $this->save($apiToken);
        return ['apiToken' => $apiToken, 'token' => $token];
    }

    /**
     * Met à jour les informations d'un ApiToken, sans modifier ni le token ni son statut
     * @param ApiToken $apiToken
     * @param array $data
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function updateApiToken(ApiToken $apiToken, array $data): void
    {
        $this->hydrateApiToken($apiToken, $data);
        $this->save($apiToken);
    }

    /**
     * Génère un nouveau token pour l'ApiToken, l'ancien devient immédiatement invalide
     * @param ApiToken $apiToken
     * @return string le token en clair, à n'afficher qu'une fois
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function regenerateToken(ApiToken $apiToken): string
    {
        $token = $this->generateToken();
        $apiToken->setToken(TokenHasher::hash($token));
        $apiToken->setLastUsedAt(null);
        $this->save($apiToken);
        return $token;
    }

    /**
     * Converti un ApiToken en tableau pour le formulaire d'édition, sans le hash du token
     * @param ApiToken $apiToken
     * @return array
     */
    public function getApiTokenFormData(ApiToken $apiToken): array
    {
        return [
            'id' => $apiToken->getId(),
            'name' => $apiToken->getName(),
            'comment' => $apiToken->getComment(),
            'roles' => array_values($this->filterRoles($apiToken->getRoles())),
            'disabled' => $apiToken->isDisabled(),
            'expiresAt' => $apiToken->getExpiresAt()?->format('Y-m-d'),
            'lastUsedAt' => $apiToken->getLastUsedAt()?->format('d/m/Y H:i'),
        ];
    }

    /**
     * Applique les données du formulaire sur l'ApiToken
     * @param ApiToken $apiToken
     * @param array $data
     * @return void
     */
    private function hydrateApiToken(ApiToken $apiToken, array $data): void
    {
        $apiToken->setName(trim((string) ($data['name'] ?? '')));
        $apiToken->setComment(isset($data['comment']) ? (string) $data['comment'] : null);
        $apiToken->setRoles($this->filterRoles((array) ($data['roles'] ?? [])));
        $apiToken->setExpiresAt($this->parseExpiresAt($data['expiresAt'] ?? null));
    }

    /**
     * Ne conserve que les rôles API autorisés, ROLE_READ_API par défaut
     * @param array $roles
     * @return array
     */
    private function filterRoles(array $roles): array
    {
        $roles = array_values(array_intersect($roles, array_keys(ApiTokenConst::API_TOKEN_ROLES)));
        if (empty($roles)) {
            return [ApiTokenConst::API_TOKEN_ROLE_READ];
        }
        return [$roles[0]];
    }

    /**
     * Converti la date d'expiration (Y-m-d) en fin de journée, null si vide ou invalide
     * @param mixed $expiresAt
     * @return \DateTime|null
     */
    private function parseExpiresAt(mixed $expiresAt): ?\DateTime
    {
        if (!is_string($expiresAt) || $expiresAt === '') {
            return null;
        }
        $date = \DateTime::createFromFormat('!Y-m-d', $expiresAt);
        return $date === false ? null : $date->setTime(23, 59, 59);
    }

    /**
     * Retourne la date d'expiration formatée pour le grid
     * @param ApiToken $apiToken
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function formatExpiresAt(ApiToken $apiToken): string
    {
        $translator = $this->getTranslator();
        if ($apiToken->getExpiresAt() === null) {
            return $translator->trans('api_token.grid.no_expiration', domain: 'api_token');
        }

        $date = $apiToken->getExpiresAt()->format('d/m/y');
        if ($apiToken->isExpired()) {
            return $translator->trans('api_token.grid.expired', ['date' => $date], 'api_token');
        }
        return $date;
    }
}
