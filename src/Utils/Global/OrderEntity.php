<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Class utilitaire pour positionner une entité en fonction d'une position
 */
namespace App\Utils\Global;

class OrderEntity
{
    const ACTION_AFTER = 'after';

    const ACTION_BEFORE = 'before';

    private array $elements;

    private string $propertyName;

    public function __construct(array $elements, string $propertyName = 'renderOrder')
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
    public function orderByIdByAction(int $idNewOrder, int $idOrder, string $action)
    {

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
}
