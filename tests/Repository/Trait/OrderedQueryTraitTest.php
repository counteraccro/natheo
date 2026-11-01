<?php

declare(strict_types=1);
/**
 * Test du trait OrderedQueryTrait
 * @author Gourdon Aymeric
 * @version 1.1
 */

namespace App\Tests\Repository\Trait;

use App\Entity\Admin\Content\Tag\Tag;
use App\Repository\Trait\OrderedQueryTrait;
use App\Tests\AppWebTestCase;
use Doctrine\ORM\EntityManagerInterface;

class OrderedQueryTraitTest extends AppWebTestCase
{
    /**
     * Test méthode applyOrdering() : champ et direction hors liste blanche ignorés
     * @return void
     */
    public function testApplyOrdering(): void
    {
        $repository = $this->getRepository();
        $em = $this->container->get(EntityManagerInterface::class);

        $dql = fn(array $params) => $repository
            ->applyOrdering($em->createQueryBuilder()->select('tag')->from(Tag::class, 'tag'), Tag::class, $params)
            ->getDQL();

        $this->assertStringEndsWith('ORDER BY tag.id DESC', $dql([]));
        $this->assertStringEndsWith('ORDER BY tag.color ASC', $dql(['orderField' => 'color', 'order' => 'asc']));
        $this->assertStringEndsWith(
            'ORDER BY tag.id DESC',
            $dql(['orderField' => 'id; DROP TABLE tag', 'order' => 'DESC; DROP TABLE tag']),
        );
        $this->assertStringEndsWith('ORDER BY tag.updateAt DESC', $dql(['orderField' => 'updateAt', 'order' => 'x']));
    }

    /**
     * Test méthode toSortDirection()
     * @return void
     */
    public function testToSortDirection(): void
    {
        $repository = $this->getRepository();

        $this->assertSame(\SortDirection::Ascending, $repository->callToSortDirection('asc'));
        $this->assertSame(\SortDirection::Ascending, $repository->callToSortDirection('ASC'));
        $this->assertSame(\SortDirection::Descending, $repository->callToSortDirection('desc'));
        $this->assertSame(\SortDirection::Descending, $repository->callToSortDirection(null));
        $this->assertSame(\SortDirection::Descending, $repository->callToSortDirection('DESC; DROP TABLE'));
        $this->assertSame(\SortDirection::Ascending, $repository->callToSortDirection('', \SortDirection::Ascending));
    }

    /**
     * Classe utilisant le trait, avec un accès public à toSortDirection() pour le test
     * @return object
     */
    private function getRepository(): object
    {
        return new class {
            use OrderedQueryTrait;

            public function callToSortDirection(
                ?string $order,
                \SortDirection $default = \SortDirection::Descending,
            ): \SortDirection {
                return $this->toSortDirection($order, $default);
            }
        };
    }
}
