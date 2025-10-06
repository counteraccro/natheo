<?php
/**
 * API de génération d'un sitemap
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Controller\Api\v1\Global;

use App\Controller\Api\v1\AppApiController;
use App\Utils\Api\ApiConst;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/{api_version}/sitemap', name: 'api_sitemap_', requirements: ['_version' => '%app.api_version%'])]
#[IsGranted('ROLE_READ_API')]
class ApiSitemapController extends AppApiController
{
    /**
     * Génère un tableau pour sitemap
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/', name: 'sitemap', methods: ['GET'])]
    public function getSitemap(): jsonResponse
    {
        $apiSitemapService = $this->getApiSitemapService();
        return $this->apiResponse(ApiConst::API_MSG_SUCCESS, $apiSitemapService->getSitemap());
    }
}
