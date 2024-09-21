<?php

namespace App\Controller\Admin\System;

use App\Controller\Admin\AppAdminController;
use App\Entity\Admin\System\ApiToken;
use App\Service\Admin\System\ApiTokenService;
use App\Utils\Breadcrumb;
use App\Utils\System\ApiToken\ApiTokenConst;
use App\Utils\System\Options\OptionUserKey;
use App\Utils\Translate\System\ApiTokenTranslate;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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
            Breadcrumb::DOMAIN => 'api_token',
            Breadcrumb::BREADCRUMB => [
                'api_token.page_title_h1' => '#'
            ]
        ];

        return $this->render('admin/system/api_token/index.html.twig', [
            'breadcrumb' => $breadcrumb,
            'page' => 1,
            'limit' => $this->optionUserService->getValueByKey(OptionUserKey::OU_NB_ELEMENT),
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
        Request         $request,
        int             $page = 1,
        int             $limit = 20
    ): JsonResponse
    {
        $search = $request->query->get('search');
        $grid = $apiTokenService->getAllFormatToGrid($page, $limit, $search);
        return $this->json($grid);
    }

    /**
     * Active ou désactive un tag
     * @param ApiToken $apiToken
     * @param ApiTokenService $apiTokenService
     * @param TranslatorInterface $translator
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/update-disabled/{id}', name: 'update_disabled', methods: ['PUT'])]
    public function updateDisabled(
        #[MapEntity(id: 'id')] ApiToken $apiToken,
        ApiTokenService                 $apiTokenService,
        TranslatorInterface             $translator,
    ): JsonResponse
    {
        $apiToken->setDisabled(!$apiToken->isDisabled());
        $apiTokenService->save($apiToken);

        $msg = $translator->trans('api_token.success.no.disabled', ['label' => $apiToken->getName()], 'api_token');
        if ($apiToken->isDisabled()) {
            $msg = $translator->trans('api_token.success.disabled', ['label' => $apiToken->getName()], 'api_token');
        }

        return $this->json($apiTokenService->getResponseAjax($msg));
    }

    /**
     * Permet de supprimer un tag
     * @param ApiToken $apiToken
     * @param ApiTokenService $apiTokenService
     * @param TranslatorInterface $translator
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/delete/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(
        #[MapEntity(id: 'id')] ApiToken $apiToken,
        ApiTokenService                 $apiTokenService,
        TranslatorInterface             $translator,
    ): JsonResponse
    {
        $msg = $translator->trans('api_token.remove.success', ['label' => $apiToken->getName()], domain: 'api_token');
        $apiTokenService->remove($apiToken);
        return $this->json($apiTokenService->getResponseAjax($msg));
    }

    /**
     * Permet d'ajouter / éditer un ApiToken
     * @param ApiTokenService $apiTokenService
     * @param ApiTokenTranslate $apiTokenTranslate
     * @param Request $request
     * @param ApiToken|null $apiToken
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws ExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/add', name: 'add', methods: ['GET'])]
    #[Route('/update/{id}', name: 'update', methods: ['GET'])]
    public function add(
        ApiTokenService                 $apiTokenService,
        ApiTokenTranslate               $apiTokenTranslate,
        Request                         $request,
        #[MapEntity(id: 'id')] ApiToken $apiToken = null
    ): Response
    {
        $breadcrumbTitle = 'api_token.update.page_title_h1';
        if ($apiToken === null) {
            $apiToken = new ApiToken();
            $breadcrumbTitle = 'api_token.add.page_title_h1';
        }

        $breadcrumb = [
            Breadcrumb::DOMAIN => 'api_token',
            Breadcrumb::BREADCRUMB => [
                'api_token.page_title' => 'admin_api_token_index',
                $breadcrumbTitle => '#'
            ]
        ];

        $translate = $apiTokenTranslate->getTranslate();
        $apiToken = $apiTokenService->convertEntityToArray($apiToken, ['createdAt', 'updateAt']);

        return $this->render('admin/system/api_token/add_update.html.twig', [
            'breadcrumb' => $breadcrumb,
            'translate' => $translate,
            'apiToken' => $apiToken,
            'urls' => [
                'generate_token' => $this->generateUrl('admin_api_token_generate_token'),
                'save_api_token' => $this->generateUrl('admin_api_token_save')
            ],
            'datas' => [
                'roles' => $apiTokenService->getRolesApi()
            ]
        ]);
    }

    /**
     * Génère un nouveau token
     * @param ApiTokenService $apiTokenService
     * @return JsonResponse
     */
    #[Route('/generate-token', name: 'generate_token', methods: ['GET'])]
    public function generate_token(ApiTokenService $apiTokenService): JsonResponse
    {
        return $this->json(['token' => $apiTokenService->generateToken()]);
    }

    /**
     * Sauvegarde ou créer un ApiToken
     * @param ApiTokenService $apiTokenService
     * @return JsonResponse
     */
    #[Route('/save', name: 'save', methods: ['POST'])]
    public function saveApiToken(ApiTokenService $apiTokenService): JsonResponse
    {
        return $this->json(['oki']);
    }
}
