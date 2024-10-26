<?php
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Service global pour les API
 */
namespace App\Service\Api;

use App\Utils\System\Options\OptionSystemKey;
use Doctrine\DBAL\Exception;
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
     * Retourne une entité en fonction de son champ et valeur associé
     * @param string $entity
     * @param array $criteria
     * @return object|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function findOneByCriteria(string $entity, array $criteria): ?object
    {
        $repo = $this->getRepository($entity);
        return $repo->findOneBy($criteria);
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

    /**
     * Retourne les options systems importantes pour les APIs
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getOptionSystemApi(): array
    {
        $optionSystemService = $this->getOptionSystemService();
        $optionAdresse = $optionSystemService->getByKey(OptionSystemKey::OS_ADRESSE_SITE);

        return [
            OptionSystemKey::OS_ADRESSE_SITE => $optionAdresse->getValue(),
        ];
    }

    /**
     * Permet de sauvegarder une entité
     * @param mixed $entity
     * @param bool $flush
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function save(mixed $entity, bool $flush = true): void
    {
        try {
            $repo = $this->getRepository($entity::class);
            $repo->save($entity, $flush);
        } catch (Exception $exception) {
            $this->getLogger()->error($exception->getMessage());
        }
    }
}
