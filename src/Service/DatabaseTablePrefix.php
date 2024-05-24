<?php

namespace App\Service;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadataInfo as ClassMetadataInfo;

/*
 * Setup information directly from Doctrine
 * https://www.doctrine-project.org/projects/doctrine-orm/en/2.16/cookbook/sql-table-prefixes.html#telling-the-entitymanager-about-our-listener
 * https://symfony.com/doc/current/doctrine/events.html#doctrine-entity-listeners
 */
#[AsDoctrineListener(event: Events::loadClassMetadata, priority: 500, connection: 'default')]
class DatabaseTablePrefix
{
    protected string $prefix = '';
    protected string $schema = '';

    public function __construct(string $prefix, string $schema)
    {
        $this->prefix = $prefix;
        $this->schema = $schema;
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        $classMetadata = $eventArgs->getClassMetadata();

        if (!$classMetadata->isInheritanceTypeSingleTable() || $classMetadata->getName() === $classMetadata->rootEntityName) {

            $classMetadata->setPrimaryTable([
                'name' => $this->schema . $this->prefix . $classMetadata->getTableName()
            ]);
        }

        foreach ($classMetadata->getAssociationMappings() as $fieldName => $mapping) {
            if ($mapping['type'] === ClassMetadataInfo::MANY_TO_MANY && $mapping['isOwningSide']) {
                $mappedTableName = $mapping['joinTable']['name'];
                $classMetadata->associationMappings[$fieldName]['joinTable']['name'] = $this->schema . $this->prefix . $mappedTableName;
            }
        }
    }
}
