<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Service pour les menus via API
 */

namespace App\Service\Api\Content;

use App\Dto\Api\Content\Menu\ApiFindMenuDto;
use App\Entity\Admin\Content\Menu\Menu;
use App\Entity\Admin\Content\Menu\MenuElement;
use App\Entity\Admin\System\User;
use App\Service\Api\AppApiService;
use App\Formatter\Api\Content\ApiMenuFormater;
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
    public function getMenuForApi(ApiFindMenuDto $dto, ?User $user = null): array
    {
        $menu = $this->getMenuByIdOrPageUrl($dto);
        if (empty($menu)) {
            return [];
        }
        return $this->formatMenu($menu, $dto->getLocale());
    }

    /**
     * Format un menu
     * @param array $menu
     * @param string $locale
     * @return array
     */
    public function formatMenu(array $menu, string $locale): array
    {
        $apiMenuFormater = new ApiMenuFormater($menu, $locale);
        return $apiMenuFormater->convertMenu()->getMenuFortApi();
    }

    /**
     * Retourne un menu actif et ses éléments de premier niveau en fonction de son id, [] s'il n'existe pas
     * @param int $id
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function findMenuForApi(int $id): array
    {
        $menu = $this->getRepository(Menu::class)->getByIdForApi($id);
        return $this->addMenuElements($menu);
    }

    /**
     * Retourne un menu en fonction de son id ou de pageUrl
     * @param ApiFindMenuDto $dto
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getMenuByIdOrPageUrl(ApiFindMenuDto $dto): array
    {
        if ($dto->getId() !== 0) {
            return $this->findMenuForApi($dto->getId());
        }
        $menu = $this->getRepository(Menu::class)->getByPageUrlAndPositionForApi($dto->getPageSlug(), $dto->getPosition());
        return $this->addMenuElements($menu);
    }

    /**
     * Garde le premier menu trouvé et lui ajoute ses éléments de premier niveau actifs
     * @param array $menus
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function addMenuElements(array $menus): array
    {
        if (empty($menus)) {
            return [];
        }
        $menu = $menus[0];
        $menu['menuElements'] = $this->getRepository(MenuElement::class)->getMenuElementByMenuAndParent(
            $menu['id'],
            null,
            false,
        );
        return $menu;
    }
}
