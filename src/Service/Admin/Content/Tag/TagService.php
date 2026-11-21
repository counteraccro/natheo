<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Service gérant les tags de l'application
 */

namespace App\Service\Admin\Content\Tag;

use App\Entity\Admin\Content\Tag\Tag;
use App\Entity\Admin\System\User;
use App\Repository\Admin\Content\Tag\TagRepository;
use App\Service\Admin\AppAdminService;
use App\Service\Api\Admin\Content\ApiAdminTagService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;

class TagService extends AppAdminService
{
    public function __construct(
        #[AutowireLocator(self::HANDLERS)] ContainerInterface $handlers,
        private readonly ApiAdminTagService $apiAdminTagService,
    ) {
        parent::__construct($handlers);
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
     * Retourne le tag dont le libellé de la locale vaut $label sans tenir compte de la casse, et le crée s'il
     * n'existe pas ; retourne null si le tag existe mais est désactivé
     * @param string $locale
     * @param string $label
     * @param User|null $user propriétaire du tag s'il est créé
     * @return Tag|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function newTagByNameAndLocale(string $locale, string $label, ?User $user = null): ?Tag
    {
        $tag = $this->apiAdminTagService->findByLabel($locale, $label);
        if ($tag === null) {
            $tag = $this->apiAdminTagService->create($user, [$locale => $label]);
            if ($tag->getId() === null) {
                // Création concurrente du même libellé : l'EntityManager est fermé, on le réinitialise pour relire
                $this->getManagerRegistry()->resetManager();
                $tag = $this->apiAdminTagService->findByLabel($locale, $label);
            }
        }

        return $tag === null || $tag->isDisabled() ? null : $tag;
    }
}
