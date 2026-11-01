<?php

declare(strict_types=1);
/**
 * Test du trait OrderedQueryTrait
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Tests\Repository\Trait;

use App\Repository\Trait\OrderedQueryTrait;
use PHPUnit\Framework\TestCase;

class OrderedQueryTraitTest extends TestCase
{
    /**
     * Test méthode toSortDirection()
     * @return void
     */
    public function testToSortDirection(): void
    {
        $trait = new class {
            use OrderedQueryTrait;
        };

        $this->assertSame(\SortDirection::Ascending, $trait->toSortDirection('asc'));
        $this->assertSame(\SortDirection::Ascending, $trait->toSortDirection('ASC'));
        $this->assertSame(\SortDirection::Descending, $trait->toSortDirection('desc'));
        $this->assertSame(\SortDirection::Descending, $trait->toSortDirection(null));
        $this->assertSame(\SortDirection::Descending, $trait->toSortDirection('DESC; DROP TABLE'));
        $this->assertSame(\SortDirection::Ascending, $trait->toSortDirection('', \SortDirection::Ascending));
    }
}
