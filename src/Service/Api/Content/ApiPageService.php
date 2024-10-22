<?php

namespace App\Service\Api\Content;

use App\Dto\Api\Content\Page\ApiFindPageDto;
use App\Entity\Admin\Content\Page\Page;
use App\Entity\Admin\System\User;
use App\Repository\Admin\Content\Page\PageRepository;
use App\Service\Api\AppApiService;
use App\Utils\Api\Content\ApiPageFormater;
use Doctrine\ORM\NonUniqueResultException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ApiPageService extends AppApiService
{
    /**
     * Retourne une page au format API
     * @param ApiFindPageDto $dto
     * @param User $user
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getPageForApi(ApiFindPageDto $dto, User $user) :array
    {
        $page = $this->getPageBySlug($dto->getSlug());
        if(empty($page))
        {
            return [];
        }

        $apiPageFormater = new ApiPageFormater($page, $dto);
        return $apiPageFormater->convertPage()->getPageForApi();
    }

    /**
     * Retourne une page en fonction du slug
     * @param string $slug
     * @return Page|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws NonUniqueResultException
     */
    private function getPageBySlug(string $slug): ?Page
    {
        /** @var PageRepository $repository */
        $repository = $this->getRepository(Page::class);
        return $repository->getBySlug($slug);
    }
}
