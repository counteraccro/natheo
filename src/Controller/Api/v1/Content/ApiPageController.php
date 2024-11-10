<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Controller pour les pages via API
 */

namespace App\Controller\Api\v1\Content;

use App\Controller\Api\v1\AppApiController;
use App\Dto\Api\Content\Page\ApiFindPageCategoryDto;
use App\Dto\Api\Content\Page\ApiFindPageContentDto;
use App\Dto\Api\Content\Page\ApiFindPageDto;
use App\Dto\Api\Content\Page\ApiFindPageTagDto;
use App\Resolver\Api\Content\Page\ApiFindPageCategoryResolver;
use App\Resolver\Api\Content\Page\ApiFindPageContentResolver;
use App\Resolver\Api\Content\Page\ApiFindPageResolver;
use App\Resolver\Api\Content\Page\ApiFindPageTagResolver;
use App\Utils\Api\ApiConst;
use Doctrine\ORM\NonUniqueResultException;
use League\CommonMark\Exception\CommonMarkException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/{api_version}/page', name: 'api_page_', requirements: ['_version' => '%app.api_version%'])]
#[IsGranted("ROLE_READ_API")]
class ApiPageController extends AppApiController
{
    /**
     * Retourne une page en fonction de son slug
     * @param ApiFindPageDto $apiFindPageDto
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NonUniqueResultException
     * @throws NotFoundExceptionInterface
     */
    #[Route('/find', name: 'find', methods: ['GET'])]
    public function index(#[MapQueryString(
        resolver: ApiFindPageResolver::class
    )] ApiFindPageDto $apiFindPageDto): JsonResponse
    {
        $user = null;
        if ($apiFindPageDto->getUserToken() !== "") {
            $user = $this->getUserByUserToken($apiFindPageDto->getUserToken());
        }

        $apiPageService = $this->getApiPageService();
        $page = $apiPageService->getPageForApi($apiFindPageDto, $user);
        if (empty($page)) {
            $translator = $this->getTranslator();
            throw new HttpException(Response::HTTP_FORBIDDEN, $translator->trans('api_errors.find.page.not.found', domain: 'api_errors'));
        }


        return $this->apiResponse(ApiConst::API_MSG_SUCCESS, ['page' => $page]);
    }

    /**
     * Retourne un pageContent en fonction de son id
     * @param ApiFindPageContentDto $apiFindPageContentDto
     * @return JsonResponse
     * @throws CommonMarkException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/content', name: 'content', methods: ['GET'])]
    public function getContentInPage(#[MapQueryString(
        resolver: ApiFindPageContentResolver::class
    )] ApiFindPageContentDto $apiFindPageContentDto): JsonResponse
    {
        $user = null;
        if ($apiFindPageContentDto->getUserToken() !== "") {
            $user = $this->getUserByUserToken($apiFindPageContentDto->getUserToken());
        }

        $apiPageContentService = $this->getApiPageContentService();
        $pageContent = $apiPageContentService->getPageContentForApi($apiFindPageContentDto, $user);
        if (empty($pageContent)) {
            $translator = $this->getTranslator();
            throw new HttpException(Response::HTTP_FORBIDDEN, $translator->trans('api_errors.find.page.content.not.found', domain: 'api_errors'));
        }
        return $this->apiResponse(ApiConst::API_MSG_SUCCESS, $pageContent);
    }

    /**
     * Retourne une liste de page en fonction de la catégorie
     * @param ApiFindPageCategoryDto $apiFindPageCategoryDto
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/category', name: 'category', methods: ['GET'])]
    public function getPageByCategory(#[MapQueryString(
        resolver: ApiFindPageCategoryResolver::class
    )] ApiFindPageCategoryDto $apiFindPageCategoryDto): JsonResponse
    {
        $user = null;
        if ($apiFindPageCategoryDto->getUserToken() !== "") {
            $user = $this->getUserByUserToken($apiFindPageCategoryDto->getUserToken());
        }

        $apiPageService = $this->getApiPageService();
        $listing = $apiPageService->getListingPageByCategoryForApi($apiFindPageCategoryDto, $user);
        if (empty($listing)) {
            $translator = $this->getTranslator();
            throw new HttpException(Response::HTTP_FORBIDDEN, $translator->trans('api_errors.find.listing.category.not.found', domain: 'api_errors'));
        }

        return $this->apiResponse(ApiConst::API_MSG_SUCCESS, $listing);
    }

    /**
     * @param ApiFindPageTagDto $apiFindPageTagDto
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/tag', name: 'category', methods: ['GET'])]
    public function getPageByTag(#[MapQueryString(
        resolver: ApiFindPageTagResolver::class
    )] ApiFindPageTagDto $apiFindPageTagDto) {
        return $this->apiResponse(ApiConst::API_MSG_SUCCESS, $apiFindPageTagDto);
    }
}
