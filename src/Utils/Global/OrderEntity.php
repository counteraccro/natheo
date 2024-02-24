<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Class utilitaire pour positionner une entité en fonction d'une position
 */

namespace App\Utils\Global;

use Doctrine\Common\Collections\Collection;

class OrderEntity
{
    const ACTION_AFTER = 'after';

    const ACTION_BEFORE = 'before';

    private Collection $elements;

    private string $propertyName;

    public function __construct(Collection $elements, string $propertyName = 'renderOrder')
    {
        $this->elements = $elements;
        $this->propertyName = $propertyName;
    }

    /**
     * Positionne $idOrder à la place de $idNewOrder et réordonne la liste en fonction de $action
     * @param int $idNewOrder
     * @param int $idOrder
     * @param string $action
     * @return void
     */
    public function orderByIdByAction(int $idNewOrder, int $idOrder, string $action = self::ACTION_BEFORE): void
    {
        $elementNewOrder = $this->getElementById($idNewOrder);
        $elementOrder = $this->getElementById($idOrder);

        $getPropertyName = 'get' . ucfirst($this->propertyName);
        $newOrder = $elementNewOrder->$getPropertyName();

        if ($action === self::ACTION_AFTER) {
            $newOrder = $newOrder + 1;
        }

        echo 'New order ' . $newOrder . '<br />';

    }

    /**
     * Positionne $idOrder à la nouvelle $position et réordonne la liste
     * @param int $idOrder
     * @param int $position
     * @return void
     */
    public function orderByNewPosition(int $idOrder, int $position)
    {

    }

    /**
     * @return Collection
     */
    public function getCollection(): Collection
    {
        return $this->elements;
    }

    private function getElementById(int $id)
    {
        return $this->elements->filter(function (object $element) use ($id) {
            return $element->getId() === $id;
        })->first();
    }
}
