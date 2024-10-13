<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Service pour les menus via API
 */

namespace App\Service\Api\Content;

use App\Dto\Api\Menu\ApiFindMenuDto;
use App\Entity\Admin\Content\Menu\Menu;
use App\Entity\Admin\System\User;
use App\Service\Api\AppApiService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ApiMenuService extends AppApiService
{

    /**
     * Retourne un menu formaté pour l'API
     * @param ApiFindMenuDto $dto
     * @param User|null $user
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getMenuForApi(ApiFindMenuDto $dto, User $user = null): array
    {
        $menu = $this->getMenuByIdOrPageUrl($dto);
        if ($menu === null) {
            return [];
        }
        return $this->formatMenuForApi($menu);
    }

    public function formatMenuForApi(Menu $menu): array
    {
        return [$menu->getName()];
    }

    /**
     * Retourne un menu en fonction de son id ou de pageUrl
     * @param ApiFindMenuDto $dto
     * @return Menu|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getMenuByIdOrPageUrl(ApiFindMenuDto $dto): ?Menu
    {
        if ($dto->getId() !== 0) {
            return $this->findOneById(Menu::class, $dto->getId());
        }

        $repository = $this->getRepository(Menu::class);
        return $repository->getByPageUrlAndPosition($dto->getPageSlug(), $dto->getPosition());
    }
}
