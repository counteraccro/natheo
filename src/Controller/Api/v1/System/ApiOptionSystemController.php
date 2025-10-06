<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 *  Api Controller pour les options systems
 */

namespace App\Controller\Api\v1\System;

use App\Controller\Api\v1\AppApiController;
use App\Service\Admin\System\OptionSystemService;
use App\Service\Api\System\ApiOptionSystemService;
use App\Utils\Api\ApiConst;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[
    Route(
        '/api/{api_version}/options-systems',
        name: 'api_options_systems_',
        requirements: ['_version' => '%app.api_version%'],
    ),
]
#[IsGranted('ROLE_READ_API')]
class ApiOptionSystemController extends AppApiController
{
    /**
     * Retourne la liste des optionsSystems pour le front
     * @param ApiOptionSystemService $apiOptionSystemService
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('', name: 'listing', methods: ['GET'])]
    public function listing(ApiOptionSystemService $apiOptionSystemService): jsonResponse
    {
        return $this->apiResponse(ApiConst::API_MSG_SUCCESS, $apiOptionSystemService->getAllOptionSystemWithValue());
    }

    /**
     * Retourne une optionSystem en fonction de sa clé
     * @param ApiOptionSystemService $apiOptionSystemService
     * @param OptionSystemService $optionSystemService
     * @param string|null $key
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/{key}', name: 'get_by_key', methods: ['GET'])]
    public function getByKey(
        ApiOptionSystemService $apiOptionSystemService,
        OptionSystemService $optionSystemService,
        ?string $key = null,
    ): JsonResponse {
        if (!in_array($key, $apiOptionSystemService->getWhiteListeOptionSystem())) {
            $translator = $this->getTranslator();
            throw new HttpException(
                Response::HTTP_FORBIDDEN,
                $translator->trans('api_errors.find.option.system.not.found', domain: 'api_errors'),
            );
        }
        return $this->apiResponse(ApiConst::API_MSG_SUCCESS, [
            'key' => $key,
            'value' => $optionSystemService->getValueByKey($key),
        ]);
    }
}
