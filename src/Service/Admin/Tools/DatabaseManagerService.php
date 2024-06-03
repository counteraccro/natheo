<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Service pour DatabaseManager
 */
namespace App\Service\Admin\Tools;

use App\Service\AppService;
use App\Utils\Global\DataBase;
use App\Utils\Tools\DatabaseManager\Query\RawPostgresQuery;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

class DatabaseManagerService extends AppService
{

    /**
     * Constructeur
     * @param ContainerInterface $handlers
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(#[AutowireLocator([
        'entityManager' => EntityManagerInterface::class,
        'containerBag' => ContainerBagInterface::class,
        'translator' => TranslatorInterface::class,
        'security' => Security::class,
        'requestStack' => RequestStack::class,
        'database' => DataBase::class
    ])] private readonly ContainerInterface $handlers)
    {
        parent::__construct($handlers);
    }

    /**
     * Retourne les informations de la base de données
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAllInformationSchemaDatabase(): array
    {
        /** @var DataBase $database */
        $database = $this->handlers->get('database');
        $query = RawPostgresQuery::getQueryAllInformationSchema('natheo');
        $result = $database->executeRawQuery($query);
        return $result;
    }
}
