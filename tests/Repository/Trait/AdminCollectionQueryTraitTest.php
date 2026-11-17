<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test du trait qui applique une AdminCollectionQuery, sur la table des tokens d'API
 */

namespace App\Tests\Repository\Trait;

use App\Entity\Admin\System\ApiToken;
use App\Enum\Api\Admin\ActivationStatus;
use App\Repository\Trait\AdminCollectionQueryTrait;
use App\Service\Api\Admin\AdminCollectionQuery;
use App\Tests\AppWebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;

class AdminCollectionQueryTraitTest extends AppWebTestCase
{
    /**
     * Recherche insensible à la casse sur les champs déclarés, statut, tri, page ; champ inconnu ignoré
     * @return void
     */
    public function testPaginateAdminCollection(): void
    {
        $this->em->createQuery('DELETE FROM ' . ApiToken::class . ' a')->execute();
        $alpha = $this->createApiToken(['name' => 'Alpha', 'comment' => 'un 100% test', 'disabled' => false]);
        $beta = $this->createApiToken(['name' => 'Beta', 'comment' => 'autre', 'disabled' => true]);
        $gamma = $this->createApiToken(['name' => 'Gamma', 'comment' => 'alpha', 'disabled' => false]);

        $this->assertSame(
            [$alpha, $gamma],
            $this->paginate(
                new AdminCollectionQuery(
                    search: 'ALPHA',
                    searchFields: ['name', 'comment', 'inconnu'],
                    orderField: 'name',
                    orderDirection: \SortDirection::Ascending,
                ),
            ),
        );
        $this->assertSame([$alpha], $this->paginate(new AdminCollectionQuery(search: 'ALPHA', searchFields: ['name'])));
        $this->assertSame(
            [$alpha],
            $this->paginate(new AdminCollectionQuery(search: '100%', searchFields: ['comment'])),
        );
        $this->assertSame([], $this->paginate(new AdminCollectionQuery(search: '_', searchFields: ['name'])));
        $this->assertSame([$beta], $this->paginate(new AdminCollectionQuery(status: ActivationStatus::DISABLED)));
        $this->assertSame([$gamma, $beta, $alpha], $this->paginate(new AdminCollectionQuery(orderField: 'inconnu')));
        $this->assertSame(
            [$beta],
            $this->paginate(
                new AdminCollectionQuery(
                    page: 2,
                    limit: 1,
                    orderField: 'name',
                    orderDirection: \SortDirection::Ascending,
                ),
            ),
        );
    }

    /**
     * @param AdminCollectionQuery $query
     * @return ApiToken[]
     */
    private function paginate(AdminCollectionQuery $query): array
    {
        $repository = new class ($this->em) {
            use AdminCollectionQueryTrait;

            public function __construct(private readonly EntityManagerInterface $em) {}

            public function find(AdminCollectionQuery $query): Paginator
            {
                return $this->paginateAdminCollection(
                    $this->em->createQueryBuilder()->select('a')->from(ApiToken::class, 'a'),
                    $query,
                    ['name' => 'a.name', 'comment' => 'a.comment'],
                    'a.disabled',
                    'a.id',
                );
            }
        };
        return iterator_to_array($repository->find($query), false);
    }
}
