<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Class utilitaire pour positionner une entité en fonction d'une position
 */

namespace App\Utils\Global;

use DeepCopy\Exception\PropertyException;
use Doctrine\Common\Collections\Collection;
use Exception;
use Symfony\Component\Asset\Exception\AssetNotFoundException;

class OrderEntity
{
    /**
     * Clé after
     * @var string
     */
    const ACTION_AFTER = 'after';

    /**
     * Clé before
     * @var string
     */
    const ACTION_BEFORE = 'before';

    /**
     * Liste des éléments
     * @var Collection
     */
    private Collection $elements;

    /**
     * Nom de la propriété pour l'ordre
     * @var string
     */
    private string $propertyName;

    /**
     * @param Collection $elements
     * @param string $propertyName
     * @throws Exception
     */
    public function __construct(Collection $elements, string $propertyName = 'renderOrder')
    {
        $this->elements = $elements;
        $this->propertyName = $propertyName;
        $this->isPropertyExist();
    }

    /**
     * Positionne $idOrder à la place de $idNewOrder et réordonne la liste en fonction de $action
     * @param int $idNewOrder
     * @param int $idOrder
     * @param string $action
     * @return void
     * @throws Exception
     */
    public function orderByIdByAction(int $idNewOrder, int $idOrder, string $action = self::ACTION_BEFORE): void
    {
        $elementNewOrder = $this->getElementById($idNewOrder);

        $getPropertyName = 'get' . ucfirst($this->propertyName);
        $setPropertyName = 'set' . ucfirst($this->propertyName);
        $newOrder = $elementNewOrder->$getPropertyName();

        if ($action === self::ACTION_AFTER) {
            $newOrder = $newOrder + 1;
        }

        foreach ($this->elements as &$element) {
            if ($element->getId() === $idOrder) {
                $element->$setPropertyName($newOrder);
            } elseif ($element->$getPropertyName() >= $newOrder) {
                $element->$setPropertyName($element->$getPropertyName() + 1);
            }
        }
    }

    /**
     * Positionne $idOrder à la nouvelle $position et réordonne la liste
     * @param int $idOrder
     * @param int $position
     * @return void
     */
    public function orderByNewPosition(int $idOrder, int $position)
    {
        //TODO à écrire
    }

    /**
     * @return Collection
     */
    public function getCollection(): Collection
    {
        return $this->elements;
    }


    /**
     * Retourne un element de la liste par son id
     * @param int $id
     * @return object
     * @throws Exception
     */
    private function getElementById(int $id): object
    {
        $element = $this->elements->filter(function (object $element) use ($id) {
            return $element->getId() === $id;
        })->first();

        if (!$element) {
            throw new AssetNotFoundException('Item #' . $id . ' not found in list');
        } else {
            return $element;
        }
    }

    /**
     * Vérifie si la propriété existe dans l'objet
     * @return void
     * @throws Exception
     */
    private function isPropertyExist(): void
    {
        $element = $this->elements->first();
        if (!property_exists($element::class, $this->propertyName)) {
            throw new PropertyException($this->propertyName .
                '() property does not exist for object ' . $element::class);
        }
    }
}
