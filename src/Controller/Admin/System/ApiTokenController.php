<?php

declare(strict_types=1);

namespace App\Controller\Admin\System;

use App\Controller\Admin\AppAdminController;
use App\Entity\Admin\System\ApiToken;
use App\Enum\Admin\Global\Breadcrumb;
use App\Enum\Admin\System\Options\OptionUser;
use App\Service\Admin\System\ApiTokenService;
use App\Utils\Translate\System\ApiTokenTranslate;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/{_locale}/api-token', name: 'admin_api_token_', requirements: ['_locale' => '%app.supported_locales%'])]
#[IsGranted('ROLE_SUPER_ADMIN')]
class ApiTokenController extends AppAdminController
{
    /**
     * Point d'entrée pour la gestion des tokens
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        $breadcrumb = [
            Breadcrumb::DOMAIN->value => 'api_token',
            Breadcrumb::BREADCRUMB->value => [
                'api_token.page_title_h1' => '#',
            ],
        ];

        return $this->render('admin/system/api_token/index.html.twig', [
            'breadcrumb' => $breadcrumb,
            'page' => 1,
            'limit' => $this->optionUserService->getValueByKey(OptionUser::OU_NB_ELEMENT->value),
        ]);
    }

    /**
     * Charge le tableau grid de apiToken en ajax
     * @param ApiTokenService $apiTokenService
     * @param Request $request
     * @param int $page
     * @param int $limit
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/load-grid-data/{page}/{limit}', name: 'load_grid_data', methods: ['GET'])]
    public function loadGridData(
        ApiTokenService $apiTokenService,
        Request $request,
        int $page = 1,
        int $limit = 20,
    ): JsonResponse {
        $queryParams = [
            'search' => $request->query->get('search'),
            'orderField' => $request->query->get('orderField'),
            'order' => $request->query->get('order'),
        ];

        $grid = $apiTokenService->getAllFormatToGrid($page, $limit, $queryParams);
        return $this->json($grid);
    }

    /**
     * Active ou désactive un token
     * @param ApiToken $apiToken
     * @param ApiTokenService $apiTokenService
     * @param TranslatorInterface $translator
     * @param Request $request
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/update-disabled/{id}', name: 'update_disabled', methods: ['PUT'])]
    public function updateDisabled(
        #[MapEntity(id: 'id')] ApiToken $apiToken,
        ApiTokenService $apiTokenService,
        TranslatorInterface $translator,
        Request $request,
    ): JsonResponse {
        if (!$this->isCsrfTokenValid(ApiTokenService::CSRF_TOKEN_UPDATE_DISABLED, $request->headers->get('X-CSRF-TOKEN'))) {
            return $this->jsonCsrfError($translator);
        }

        $apiToken->setDisabled(!$apiToken->isDisabled());
        $apiTokenService->save($apiToken);

        $msg = $translator->trans('api_token.success.no.disabled', ['label' => $apiToken->getName()], 'api_token');
        if ($apiToken->isDisabled()) {
            $msg = $translator->trans('api_token.success.disabled', ['label' => $apiToken->getName()], 'api_token');
        }

        return $this->json($apiTokenService->getResponseAjax($msg));
    }

    /**
     * Permet de supprimer un token
     * @param ApiToken $apiToken
     * @param ApiTokenService $apiTokenService
     * @param TranslatorInterface $translator
     * @param Request $request
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/delete/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(
        #[MapEntity(id: 'id')] ApiToken $apiToken,
        ApiTokenService $apiTokenService,
        TranslatorInterface $translator,
        Request $request,
    ): JsonResponse {
        if (!$this->isCsrfTokenValid(ApiTokenService::CSRF_TOKEN_DELETE, $request->headers->get('X-CSRF-TOKEN'))) {
            return $this->jsonCsrfError($translator);
        }

        $msg = $translator->trans('api_token.remove.success', ['label' => $apiToken->getName()], domain: 'api_token');
        $apiTokenService->remove($apiToken);
        return $this->json($apiTokenService->getResponseAjax($msg));
    }

    /**
     * Permet d'ajouter / éditer un ApiToken
     * @param ApiTokenService $apiTokenService
     * @param ApiTokenTranslate $apiTokenTranslate
     * @param ApiToken|null $apiToken
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws ExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/add', name: 'add', methods: ['GET'])]
    #[Route('/update/{id}', name: 'update', methods: ['GET'])]
    public function add(
        Request $request,
        ApiTokenService $apiTokenService,
        ApiTokenTranslate $apiTokenTranslate,
        #[MapEntity(id: 'id')] ?ApiToken $apiToken = null,
        ?int $id = null,
    ): Response {
        $breadcrumbTitle = 'api_token.update.page_title_h1';
        if ($apiToken === null && $id === null) {
            $apiToken = new ApiToken();
            $breadcrumbTitle = 'api_token.add.page_title_h1';
        }

        $breadcrumb = [
            Breadcrumb::DOMAIN->value => 'api_token',
            Breadcrumb::BREADCRUMB->value => [
                'api_token.page_title' => 'admin_api_token_index',
                $breadcrumbTitle => '#',
            ],
        ];

        $translate = $apiTokenTranslate->getTranslate();
        if ($apiToken) {
            $apiToken = $apiTokenService->getApiTokenFormData($apiToken);
        }

        return $this->render('admin/system/api_token/add_update.html.twig', [
            'breadcrumb' => $breadcrumb,
            'translate' => $translate,
            'apiToken' => $apiToken,
            'urls' => [
                'regenerate_token' => $this->generateUrl('admin_api_token_regenerate', ['id' => $apiToken['id'] ?? 0]),
                'save_api_token' => $this->generateUrl('admin_api_token_save'),
                'index_api_token' => $this->generateUrl('admin_api_token_index'),
                'delete_api_token' => $this->generateUrl('admin_api_token_delete', ['id' => $apiToken['id'] ?? 0]),
            ],
            'datas' => [
                'roles' => $apiTokenService->getRolesApi(),
            ],
            'csrfTokens' => [
                'save' => ApiTokenService::CSRF_TOKEN_SAVE,
                'regenerate' => ApiTokenService::CSRF_TOKEN_REGENERATE,
                'delete' => ApiTokenService::CSRF_TOKEN_DELETE,
            ],
        ]);
    }

    /**
     * Génère un nouveau token pour un ApiToken existant et le retourne en clair (une seule fois)
     * @param ApiToken $apiToken
     * @param ApiTokenService $apiTokenService
     * @param TranslatorInterface $translator
     * @param Request $request
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/regenerate/{id}', name: 'regenerate', methods: ['PUT'])]
    public function regenerateToken(
        #[MapEntity(id: 'id')] ApiToken $apiToken,
        ApiTokenService $apiTokenService,
        TranslatorInterface $translator,
        Request $request,
    ): JsonResponse {
        if (!$this->isCsrfTokenValid(ApiTokenService::CSRF_TOKEN_REGENERATE, $request->headers->get('X-CSRF-TOKEN'))) {
            return $this->jsonCsrfError($translator);
        }

        $token = $apiTokenService->regenerateToken($apiToken);
        $response = $apiTokenService->getResponseAjax(
            $translator->trans('api_token.regenerate.success', domain: 'api_token'),
        );
        $response['token'] = $response['success'] === true ? $token : '';
        return $this->json($response);
    }

    /**
     * Sauvegarde ou créer un ApiToken.
     * À la création, le token est généré côté serveur et retourné en clair une seule fois
     * @param Request $request
     * @param ApiTokenService $apiTokenService
     * @param TranslatorInterface $translator
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/save', name: 'save', methods: ['POST'])]
    public function saveApiToken(
        Request $request,
        ApiTokenService $apiTokenService,
        TranslatorInterface $translator,
    ): JsonResponse {
        if (!$this->isCsrfTokenValid(ApiTokenService::CSRF_TOKEN_SAVE, $request->headers->get('X-CSRF-TOKEN'))) {
            return $this->jsonCsrfError($translator);
        }

        $data = json_decode($request->getContent(), true);
        $dataApiToken = is_array($data['apiToken'] ?? null) ? $data['apiToken'] : [];
        if (trim((string) ($dataApiToken['name'] ?? '')) === '') {
            return $this->json(
                ['success' => false, 'msg' => $translator->trans('api_token.card.title.error', domain: 'api_token')],
                Response::HTTP_BAD_REQUEST,
            );
        }

        $id = intval($dataApiToken['id'] ?? 0);
        if ($id > 0) {
            $apiToken = $apiTokenService->findOneById(ApiToken::class, $id);
            if ($apiToken === null) {
                return $this->json(
                    ['success' => false, 'msg' => $translator->trans('api_token.update.no.mail.title', domain: 'api_token')],
                    Response::HTTP_NOT_FOUND,
                );
            }
            $apiTokenService->updateApiToken($apiToken, $dataApiToken);
            $response = $apiTokenService->getResponseAjax(
                $translator->trans('api_token.save.success', domain: 'api_token'),
            );
            $response['token'] = '';
            return $this->json($response);
        }

        $result = $apiTokenService->createApiToken($dataApiToken);
        $response = $apiTokenService->getResponseAjax(
            $translator->trans('api_token.new.token.success', domain: 'api_token'),
        );
        $response['token'] = $response['success'] === true ? $result['token'] : '';
        return $this->json($response);
    }

    /**
     * Réponse JSON en cas de jeton CSRF invalide
     * @param TranslatorInterface $translator
     * @return JsonResponse
     */
    private function jsonCsrfError(TranslatorInterface $translator): JsonResponse
    {
        return $this->json(
            ['success' => false, 'msg' => $translator->trans('api_token.error.csrf', domain: 'api_token')],
            Response::HTTP_FORBIDDEN,
        );
    }
}
