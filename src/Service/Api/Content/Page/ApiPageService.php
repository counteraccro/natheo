<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Service pour l'API Page
 */

namespace App\Service\Api\Content\Page;

use App\Dto\Api\Content\Page\ApiFindPageCategoryDto;
use App\Dto\Api\Content\Page\ApiFindPageContentDto;
use App\Dto\Api\Content\Page\ApiFindPageDto;
use App\Dto\Api\Content\Page\ApiFindPageTagDto;
use App\Entity\Admin\Content\Menu\Menu;
use App\Entity\Admin\Content\Menu\MenuElement;
use App\Entity\Admin\Content\Page\Page;
use App\Entity\Admin\Content\Page\PageStatistique;
use App\Entity\Admin\System\User;
use App\Enum\Admin\Content\Menu\MenuPosition;
use App\Enum\Admin\Content\Page\PageCategory;
use App\Enum\Admin\Content\Page\PageStatistics;
use App\Enum\Admin\Content\Page\PageStatus;
use App\Enum\Admin\System\Options\OptionUser;
use App\Repository\Admin\Content\Menu\MenuRepository;
use App\Repository\Admin\Content\Page\PageRepository;
use App\Security\Voter\ContentVoter;
use App\Service\Api\AppApiService;
use App\Service\Api\Content\ApiMenuService;
use App\Formatter\Api\Content\ApiPageFormater;
use App\Service\Admin\System\User\PersonalData;
use Doctrine\ORM\NonUniqueResultException;
use League\CommonMark\Exception\CommonMarkException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

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
     * @throws CommonMarkException
     */
    public function getPageForApi(ApiFindPageDto $dto, ?User $user = null): array
    {
        $page = $this->getPageBySlug($dto->getSlug(), $dto->getLocale(), $user);
        if (empty($page)) {
            return [];
        }

        $previewPage = $this->getPreviewPage($page, $dto, $user);
        if ($previewPage !== null) {
            $pageApi = $this->addPreviewContents($this->formatPageForApi($previewPage, $dto), $previewPage, $dto);
            $this->getPageDraftService()->releaseWorkingCopy($previewPage);
            return $pageApi;
        }

        // Les prévisualisations de brouillons par un contributeur ne comptent pas comme des lectures
        if ($page->getStatus() === PageStatus::PUBLISH->value) {
            $this->incrementReadCount($page);
        }

        return $this->formatPageForApi($page, $dto);
    }

    /**
     * Retourne la page telle qu'elle sera une fois son brouillon publié, si la prévisualisation est demandée
     * par un utilisateur qui peut modifier la page ; null sinon
     * @param Page $page
     * @param ApiFindPageDto $dto
     * @param User|null $user
     * @return Page|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getPreviewPage(Page $page, ApiFindPageDto $dto, ?User $user): ?Page
    {
        if (!$dto->isPreview() || $user === null) {
            return null;
        }
        if (!$this->getSecurity()->isGrantedForUser($user, ContentVoter::EDIT, $page)) {
            return null;
        }
        return $this->getPageDraftService()->getPreviewPage($page);
    }

    /**
     * Les contenus d'un brouillon n'existent pas encore en base : ils reçoivent un id négatif et leur rendu
     * est joint à la page (preview_contents), au format de l'endpoint content
     * @param array $pageApi
     * @param Page $previewPage
     * @param ApiFindPageDto $dto
     * @return array
     * @throws CommonMarkException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function addPreviewContents(array $pageApi, Page $previewPage, ApiFindPageDto $dto): array
    {
        $apiPageContentService = $this->getApiPageContentService();
        $pageApi['contents'] = [];
        $pageApi['preview_contents'] = [];
        $id = 0;
        foreach ($previewPage->getPageContents() as $pageContent) {
            $id--;
            $pageApi['contents'][] = [
                'id' => $id,
                'type' => $pageContent->getType(),
                'position' => $pageContent->getRenderBlock(),
            ];
            $contentDto = new ApiFindPageContentDto($id, $dto->getLocale(), 1, 20, $dto->getUserToken());
            $pageApi['preview_contents'][$id] =
                ['id' => $id] + $apiPageContentService->getFormatContent($pageContent, $contentDto);
        }
        return $pageApi;
    }

    /**
     * Formate la page et ses menus pour l'API
     * @param Page $page
     * @param ApiFindPageDto $dto
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function formatPageForApi(Page $page, ApiFindPageDto $dto): array
    {
        $apiPageFormater = new ApiPageFormater($page, $dto);
        $pageApi = $apiPageFormater->convertPage()->getPageForApi();

        if (!$dto->isShowMenus()) {
            return $pageApi;
        }
        $apiMenuService = $this->getApiMenuService();
        /** @var MenuRepository $menuRepo */
        $menuRepo = $apiMenuService->getRepository(Menu::class);

        foreach ($this->getMenusForPage($page) as $menu) {
            if (!$this->isMenuPositionRequested($menu->getPosition(), $dto)) {
                continue;
            }
            $pageApi = $this->getFormatedMenu($menu->getId(), $dto->getLocale(), $pageApi, $apiMenuService, $menuRepo);
        }
        return $pageApi;
    }

    /**
     * Retourne les menus affichés sur une page, dans l'ordre d'affichage : ceux de la page puis les menus par défaut
     * des positions libres (pas de menu gauche par défaut si la page a un menu droit, et inversement)
     * @param Page $page
     * @return Menu[]
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getMenusForPage(Page $page): array
    {
        $menus = [];
        $usedPositions = [];
        foreach ($page->getMenus() as $menu) {
            if ($menu->isDisabled()) {
                continue;
            }
            $menus[] = $menu;
            $usedPositions[$menu->getPosition()] = true;
        }

        $sides = [MenuPosition::POSITION_LEFT->value, MenuPosition::POSITION_RIGHT->value];
        $hasSideMenu = isset($usedPositions[$sides[0]]) || isset($usedPositions[$sides[1]]);

        /** @var MenuRepository $menuRepo */
        $menuRepo = $this->getRepository(Menu::class);
        $defaults = [];
        foreach ($menuRepo->findActiveDefaults() as $default) {
            $defaults[$default->getPosition()] = $default;
        }
        foreach ($defaults as $position => $default) {
            if (isset($usedPositions[$position]) || ($hasSideMenu && in_array($position, $sides, true))) {
                continue;
            }
            $menus[] = $default;
        }
        return $menus;
    }

    /**
     * Indique si la position de menu fait partie des positions demandées (paramètre menu_positions, 0 = toutes)
     * @param int $position
     * @param ApiFindPageDto $dto
     * @return bool
     */
    private function isMenuPositionRequested(int $position, ApiFindPageDto $dto): bool
    {
        $positions = $dto->getMenuPositions();
        return in_array(0, $positions, true) || in_array($position, $positions, true);
    }

    /**
     * Retourne une liste de page en fonction de la catégorie
     * @param ApiFindPageCategoryDto $dto
     * @param User|null $user
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getListingPageByCategoryForApi(ApiFindPageCategoryDto $dto, ?User $user = null): array
    {
        $return = [
            'pages' => [],
            'limit' => $dto->getLimit(),
            'current_page' => $dto->getPage(),
            'rows' => 0,
        ];

        $idCategory = $this->findCategoryId($dto->getCategory());
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
    public function getListingPagesByTag(ApiFindPageTagDto $dto, ?User $user = null): array
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

            $render = $page
                ->getUser()
                ->getOptionUserByKey(OptionUser::OU_DEFAULT_PERSONAL_DATA_RENDER->value)
                ->getValue();
            $personalData = new PersonalData($page->getUser(), $render);

            $pageTranslation = $page->getPageTranslationByLocale($locale);
            $return[] = [
                'title' => $pageTranslation->getTitre(),
                'slug' => $pageTranslation->getUrl(),
                'author' => $personalData->getPersonalData(),
                'created' => $page->getCreatedAt()->getTimestamp(),
                'update' => $page->getUpdateAt()->getTimestamp(),
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
        int $id,
        string $locale,
        array $return,
        ApiMenuService $apiMenuService,
        MenuRepository $menuRepo,
    ): array {
        $m = $menuRepo->getByIdForApi($id)[0];
        $repository = $this->getRepository(MenuElement::class);
        $m['menuElements'] = $repository->getMenuElementByMenuAndParent($m['id'], null, false);
        $menuApi = $apiMenuService->formatMenu($m, $locale);
        $return['menus'][$menuApi['position']] = $menuApi;

        return $return;
    }

    /**
     * Incrémente le compteur de lectures d'une page
     * @param Page $page
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function incrementReadCount(Page $page): void
    {
        $pageStatistiqueNbPageRead = $page->getPageStatistiqueByKey(PageStatistics::NB_READ->value);
        if ($pageStatistiqueNbPageRead === null) {
            $pageStatistiqueNbPageRead = (new PageStatistique())->setKey(PageStatistics::NB_READ->value)->setValue('0');
            $page->addPageStatistique($pageStatistiqueNbPageRead);
        }
        $pageStatistiqueNbPageRead->setValue(strval($pageStatistiqueNbPageRead->getValue() + 1));
        $this->save($pageStatistiqueNbPageRead);
    }

    /**
     * Retourne une page publiée et active en fonction de son id
     * @param int $id
     * @return Page|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function findPublishedPage(int $id): ?Page
    {
        /** @var Page|null $page */
        $page = $this->findOneById(Page::class, $id);
        return $this->isPageVisible($page) ? $page : null;
    }

    /**
     * Retourne une page publiée et active en fonction de son slug, la page d'accueil si le slug est vide
     * @param string $slug
     * @param string $locale
     * @return Page|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function findPublishedPageBySlug(string $slug, string $locale): ?Page
    {
        return $this->getPageBySlug($slug, $locale);
    }

    /**
     * Retourne les pages publiées, filtrées par catégorie, tag actif, slug ou page d'accueil.
     * Le slug désigne au plus une page, en priorité celle dont le slug est celui de $locale
     * @param int $page
     * @param int $limit
     * @param string $locale
     * @param PageCategory|null $category
     * @param int|null $tagId
     * @param string|null $slug
     * @param bool|null $landing
     * @return Paginator
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function findPublishedPages(
        int $page,
        int $limit,
        string $locale,
        ?PageCategory $category = null,
        ?int $tagId = null,
        ?string $slug = null,
        ?bool $landing = null,
    ): Paginator {
        $pageId = null;
        if ($slug !== null) {
            // Slug inconnu : id 0, aucune page
            $pageId = $slug === '' ? 0 : $this->getPageBySlug($slug, $locale)?->getId() ?? 0;
        }

        /** @var PageRepository $pageRepository */
        $pageRepository = $this->getRepository(Page::class);
        return $pageRepository->getPublishedPagesPaginate($page, $limit, $category, $tagId, $pageId, $landing);
    }

    /**
     * Retourne l'id de la catégorie correspondant au libellé traduit ou au slug, 0 si aucune.
     * Comparaison insensible à la casse et aux accents (ex : evenement)
     * @param string $category
     * @return int
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function findCategoryId(string $category): int
    {
        $slugger = new AsciiSlugger();
        $search = $slugger->slug(trim($category))->lower()->toString();
        foreach ($this->getPageService()->getAllCategories() as $id => $label) {
            if (
                $search === $slugger->slug($label)->lower()->toString() ||
                $search === PageCategory::from($id)->getSlug()
            ) {
                return $id;
            }
        }
        return 0;
    }

    /**
     * Retourne une page en fonction du slug, en priorité celle dont le slug est celui de $locale
     * @param string $slug
     * @param string $locale
     * @param User|null $user
     * @return Page|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getPageBySlug(string $slug, string $locale, ?User $user = null): ?Page
    {
        /** @var PageRepository $repository */
        $repository = $this->getRepository(Page::class);
        return $repository->getBySlug($slug, $this->getAllowedPageStatus($user), $locale);
    }
}
