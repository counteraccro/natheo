<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Service gérant les tags de l'application
 */

namespace App\Service\Admin\Content\Tag;

use App\Entity\Admin\Content\Tag\Tag;
use App\Entity\Admin\Content\Tag\TagTranslation;
use App\Entity\Admin\System\User;
use App\Enum\Admin\Content\Tag\TagCsrfToken;
use App\Repository\Admin\Content\Tag\TagRepository;
use App\Repository\Admin\Content\Tag\TagTranslationRepository;
use App\Security\Voter\ContentVoter;
use App\Service\Admin\AppAdminService;
use App\Service\Admin\GridService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class TagService extends AppAdminService
{
    /**
     * Retourne une liste de tag paginé
     * @param int $page
     * @param int $limit
     * @param array $queryParams
     * @param int|null $userId
     * @return Paginator
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAllPaginate(int $page, int $limit, array $queryParams, ?int $userId = null): Paginator
    {
        $repo = $this->getRepository(Tag::class);
        return $repo->getAllPaginate($page, $limit, $queryParams, $userId);
    }

    /**
     * Construit le tableau de donnée à envoyé au tableau GRID
     * @param int $page
     * @param int $limit
     * @param array $queryParams
     * @param int|null $userId
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAllFormatToGrid(int $page, int $limit, array $queryParams, ?int $userId = null): array
    {
        $translator = $this->getTranslator();
        $gridService = $this->getGridService();
        $tagRender = $this->getTagRender();
        $locale = $this->getRequestStack()->getCurrentRequest()->getLocale();
        $csrfTokenManager = $this->getCsrfTokenManager();
        $csrfTokens = [
            TagCsrfToken::DISABLED->value => $csrfTokenManager->getToken(TagCsrfToken::DISABLED->value)->getValue(),
            TagCsrfToken::DELETE->value => $csrfTokenManager->getToken(TagCsrfToken::DELETE->value)->getValue(),
        ];

        $column = [
            $translator->trans('tag.grid.id', domain: 'tag'),
            $translator->trans('tag.grid.label', domain: 'tag'),
            $translator->trans('tag.grid.color', domain: 'tag'),
            $translator->trans('tag.grid.created_at', domain: 'tag'),
            $translator->trans('tag.grid.update_at', domain: 'tag'),
            $translator->trans('tag.grid.author', domain: 'tag'),
            GridService::KEY_ACTION,
        ];

        $dataPaginate = $this->getAllPaginate($page, $limit, $queryParams, $userId);

        $nb = $dataPaginate->count();
        $data = [];
        foreach ($dataPaginate as $element) {
            /* @var Tag $element */

            $action = $this->generateTabAction($element, $locale, $csrfTokens);

            $data[] = [
                $translator->trans('tag.grid.id', domain: 'tag') => $element->getId(),
                $translator->trans('tag.grid.label', domain: 'tag') => $tagRender->getHtml($element, $locale),
                $translator->trans('tag.grid.color', domain: 'tag') => $tagRender->getSafeColor($element->getColor()),
                $translator->trans('tag.grid.created_at', domain: 'tag') => $element
                    ->getCreatedAt()
                    ->format('d/m/y H:i'),
                $translator->trans('tag.grid.update_at', domain: 'tag') => $element->getUpdateAt()->format('d/m/y H:i'),
                $translator->trans('tag.grid.author', domain: 'tag') => htmlspecialchars(
                    (string) $element->getUser()?->getLogin(),
                    ENT_QUOTES,
                ),
                GridService::KEY_ACTION => $action,
                'isDisabled' => $element->isDisabled(),
            ];
        }

        $tabReturn = [
            GridService::KEY_NB => $nb,
            GridService::KEY_DATA => $data,
            GridService::KEY_COLUMN => $column,
            GridService::KEY_RAW_SQL => $gridService->getFormatedSQLQuery($dataPaginate),
            GridService::KEY_LIST_ORDER_FIELD => [
                'id' => $translator->trans('tag.grid.id', domain: 'tag'),
                'label' => $translator->trans('tag.grid.label', domain: 'tag'),
                'color' => $translator->trans('tag.grid.color', domain: 'tag'),
                'createdAt' => $translator->trans('tag.grid.created_at', domain: 'tag'),
                'updateAt' => $translator->trans('tag.grid.update_at', domain: 'tag'),
            ],
        ];
        return $gridService->addAllDataRequiredGrid($tabReturn);
    }

    /**
     * Génère le tableau d'actions de la grille des tags, selon les droits (ContentVoter)
     * @param Tag $tag
     * @param string $locale
     * @param array<string, string> $csrfTokens jetons CSRF indexés par identifiant TagCsrfToken
     * @return array[]
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function generateTabAction(Tag $tag, string $locale, array $csrfTokens): array
    {
        $translator = $this->getTranslator();
        $router = $this->getRouter();
        $security = $this->getSecurity();

        $label = $this->getEscapedLabel($tag, $locale);
        $actions = [];

        if ($security->isGranted(ContentVoter::DISABLE, $tag)) {
            $actionDisabled = [
                'label' => [
                    'M21 12c0 1.2-4.03 6-9 6s-9-4.8-9-6c0-1.2 4.03-6 9-6s9 4.8 9 6Z',
                    'M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
                ],
                'color' => 'primary',
                'type' => 'put',
                'url' => $router->generate('admin_tag_update_disabled', ['id' => $tag->getId()]),
                'ajax' => true,
                'csrf' => $csrfTokens[TagCsrfToken::DISABLED->value],
            ];
            if (!$tag->isDisabled()) {
                $actionDisabled['label'] = [
                    'M3.933 13.909A4.357 4.357 0 0 1 3 12c0-1 4-6 9-6m7.6 3.8A5.068 5.068 0 0 1 21 12c0 1-3 6-9 6-.314 0-.62-.014-.918-.04M5 19 19 5m-4 7a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
                ];
                $actionDisabled['confirm'] = true;
                $actionDisabled['msgConfirm'] = $translator->trans(
                    'tag.confirm.disabled.message',
                    ['label' => $label],
                    'tag',
                );
            }
            $actions[] = $actionDisabled;
        }

        if ($security->isGranted(ContentVoter::DELETE, $tag)) {
            $actions[] = [
                'label' => [
                    'M5 7h14m-9 3v8m4-8v8M10 3h4a1 1 0 0 1 1 1v3H9V4a1 1 0 0 1 1-1ZM6 7h12v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7Z',
                ],
                'type' => 'delete',
                'color' => 'danger',
                'url' => $router->generate('admin_tag_delete', ['id' => $tag->getId()]),
                'ajax' => true,
                'confirm' => true,
                'msgConfirm' => $translator->trans('tag.confirm.delete.message', ['label' => $label], 'tag'),
                'csrf' => $csrfTokens[TagCsrfToken::DELETE->value],
            ];
        }

        if ($security->isGranted(ContentVoter::EDIT, $tag)) {
            $actions[] = [
                'label' => [
                    'M10.779 17.779 4.36 19.918 6.5 13.5m4.279 4.279 8.364-8.643a3.027 3.027 0 0 0-2.14-5.165 3.03 3.03 0 0 0-2.14.886L6.5 13.5m4.279 4.279L6.499 13.5m2.14 2.14 6.213-6.504M12.75 7.04 17 11.28',
                ],
                'color' => 'primary',
                'type' => 'post',
                'id' => $tag->getId(),
                'url' => $router->generate('admin_tag_update', ['id' => $tag->getId()]),
                'ajax' => false,
            ];
        }

        return $actions;
    }

    /**
     * Retourne le libellé du tag dans la locale, échappé pour un affichage HTML
     * @param Tag $tag
     * @param string $locale
     * @return string
     */
    public function getEscapedLabel(Tag $tag, string $locale): string
    {
        return htmlspecialchars((string) $tag->getTagTranslationByLocale($locale)?->getLabel(), ENT_QUOTES);
    }

    /**
     * Remplit un tag depuis les données du formulaire, limité à la couleur, au statut et aux libellés
     * des locales supportées ; le propriétaire et les dates ne sont jamais repris
     * @param Tag $tag
     * @param array $data
     * @return Tag
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function populate(Tag $tag, array $data): Tag
    {
        if (array_key_exists('color', $data)) {
            $tag->setColor((string) $data['color']);
        }
        if (array_key_exists('disabled', $data)) {
            $tag->setDisabled((bool) $data['disabled']);
        }

        $labels = [];
        foreach ($data['tagTranslations'] ?? [] as $translation) {
            if (is_array($translation) && isset($translation['locale'])) {
                $labels[(string) $translation['locale']] = trim((string) ($translation['label'] ?? ''));
            }
        }

        foreach ($this->getLocales()['locales'] as $locale) {
            if (!array_key_exists($locale, $labels)) {
                continue;
            }
            $tagTranslation = $tag->getTagTranslationByLocale($locale);
            if ($tagTranslation === null || $tagTranslation->getLocale() !== $locale) {
                $tagTranslation = new TagTranslation();
                $tagTranslation->setLocale($locale);
                $tag->addTagTranslation($tagTranslation);
            }
            $tagTranslation->setLabel($labels[$locale]);
        }
        return $tag;
    }

    /**
     * Valide un tag (couleur, un libellé par locale supportée, doublons de libellé par locale)
     * Retourne la liste des messages d'erreur traduits, vide si le tag est valide
     * @param Tag $tag
     * @return string[]
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function validate(Tag $tag): array
    {
        $translator = $this->getTranslator();
        $errors = [];
        foreach ($this->getValidator()->validate($tag) as $violation) {
            $errors[] = $translator->trans($violation->getMessageTemplate(), domain: 'tag');
        }
        foreach ($this->getLocales()['locales'] as $locale) {
            if ($tag->getTagTranslations()->findFirst(fn($k, $t) => $t->getLocale() === $locale) === null) {
                $errors[] = $translator->trans('tag.error.label.blank', domain: 'tag');
            }
        }

        /** @var TagTranslationRepository $repo */
        $repo = $this->getRepository(TagTranslation::class);
        foreach ($tag->getTagTranslations() as $tagTranslation) {
            $existing = $repo->findOneBy(['locale' => $tagTranslation->getLocale(), 'label' => $tagTranslation->getLabel()]);
            if ($existing !== null && $existing->getTag()?->getId() !== $tag->getId()) {
                $errors[] = $translator->trans(
                    'tag.error.label.duplicate',
                    ['label' => htmlspecialchars((string) $tagTranslation->getLabel(), ENT_QUOTES)],
                    'tag',
                );
            }
        }
        return array_values(array_unique($errors));
    }

    /**
     * Retourne le nombre de pages auxquelles le tag est rattaché
     * @param Tag $tag
     * @return int
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function countPages(Tag $tag): int
    {
        /** @var TagRepository $repo */
        $repo = $this->getRepository(Tag::class);
        return $repo->countPages($tag);
    }

    /**
     * Recherche une liste de tag en fonction de la locale
     * @param string $locale
     * @param string $search
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function searchByLocale(string $locale, string $search): array
    {
        /** @var TagRepository $repo */
        $repo = $this->getRepository(Tag::class);
        $result = $repo->searchByName($locale, $search);

        $return = [];
        foreach ($result as $row) {
            $return[$row['id']] = [
                'id' => $row['id'],
                'color' => $row['color'],
                'label' => $row['label'],
            ];
        }
        return $return;
    }

    /**
     * Retourne un tag en fonction de son label et de la locale
     * Si le tag n'existe pas retourne null
     * @param string $locale
     * @param string $label
     * @return ?Tag
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function searchByNameByLocale(string $locale, string $label): ?Tag
    {
        $repo = $this->getRepository(TagTranslation::class);
        /** @var TagTranslation $tagTranslation */
        $tagTranslation = $repo->findOneBy(['label' => $label, 'locale' => $locale]);

        return $tagTranslation?->getTag();
    }

    /**
     * Retourne le tag correspondant au label dans la locale, et le crée s'il n'existe pas
     * Retourne null si le tag existe mais est désactivé
     * @param string $locale
     * @param string $label
     * @param User|null $user propriétaire du tag s'il est créé
     * @return Tag|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function newTagByNameAndLocale(string $locale, string $label, ?User $user = null): ?Tag
    {
        $tag = $this->searchByNameByLocale($locale, $label);
        if ($tag !== null) {
            return $tag->isDisabled() ? null : $tag;
        }

        $tag = new Tag();
        $tag->setUser($user);
        $tag->setColor('#' . str_pad(dechex(mt_rand(0, 0xffffff)), 6, '0', STR_PAD_LEFT));

        foreach ($this->getLocales()['locales'] as $loc) {
            $tagTranslation = new TagTranslation();
            $tagTranslation->setLocale($loc);
            $tagTranslation->setLabel($loc === $locale ? $label : $this->getFreeLabel($loc, $label));
            $tag->addTagTranslation($tagTranslation);
        }

        try {
            $this->getEntityManager()->persist($tag);
            $this->getEntityManager()->flush();
        } catch (UniqueConstraintViolationException $exception) {
            // Création concurrente du même libellé : l'EntityManager est fermé, on le réinitialise pour relire
            $this->getLogger()->warning($exception->getMessage());
            $this->getManagerRegistry()->resetManager();
            $tag = $this->searchByNameByLocale($locale, $label);
            if ($tag === null || $tag->isDisabled()) {
                return null;
            }
        }

        return $tag;
    }

    /**
     * Retourne un libellé libre dans la locale pour un tag créé depuis une autre langue :
     * "label (loc)", puis "label (loc 2)", "label (loc 3)"... si le libellé est déjà pris
     * @param string $locale
     * @param string $label
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getFreeLabel(string $locale, string $label): string
    {
        $i = 1;
        do {
            $suffix = ' (' . $locale . ($i > 1 ? ' ' . $i : '') . ')';
            $candidate = mb_substr($label, 0, 255 - mb_strlen($suffix)) . $suffix;
            $i++;
        } while ($this->searchByNameByLocale($locale, $candidate) !== null);

        return $candidate;
    }
}
