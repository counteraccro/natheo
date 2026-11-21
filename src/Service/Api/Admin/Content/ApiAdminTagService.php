<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Lecture et écriture des tags par l'API d'administration
 */

namespace App\Service\Api\Admin\Content;

use App\Api\V2\Exception\ConflictException;
use App\Dto\Api\Admin\AdminCollectionQuery;
use App\Entity\Admin\Content\Tag\Tag;
use App\Entity\Admin\Content\Tag\TagTranslation;
use App\Entity\Admin\System\User;
use App\Repository\Admin\Content\Tag\TagRepository;
use App\Repository\Admin\Content\Tag\TagTranslationRepository;
use App\Service\Api\AppApiService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ApiAdminTagService extends AppApiService
{
    /**
     * Page de la collection d'administration dans la langue de contenu (voir TagRepository::getAdminCollection())
     * @param AdminCollectionQuery $query
     * @return Paginator
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getCollection(AdminCollectionQuery $query): Paginator
    {
        return $this->getTagRepository()->getAdminCollection(
            $query,
            $this->getContentLocale(),
            $this->getDefaultLocale(),
        );
    }

    /**
     * @param int $id
     * @return Tag|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function find(int $id): ?Tag
    {
        return $this->getTagRepository()->find($id);
    }

    /**
     * Langues du site, dans l'ordre de app.supported_locales
     * @return string[]
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getLocales(): array
    {
        return $this->getSupportedLocales();
    }

    /**
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getDefaultLocale(): string
    {
        return $this->getOptionSystemService()->getDefaultLanguage();
    }

    /**
     * Crée un tag. Une langue du site absente de $labels reçoit un libellé libre tiré d'un libellé fourni
     * @param User|null $user auteur
     * @param array<string, string> $labels libellé par langue, au moins une langue du site
     * @param string|null $color couleur #rrggbb, tirée au hasard si null
     * @param bool $disabled
     * @return Tag
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function create(?User $user, array $labels, ?string $color = null, bool $disabled = false): Tag
    {
        $labels = $this->filterLabels($labels);
        if ($labels === []) {
            throw new \InvalidArgumentException('Un tag a besoin d\'au moins un libellé dans une langue du site');
        }

        $tag = (new Tag())
            ->setUser($user)
            ->setColor($color ?? $this->randomColor())
            ->setDisabled($disabled);
        $source = reset($labels);
        foreach ($this->getLocales() as $locale) {
            $tag->addTagTranslation(
                (new TagTranslation())
                    ->setLocale($locale)
                    ->setLabel($labels[$locale] ?? $this->getFreeLabel($locale, $source)),
            );
        }
        $this->save($tag);
        return $tag;
    }

    /**
     * Modifie un tag, une valeur null ou une langue absente restant inchangée
     * @param Tag $tag
     * @param array<string, string> $labels
     * @param string|null $color
     * @param bool|null $disabled
     * @return Tag
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function update(Tag $tag, array $labels = [], ?string $color = null, ?bool $disabled = null): Tag
    {
        if ($color !== null) {
            $tag->setColor($color);
        }
        if ($disabled !== null) {
            $tag->setDisabled($disabled);
        }
        foreach ($this->filterLabels($labels) as $locale => $label) {
            $translation = $tag
                ->getTagTranslations()
                ->findFirst(fn($key, TagTranslation $t) => $t->getLocale() === $locale);
            if ($translation === null) {
                $translation = (new TagTranslation())->setLocale($locale);
                $tag->addTagTranslation($translation);
            }
            $translation->setLabel($label);
        }
        $this->save($tag);
        return $tag;
    }

    /**
     * Supprime un tag qui n'est rattaché à aucune page
     * @param Tag $tag
     * @return void
     * @throws ConflictException si le tag est rattaché à une page
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function delete(Tag $tag): void
    {
        $count = $this->countPages($tag);
        if ($count > 0) {
            throw new ConflictException(
                $this->getTranslator()->trans('tag.error.delete_used', ['count' => $count], 'tag'),
            );
        }
        $this->getEntityManager()->remove($tag);
        $this->getEntityManager()->flush();
    }

    /**
     * @param Tag $tag
     * @return int
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function countPages(Tag $tag): int
    {
        return $this->getTagRepository()->countPages($tag);
    }

    /**
     * Tag dont le libellé de la langue vaut $label sans tenir compte de la casse, hors tag $excludedTagId
     * @param string $locale
     * @param string $label
     * @param int|null $excludedTagId
     * @return Tag|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function findByLabel(string $locale, string $label, ?int $excludedTagId = null): ?Tag
    {
        /** @var TagTranslationRepository $repository */
        $repository = $this->getRepository(TagTranslation::class);
        return $repository->findOneByLabel($locale, $label, $excludedTagId)?->getTag();
    }

    /**
     * Libellé libre dans la langue : « label (en) », puis « label (en 2) »… si le libellé est déjà pris
     * @param string $locale
     * @param string $label
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getFreeLabel(string $locale, string $label): string
    {
        $i = 1;
        do {
            $suffix = ' (' . $locale . ($i > 1 ? ' ' . $i : '') . ')';
            $candidate = mb_substr($label, 0, 255 - mb_strlen($suffix)) . $suffix;
            $i++;
        } while ($this->findByLabel($locale, $candidate) !== null);
        return $candidate;
    }

    /**
     * Couleur #rrggbb tirée au hasard
     * @return string
     */
    public function randomColor(): string
    {
        return sprintf('#%06x', random_int(0, 0xffffff));
    }

    /**
     * Libellés des langues du site, sans espaces autour
     * @param array<string, string> $labels
     * @return array<string, string>
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function filterLabels(array $labels): array
    {
        $labels = array_map(fn(string $label) => trim($label), $labels);
        return array_intersect_key($labels, array_flip($this->getLocales()));
    }

    /**
     * @return TagRepository
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getTagRepository(): TagRepository
    {
        /** @var TagRepository $repository */
        $repository = $this->getRepository(Tag::class);
        return $repository;
    }
}
