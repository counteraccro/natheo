<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Lit les surcharges d'opérations d'API des fichiers overwrite.yaml et les range dans le paramètre app.overwrite_api
 */

namespace App\DependencyInjection\Compiler;

use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\State\ProviderInterface;
use Symfony\Component\Config\Resource\FileExistenceResource;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Yaml;

final class OverwriteConfigPass implements CompilerPassInterface
{
    /**
     * Liste des fichiers de surcharge à lire
     * @var string
     */
    public const string FILES_PARAMETER = 'app.overwrite_files';

    /**
     * Surcharges retenues : [nom de l'opération => [provider|processor => classe]]
     * @var string
     */
    public const string API_PARAMETER = 'app.overwrite_api';

    /**
     * Clé de la classe de remplacement d'une lecture
     * @var string
     */
    public const string PROVIDER = 'provider';

    /**
     * Clé de la classe de remplacement d'une écriture
     * @var string
     */
    public const string PROCESSOR = 'processor';

    /**
     * @param ContainerBuilder $container
     * @return void
     */
    public function process(ContainerBuilder $container): void
    {
        $files = $container->hasParameter(self::FILES_PARAMETER)
            ? (array) $container->getParameterBag()->resolveValue($container->getParameter(self::FILES_PARAMETER))
            : [];

        $entries = [];
        foreach ($files as $file) {
            $container->addResource(new FileExistenceResource($file));
            if (!is_file($file)) {
                continue;
            }
            $container->addResource(new FileResource($file));
            $entries[$file] = Yaml::parseFile($file)['overwrite']['api'] ?? [];
        }

        $overwrites = self::parse($entries);
        foreach ($overwrites as $operation => $overwrite) {
            $class = reset($overwrite);
            if (!$container->has($class)) {
                throw new \LogicException(
                    sprintf('La classe « %s » de l\'opération « %s » n\'est pas un service', $class, $operation),
                );
            }
        }
        $container->setParameter(self::API_PARAMETER, $overwrites);
    }

    /**
     * Contrôle les entrées de chaque fichier et les range par nom d'opération
     * @param array<string, mixed> $entries entrées de la clé overwrite.api, par fichier
     * @return array<string, array<string, class-string>>
     * @throws \LogicException si une entrée est mal formée, en double ou vise une classe invalide
     */
    public static function parse(array $entries): array
    {
        $overwrites = [];
        foreach ($entries as $file => $list) {
            if (!is_array($list) || !array_is_list($list)) {
                throw new \LogicException(sprintf('overwrite.api doit être une liste dans %s', $file));
            }
            foreach ($list as $entry) {
                $operation = is_array($entry) ? $entry['operation'] ?? null : null;
                if (!is_string($operation) || $operation === '') {
                    throw new \LogicException(sprintf('Entrée de overwrite.api sans « operation » dans %s', $file));
                }
                if (isset($overwrites[$operation])) {
                    throw new \LogicException(sprintf('Opération « %s » surchargée deux fois', $operation));
                }
                $overwrites[$operation] = self::parseEntry($operation, $entry);
            }
        }
        return $overwrites;
    }

    /**
     * @param string $operation
     * @param array<string, mixed> $entry
     * @return array<string, class-string>
     */
    private static function parseEntry(string $operation, array $entry): array
    {
        $keys = array_values(array_intersect([self::PROVIDER, self::PROCESSOR], array_keys($entry)));
        if (count($keys) !== 1 || count($entry) !== 2) {
            throw new \LogicException(
                sprintf('L\'opération « %s » attend soit « provider », soit « processor »', $operation),
            );
        }

        $key = $keys[0];
        $class = $entry[$key];
        $interface = $key === self::PROVIDER ? ProviderInterface::class : ProcessorInterface::class;
        if (!is_string($class) || !class_exists($class)) {
            throw new \LogicException(
                sprintf(
                    'Classe « %s » introuvable pour l\'opération « %s »',
                    is_string($class) ? $class : get_debug_type($class),
                    $operation,
                ),
            );
        }
        if (!is_subclass_of($class, $interface)) {
            throw new \LogicException(
                sprintf('La classe « %s » de l\'opération « %s » doit implémenter %s', $class, $operation, $interface),
            );
        }
        return [$key => $class];
    }
}
