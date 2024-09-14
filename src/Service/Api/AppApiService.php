<?php
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Service global pour les API
 */
namespace App\Service\Api;

use Doctrine\ORM\EntityRepository;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class AppApiService extends AppApiHandlerService
{

    /**
     * Retourne le repository en fonction de l'entité
     * @param string $entity
     * @return EntityRepository
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getRepository(string $entity): EntityRepository
    {
        return $this->getEntityManager()->getRepository($entity);
    }

    /**
     * Retourne une entité en fonction de son id
     * @param string $entity
     * @param int $id
     * @return object|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function findOneById(string $entity, int $id): ?object
    {
        return $this->findOneBy($entity, 'id', $id);
    }

    /**
     * Retourne une entité en fonction de son champ et valeur associé
     * @param string $entity
     * @param string $field
     * @param mixed $value
     * @return object|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function findOneBy(string $entity, string $field, mixed $value): ?object
    {
        $repo = $this->getRepository($entity);
        return $repo->findOneBy([$field => $value]);
    }

    /**
     * Retourne une liste d'entité en fonction de critères
     * @param string $entity
     * @param array $criteria
     * @param array $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function findBy(
        string $entity,
        array  $criteria = [],
        array  $orderBy = [],
        int    $limit = null,
        int    $offset = null
    ): array
    {
        $repo = $this->getRepository($entity);
        return $repo->findBy($criteria, $orderBy, $limit, $offset);
    }
}
