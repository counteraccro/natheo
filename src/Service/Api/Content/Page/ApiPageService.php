<?php
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Service pour l'API Page
 */
namespace App\Service\Api\Content\Page;

use App\Dto\Api\Content\Page\ApiFindPageDto;
use App\Entity\Admin\Content\Menu\Menu;
use App\Entity\Admin\Content\Menu\MenuElement;
use App\Entity\Admin\Content\Page\Page;
use App\Entity\Admin\System\User;
use App\Repository\Admin\Content\Menu\MenuRepository;
use App\Repository\Admin\Content\Page\PageRepository;
use App\Service\Api\AppApiService;
use App\Service\Api\Content\ApiMenuService;
use App\Utils\Api\Content\ApiPageFormater;
use App\Utils\Content\Page\PageStatistiqueKey;
use Doctrine\ORM\NonUniqueResultException;
use League\CommonMark\Exception\CommonMarkException;
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
     * @throws NonUniqueResultException
     * @throws NotFoundExceptionInterface
     * @throws CommonMarkException
     */
    public function getPageForApi(ApiFindPageDto $dto, User $user): array
    {
        $page = $this->getPageBySlug($dto->getSlug());
        if (empty($page)) {
            return [];
        }

        $pageStatistiqueNbPageRead = $page->getPageStatistiqueByKey(PageStatistiqueKey::KEY_PAGE_NB_READ);
        $pageStatistiqueNbPageRead->setValue($pageStatistiqueNbPageRead->getValue() + 1);
        $this->save($pageStatistiqueNbPageRead);

        $apiPageFormater = new ApiPageFormater($page, $dto);
        $pageApi = $apiPageFormater->convertPage()->getPageForApi();

        //$apiPageContentService = $this->getApiPageContentService();
        //$pageApi['contents'] = $apiPageContentService->getFormatContent($pageApi['contents']);

        if(!$dto->isShowMenus()) {
            return $pageApi;
        }
        $apiMenuService = $this->getApiMenuService();
        /** @var MenuRepository $menuRepo */
        $menuRepo = $apiMenuService->getRepository(Menu::class);

        $tmp = $menuRepo->getDefaultForApi();
        $tabDefault = [];
        foreach ($tmp as $default) {
            $tabDefault[$default['position']] = $default['id'];
        }

        foreach ($page->getMenus() as $menu) {
            unset($tabDefault[$menu->getPosition()]);
            $pageApi = $this->getFormatedMenu($menu->getId(), $dto->getLocale(), $pageApi, $apiMenuService, $menuRepo);
        }

        if (!empty($tabDefault)) {
            foreach ($tabDefault as $id) {
                $pageApi = $this->getFormatedMenu($id, $dto->getLocale(), $pageApi, $apiMenuService, $menuRepo);
            }
        }
        return $pageApi;
    }

    /**
     * Ajoute au tableau $return un menu en fonction de sa position
     * @param int $id
     * @param string $locale
     * @param array $return
     * @param ApiMenuService $apiMenuService
     * @param MenuRepository $menuRepo
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getFormatedMenu(
        int            $id,
        string         $locale,
        array          $return,
        ApiMenuService $apiMenuService,
        MenuRepository $menuRepo): array
    {

        $m = $menuRepo->getByIdForApi($id)[0];
        $repository = $this->getRepository(MenuElement::class);
        $m['menuElements'] = $repository->getMenuElementByMenuAndParent($m['id'], null, false);
        $menuApi = $apiMenuService->formatMenu($m, $locale, $this->getOptionSystemApi());
        $return['menus'][$menuApi['position']] = $menuApi;

        return $return;
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
