<?php

namespace App\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadataInfo as ClassMetadataInfo;

/**
 * Ajoute un prefix et un schema aux tables de la base de données
 */
#[AsDoctrineListener(event: Events::loadClassMetadata, priority: 500, connection: 'default')]
class DatabaseTablePrefixListener
{
    protected string $prefix = '';
    protected string $schema = '';

    /**
     * @param string $prefix
     * @param string $schema
     */
    public function __construct(string $prefix, string $schema)
    {
        $this->prefix = $prefix;
        $this->schema = $schema;

        if ($this->schema !== '') {
            $this->schema .= '.';
        }

        if ($this->prefix !== '') {
            $this->prefix .= '_';
        }
    }

    /**
     * @param LoadClassMetadataEventArgs $eventArgs
     * @return void
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        $classMetadata = $eventArgs->getClassMetadata();

        if (
            !$classMetadata->isInheritanceTypeSingleTable() ||
            $classMetadata->getName() === $classMetadata->rootEntityName
        ) {
            $classMetadata->setPrimaryTable([
                'name' => $this->schema . $this->prefix . $classMetadata->getTableName(),
            ]);
        }

        foreach ($classMetadata->getAssociationMappings() as $fieldName => $mapping) {
            $mapping = $mapping->toArray();

            if ($mapping['type'] === 8 && $mapping['isOwningSide']) {
                $mappedTableName = $mapping['joinTable']['name'];
                $classMetadata->associationMappings[$fieldName]['joinTable']['name'] =
                    $this->schema . $this->prefix . $mappedTableName;
            }
        }
    }
}
