<?php
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Service pour l'API Page
 */

namespace App\Service\Api\Content\Page;

use App\Dto\Api\Content\Page\ApiFindPageCategoryDto;
use App\Dto\Api\Content\Page\ApiFindPageDto;
use App\Dto\Api\Content\Page\ApiFindPageTagDto;
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
use App\Utils\System\Options\OptionUserKey;
use App\Utils\System\User\PersonalData;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ApiPageService extends AppApiService
{
    /**
     * Retourne une page au format API
     * @param ApiFindPageDto $dto
     * @param User|null $user
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NonUniqueResultException
     * @throws NotFoundExceptionInterface
     */
    public function getPageForApi(ApiFindPageDto $dto, User $user = null): array
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

        if (!$dto->isShowMenus()) {
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
     * Retourne une liste de page en fonction de la catégorie
     * @param ApiFindPageCategoryDto $dto
     * @param User|null $user
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getListingPageByCategoryForApi(ApiFindPageCategoryDto $dto, User $user = null): array
    {
        $return = [
            'pages' => [],
            'limit' => $dto->getLimit(),
            'current_page' => $dto->getPage(),
            'rows' => 0,
        ];

        $pageService = $this->getPageService();
        $listeCategories = $pageService->getAllCategories();
        $idCategory = 0;
        foreach ($listeCategories as $id => $label) {
            if (strtolower(trim($label)) == strtolower(trim($dto->getCategory()))) {
                $idCategory = $id;
            }
        }

        if ($idCategory === 0) {
            return [];
        }

        /** @var PageRepository $pageRepository */
        $pageRepository = $this->getRepository(Page::class);
        $listePages = $pageRepository->getPagesByCategoryPaginate($dto->getPage(), $dto->getLimit(), $idCategory);
        $return['pages'] = $this->getFormatedListingPages($listePages, $dto->getLocale());
        $nb = $listePages->count();
        $return['rows'] = $nb;
        return $return;
    }

    /**
     * Retourne une liste de pages en fonction d'un tag
     * @param ApiFindPageTagDto $dto
     * @param User|null $user
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getListingPagesByTag(ApiFindPageTagDto $dto, User $user = null): array
    {
        $return = [
            'limit' => $dto->getLimit(),
            'current_page' => $dto->getPage(),
        ];

        /** @var PageRepository $pageRepository */
        $pageRepository = $this->getRepository(Page::class);
        $listePages = $pageRepository->getPagesByTagPaginate($dto->getPage(), $dto->getLimit(), $dto->getTag());
        $return['pages'] = $this->getFormatedListingPages($listePages, $dto->getLocale());
        $nb = $listePages->count();
        $return['rows'] = $nb;
        return $return;
    }

    /**
     * Formate au format API un listing de pages
     * @param Paginator $pages
     * @param string $locale
     * @return array
     */
    private function getFormatedListingPages(Paginator $pages, string $locale): array
    {
        $return = [];
        foreach ($pages as $page) {
            /** @var Page $page */

            $render = $page->getUser()->getOptionUserByKey(OptionUserKey::OU_DEFAULT_PERSONAL_DATA_RENDER)->getValue();
            $personalData = new PersonalData($page->getUser(), $render);

            $pageTranslation = $page->getPageTranslationByLocale($locale);
            $return[] = [
                'title' => $pageTranslation->getTitre(),
                'slug' => $pageTranslation->getUrl(),
                'author' => $personalData->getPersonalData(),
                'created' => $page->getCreatedAt()->getTimestamp(),
                'update' => $page->getUpdateAt()->getTimestamp()
            ];
        }
        return $return;
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
