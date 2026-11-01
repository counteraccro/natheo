<?php

declare(strict_types=1);
/**
 * Test du PopulateTrait
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Tests\Entity\Trait;

use App\Entity\Admin\Content\Menu\Menu;
use App\Entity\Admin\System\User;
use App\Entity\Trait\PopulateTrait;
use PHPUnit\Framework\TestCase;

class PopulateTraitTest extends TestCase
{
    /**
     * Seuls les champs de la liste blanche sont appliqués, les autres clés sont ignorées
     * @return void
     */
    public function testMergeDataAppliesOnlyAllowedFields(): void
    {
        $owner = new User();
        $menu = new Menu();
        $menu->setName('origine')->setUser($owner)->setDisabled(false);

        $populate = new class {
            use PopulateTrait;

            public function merge(object $object, array $data, array $allowed): object
            {
                return $this->mergeData($object, $data, $allowed);
            }
        };

        /** @var Menu $result */
        $result = $populate->merge(
            $menu,
            [
                'name' => 'nouveau nom',
                'disabled' => true,
                'user' => new User(),
                'unknownKey' => 'valeur',
            ],
            ['name'],
        );

        $this->assertSame($menu, $result);
        $this->assertSame('nouveau nom', $menu->getName());
        $this->assertFalse($menu->isDisabled(), 'Champ hors liste blanche non appliqué');
        $this->assertSame($owner, $menu->getUser(), 'Propriétaire non modifiable');
    }

    /**
     * Un champ de la liste blanche absent des données n'est pas modifié
     * @return void
     */
    public function testMergeDataIgnoresMissingAllowedField(): void
    {
        $menu = new Menu();
        $menu->setName('origine');

        $populate = new class {
            use PopulateTrait;

            public function merge(object $object, array $data, array $allowed): object
            {
                return $this->mergeData($object, $data, $allowed);
            }
        };
        $populate->merge($menu, [], ['name', 'disabled']);

        $this->assertSame('origine', $menu->getName());
    }
}
