<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Trait principal Fixture
 */
namespace App\Tests\Helper\Fixtures;

use Doctrine\ORM\EntityManagerInterface;

trait FixturesTrait
{
    use UserFixturesTrait;

    /**
     * @var EntityManagerInterface
     */
    protected readonly EntityManagerInterface $em;

    /**
     * Initialise une entité et set ses valeurs en fonction de $data
     * @param string $entityName
     * @param array $data
     * @param object|null $entityToUpdate
     * @return object
     */
    public function initEntity(string $entityName, array $data, object $entityToUpdate = null): object
    {
        $object = new $entityName();
        if ($entityToUpdate !== null) {
            $object = $entityToUpdate;
        }

        foreach ($data as $property => $value) {
            if (!property_exists($object, $property)) {
                echo "error " . $property . "\n";
                continue;
            }

            $setterAddName = 'add' . ucfirst(rtrim($property, 's'));
            $setterName = 'set' . ucfirst($property);
            $setterIsName = 'is' . ucfirst($property);

            // add
            if(method_exists($object, $setterAddName) && !method_exists($object, $setterName)) {
                $value = is_array($value) ? $value : [$value];
                foreach ($value as $item) {
                    $object->$setterAddName($item);
                }
                continue;
            }

            // is
            if (method_exists($object, $setterIsName)) {
                $object->$setterIsName($value);
                continue;
            }

            // set
            if (method_exists($object, $setterName)) {
                $object->$setterName($value);
            }
        }
        return $object;
    }

    /**
     * @param object $object
     * @return void
     */
    public function persistAndFlush(object $object): void
    {
        $this->em->persist($object);
        $this->em->flush();
    }
}