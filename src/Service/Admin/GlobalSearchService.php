<?php
/**
 * Service pour la recherche globale
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Service\Admin;

use App\Entity\Admin\Content\Faq\Faq;
use App\Entity\Admin\Content\Faq\FaqCategory;
use App\Entity\Admin\Content\Menu\Menu;
use App\Entity\Admin\Content\Page\Page;
use App\Entity\Admin\Content\Tag\Tag;
use App\Entity\Admin\System\User;
use App\Utils\Content\Page\PageConst;
use App\Utils\System\Options\OptionUserKey;
use App\Utils\System\User\PersonalData;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class GlobalSearchService extends AppAdminService
{
    /**
     * Recherche globale
     * @param string $entity
     * @param string $search
     * @param int $page
     * @param int $limit
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function globalSearch(string $entity, string $search, int $page, int $limit): array
    {
        $translate = $this->getTranslator();

        $entity = $this->getEntityByString($entity);
        if ($entity === "") {
            return ['error' => $translate->trans('global_search.error.notFound', domain: 'global_search')];
        }

        $repository = $this->getRepository(ucfirst($entity));
        $result = $repository->search($search, $this->getLocales()['current'], $page, $limit);

        return $this->formatResult($result, $entity, $search);
    }

    /**
     * Retourne
     * @param string $entity
     * @return string
     */
    private function getEntityByString(string $entity): string
    {
        return match ($entity) {
            'page' => Page::class,
            'menu' => Menu::class,
            'faq' => Faq::class,
            'tag' => Tag::class,
            'user' => User::class,
            default => '',
        };
    }


    /**
     * @param Paginator $paginator
     * @param string $entity
     * @param string $search
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function formatResult(Paginator $paginator, string $entity, string $search): array
    {
        $return = ['elements' => [], 'total' => $paginator->count()];
        $locales = $this->getLocales();
        foreach ($paginator as $item) {
            switch ($entity) {
                case Page::class:
                    $return['elements'][] = $this->formatResulPage($item, $locales['current'], $search);
                    break;
                case Menu::class:
                    $return['elements'][] = $this->formatResulMenu($item, $locales['current'], $search);
                    break;
                case Faq::class:
                    $return['elements'][] = $this->formatResultFaq($item, $locales['current'], $search);
                    break;
                case Tag::class:
                    $return['elements'][] = $this->formatResultTag($item, $locales['current'], $search);
                    break;
                case User::class:
                    $return['elements'][] = $this->formatResultUser($item, $search);
                    break;
                default:

            }
        }

        return $return;
    }

    /**
     * Formatage des résultats pour les users
     * @param User $user
     * @param string $locale
     * @param string $search
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function formatResultUser(User $user, string $search): array
    {
        $router = $this->getRouter();

        $personalData = new PersonalData($user,
            $user->getOptionUserByKey(OptionUserKey::OU_DEFAULT_PERSONAL_DATA_RENDER)->getValue());

        $label = $this->highlightText($search, $personalData->getPersonalData());

        return [
            'id' => $user->getId(),
            'label' => $label,
            
            'date' => [
                'create' => $user->getCreatedAt()->format('d/m/y H:i'),
                'update' => $user->getUpdateAt()->format('d/m/y H:i')
            ],
            'urls' => [
                'edit' => $router->generate('admin_user_update', ['id' => $user->getId()]),
            ]
        ];
    }

    /**
     * Formatage des résultats pour les tags
     * @param Tag $tag
     * @param string $locale
     * @param string $search
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function formatResultTag(Tag $tag, string $locale, string $search): array
    {
        $router = $this->getRouter();

        $label = $tag->getTagTranslationByLocale($locale)->getLabel();
        $label = $this->highlightText($search, $label);

        return [
            'id' => $tag->getId(),
            'label' => $label,
            'contents' => [],
            'date' => [
                'create' => $tag->getCreatedAt()->format('d/m/y H:i'),
                'update' => $tag->getUpdateAt()->format('d/m/y H:i')
            ],
            'urls' => [
                'edit' => $router->generate('admin_tag_update', ['id' => $tag->getId()]),
            ]
        ];
    }

    /**
     * Formatage des résultats pour la recherche FAQ
     * @param Faq $faq
     * @param string $locale
     * @param string $search
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function formatResultFaq(Faq $faq, string $locale, string $search): array
    {
        $router = $this->getRouter();

        $label = $faq->getFaqTranslationByLocale($locale)->getTitle();
        $label = $this->highlightText($search, $label);

        $personalData = new PersonalData($faq->getUser(),
            $faq->getUser()->getOptionUserByKey(OptionUserKey::OU_DEFAULT_PERSONAL_DATA_RENDER)->getValue());

        $re = '/(\B||\b)((?-i:\w+[^\w\n]+){0,10}' . $search . '(\B||\b)(?-i:[^\w\n]+\w+){0,10})/mu';
        $content = [];
        foreach ($faq->getFaqCategories() as $faqCategory) {
            /** @var FaqCategory $faqCategory */
            $faqCategoryTranslation = $faqCategory->getFaqCategoryTranslationByLocale($locale);
            preg_match_all($re, $faqCategoryTranslation->getTitle(), $matches, PREG_SET_ORDER, 0);

            foreach ($matches as $matche) {
                $content[] = $this->highlightText($search, $matche[0]);
            }

            foreach ($faqCategory->getFaqQuestions() as $faqQuestion) {
                $faqQuestionTranslation = $faqQuestion->getFaqQuestionTranslationByLocale($locale);
                preg_match_all($re, $faqQuestionTranslation->getTitle(), $matches, PREG_SET_ORDER, 0);
                foreach ($matches as $matche) {
                    $content[] = $this->highlightText($search, $matche[0]);
                }

                preg_match_all($re, $faqQuestionTranslation->getAnswer(), $matches, PREG_SET_ORDER, 0);
                foreach ($matches as $matche) {
                    $content[] = $this->highlightText($search, $matche[0]);
                }
            }
        }

        return [
            'id' => $faq->getId(),
            'label' => $label,
            'contents' => $content,
            'date' => [
                'create' => $faq->getCreatedAt()->format('d/m/y H:i'),
                'update' => $faq->getUpdateAt()->format('d/m/y H:i')
            ],
            'author' => $this->highlightText($search, $personalData->getPersonalData()),
            'urls' => [
                'edit' => $router->generate('admin_faq_update', ['id' => $faq->getId()]),
            ]
        ];
    }

    /**
     * @param Menu $menu
     * @param string $locale
     * @param string $search
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function formatResulMenu(Menu $menu, string $locale, string $search): array
    {
        $router = $this->getRouter();
        $label = $menu->getName();
        $label = $this->highlightText($search, $label);

        $content = [];
        foreach ($menu->getMenuElements() as $element) {
            $elementTranslate = $element->getMenuElementTranslationByLocale($locale);

            $re = '/(\B||\b)((?-i:\w+[^\w\n]+){0,1}' . $search . '(\B||\b)(?-i:[^\w\n]+\w+){0,1})/mu';
            preg_match_all($re, $elementTranslate->getTextLink(), $matches, PREG_SET_ORDER, 0);

            foreach ($matches as $matche) {
                $content[] = $this->highlightText($search, $matche[0]);
            }

        }


        $personalData = new PersonalData($menu->getUser(),
            $menu->getUser()->getOptionUserByKey(OptionUserKey::OU_DEFAULT_PERSONAL_DATA_RENDER)->getValue());

        return [
            'id' => $menu->getId(),
            'label' => $label,
            'contents' => $content,
            'date' => [
                'create' => $menu->getCreatedAt()->format('d/m/y H:i'),
                'update' => $menu->getUpdateAt()->format('d/m/y H:i')
            ],
            'author' => $this->highlightText($search, $personalData->getPersonalData()),
            'urls' => [
                'edit' => $router->generate('admin_menu_update', ['id' => $menu->getId()]),
            ]
        ];
    }

    /**
     * @param Page $page
     * @param string $locale
     * @param string $search
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function formatResulPage(Page $page, string $locale, string $search): array
    {
        $label = $page->getPageTranslationByLocale($locale)->getTitre();
        $label = $this->highlightText($search, $label);

        $content = [];
        foreach ($page->getPageContents() as $pageContent) {
            if ($pageContent->getType() === PageConst::CONTENT_TYPE_TEXT) {
                $text = $pageContent->getPageContentTranslationByLocale($locale)->getText();
                $re = '/(\B||\b)((?-i:\w+[^\w\n]+){0,10}' . $search . '(\B||\b)(?-i:[^\w\n]+\w+){0,10})/mu';
                preg_match_all($re, $text, $matches, PREG_SET_ORDER, 0);

                foreach ($matches as $matche) {
                    $content[] = $this->highlightText($search, $matche[0]);
                }
            }
        }

        $router = $this->getRouter();
        $personalData = new PersonalData($page->getUser(),
            $page->getUser()->getOptionUserByKey(OptionUserKey::OU_DEFAULT_PERSONAL_DATA_RENDER)->getValue());

        return [
            'id' => $page->getId(),
            'label' => $label,
            'contents' => $content,
            'date' => [
                'create' => $page->getCreatedAt()->format('d/m/y H:i'),
                'update' => $page->getUpdateAt()->format('d/m/y H:i')
            ],
            'author' => $this->highlightText($search, $personalData->getPersonalData()),
            'urls' => [
                'edit' => $router->generate('admin_page_update', ['id' => $page->getId()]),
                'preview' => $router->generate('admin_page_preview', ['id' => $page->getId(), 'locale' => $locale]),
            ]
        ];
    }

    /**
     * Surligne un élement de recherche
     * @param string $search
     * @param string $text
     * @return string
     */
    private function highlightText(string $search, string $text): string
    {
        return str_ireplace($search, '<mark>' . $search . '</mark>', $text);
    }
}
