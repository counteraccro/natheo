<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 *
 */

namespace App\Controller\Api\v1\System;

use App\Controller\Api\v1\AppApiController;
use App\Utils\Api\ApiConst;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/{api_version}/options-systems', name: 'api_options_systems_', requirements: ['_version' => '%app.api_version%'])]
#[IsGranted("ROLE_READ_API")]
class ApiOptionSystemController extends AppApiController
{

    /**
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/', name: 'listing', methods: ['GET'])]
    public function listing(): jsonResponse
    {

        return $this->apiResponse(ApiConst::API_MSG_SUCCESS, []);
    }
}
