<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Convertit une page en ressource Page de l'API v2
 */

namespace App\Api\V2\Mapper\Content;

use App\Api\V2\Output\Content\Author;
use App\Api\V2\Output\Content\Meta;
use App\Api\V2\Output\Content\Tag;
use App\Api\V2\Resource\Content\Menu;
use App\Api\V2\Resource\Content\Page;
use App\Api\V2\Resource\Content\PageContent;
use App\Entity\Admin\Content\Page\Page as PageEntity;
use App\Entity\Admin\Content\Page\PageContent as PageContentEntity;
use App\Entity\Admin\System\User;
use App\Enum\Admin\Content\Page\PageCategory;
use App\Enum\Admin\Content\Page\PageContentType;
use App\Enum\Admin\Content\Page\PageRender;
use App\Enum\Admin\Content\Page\PageStatistics;
use App\Enum\Admin\System\Options\OptionUser;
use App\Service\Admin\System\User\PersonalData;

final readonly class PageMapper
{
    public function __construct(private string $locale) {}

    /**
     * Page complète, avec les blocs et les menus fournis
     * @param PageEntity $entity
     * @param PageContentEntity[] $pageContents blocs à lister
     * @param Menu[] $menus
     * @return Page
     */
    public function map(PageEntity $entity, array $pageContents = [], array $menus = []): Page
    {
        $page = $this->mapSummary($entity);
        $page->render = PageRender::tryFrom((int) $entity->getRender())?->getSlug() ?? '';
        $page->landingPage = (bool) $entity->isLandingPage();
        $page->openComment = (bool) $entity->isOpenComment();
        $page->reads = (int) ($entity->getPageStatistiqueByKey(PageStatistics::NB_READ->value)?->getValue() ?? 0);
        $page->menus = $menus;

        foreach ($entity->getPageMetas() as $pageMeta) {
            $page->seo[] = new Meta(
                (string) $pageMeta->getName(),
                (string) $pageMeta->getPageMetaTranslationByLocale($this->locale)?->getValue(),
            );
        }

        foreach ($pageContents as $pageContent) {
            $content = new PageContent();
            $content->id = (int) $pageContent->getId();
            $content->type = PageContentType::tryFrom((int) $pageContent->getType())?->getSlug() ?? '';
            $content->position = (int) $pageContent->getRenderBlock();
            $page->contents[] = $content;
        }
        return $page;
    }

    /**
     * Page réduite aux champs des listes
     * @param PageEntity $entity
     * @return Page
     */
    public function mapSummary(PageEntity $entity): Page
    {
        $translation = $entity->getPageTranslationByLocale($this->locale);

        $page = new Page();
        $page->id = (int) $entity->getId();
        $page->title = (string) $translation?->getTitre();
        $page->slug = (string) $translation?->getUrl();
        $page->category = PageCategory::tryFrom((int) $entity->getCategory())?->getSlug() ?? '';
        $page->headerImage = $entity->getHeaderImg() ?: null;
        $page->author = $this->getAuthor($entity->getUser());
        $page->createdAt = $this->toImmutable($entity->getCreatedAt());
        $page->updatedAt = $this->toImmutable($entity->getUpdateAt());

        foreach ($entity->getTags() as $tag) {
            if ($tag->isDisabled()) {
                continue;
            }
            $page->tags[] = new Tag(
                (int) $tag->getId(),
                (string) $tag->getTagTranslationByLocale($this->locale)?->getLabel(),
                (string) $tag->getColor(),
            );
        }
        return $page;
    }

    /**
     * Auteur affiché selon son option de rendu des données personnelles
     * @param User|null $user
     * @return Author|null
     */
    private function getAuthor(?User $user): ?Author
    {
        if ($user === null) {
            return null;
        }
        $render = $user->getOptionUserByKey(OptionUser::OU_DEFAULT_PERSONAL_DATA_RENDER->value)?->getValue();
        $personalData = $render === null ? new PersonalData($user) : new PersonalData($user, $render);
        $name = $personalData->getPersonalData();
        return new Author($name, $user->getDescription() ?: null, $user->getAvatar() ?: null);
    }

    /**
     * @param \DateTimeInterface|null $date
     * @return \DateTimeImmutable|null
     */
    private function toImmutable(?\DateTimeInterface $date): ?\DateTimeImmutable
    {
        return $date === null ? null : \DateTimeImmutable::createFromInterface($date);
    }
}
