<?php
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
use App\Utils\Api\Content\ApiMenuFormater;
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
        if (empty($menu)) {
            return [];
        }
        return $this->formatMenu($menu, $dto->getLocale(), $this->getOptionSystemApi(), []);
    }

    /**
     * Format un menu
     * @param array $menu
     * @param string $locale
     * @param array $optionsSystemApi
     * @param array $datas
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function formatMenu(array $menu, string $locale, array $optionsSystemApi, array $datas = []): array
    {
        $pageService = $this->getPageService();
        $datas['pageCategories'] = $pageService->getAllCategories();

        $apiMenuFormater = new ApiMenuFormater($menu, $locale, $optionsSystemApi, $datas);
        return $apiMenuFormater->convertMenu()->getMenuFortApi();
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
        $repository = $this->getRepository(Menu::class);
        if ($dto->getId() !== 0) {
            $menu = $repository->getByIdForApi($dto->getId());
        } else {
            $menu = $repository->getByPageUrlAndPositionForApi($dto->getPageSlug(), $dto->getPosition());
        }

        if (!empty($menu)) {
            $menu = $menu[0];
            $repository = $this->getRepository(MenuElement::class);
            $menu['menuElements'] = $repository->getMenuElementByMenuAndParent($menu['id'], null, false);
        }

        return $menu;
    }
}
