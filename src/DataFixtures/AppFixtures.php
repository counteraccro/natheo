<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Class globale pour les fixtures
 */

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Yaml\Yaml;

class AppFixtures extends Fixture
{
    /**
     * Chemin d'accès aux données de fixtures
     * @var string
     */
    protected string $pathDataFixtures = '';

    /**
     * Lié à toutes les données user
     * @var string
     */
    protected const GROUP_USER = 'user';

    /**
     * Lié à toutes les données de membres
     * @var string
     */
    protected const GROUP_REGISTERED = 'registered';

    /**
     * Lié à tous éléments en rapports au devtools
     * @var string
     */
    protected const GROUP_DEVTOOLS = 'devTools';

    /**
     * Lié à tous les éléments de sidebar
     * @var string
     */
    protected const GROUP_SIDEBAR_ELEMENT = 'sidebarElement';

    /**
     * Lié à tous les éléments de system
     * @var string
     */
    protected const GROUP_SYSTEM = 'system';

    /**
     * Lié à tous les éléments des options système
     * @var string
     */
    protected const GROUP_OPTION_SYSTEM = 'option_system';

    /**
     * Lié à tous les éléments email
     * @var string
     */
    protected const GROUP_MAIL = 'mail';

    /**
     * Lié à tous les elements content
     * @var string
     */
    protected const GROUP_CONTENT = 'content';

    /**
     * Lié à tous les elements tools
     * @var string
     */
    protected const GROUP_TOOLS = 'tools';

    /**
     * Lié à tous les elements tags
     * @var string
     */
    protected const GROUP_TAG = 'tag';

    /**
     * Lié à tous les éléments média
     * @var string
     */
    protected const GROUP_MEDIA = 'media';

    /**
     * Lié à tous les éléments page
     * @var string
     */
    protected const GROUP_PAGE = 'page';

    /**
     * Lié à tous les éléments faq
     * @var string
     */
    protected const GROUP_FAQ = 'faq';

    /**
     * Lié à tous les éléments sql manager
     * @var string
     */
    protected const GROUP_SQL_MANAGER = 'sql_manager';

    /**
     * Lié à tous les éléments menu
     * @var string
     */
    protected const GROUP_MENU = 'menu';

    /**
     * Lié à tous les éléments api_token
     * @var string
     */
    protected const GROUP_API_TOKEN = 'api_token';

    /**
     * Lié à tous les élements comment
     * @var string
     */
    protected const GROUP_COMMENT = 'comment';

    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * @param ContainerInterface $handlers
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        #[
            AutowireLocator([
                'container' => ContainerBagInterface::class,
            ]),
        ]
        private readonly ContainerInterface $handlers,
    ) {
        $this->container = $this->handlers->get('container');

        $kernel = $this->container->get('kernel.project_dir');
        $this->pathDataFixtures =
            $kernel .
            DIRECTORY_SEPARATOR .
            'src' .
            DIRECTORY_SEPARATOR .
            'DataFixtures' .
            DIRECTORY_SEPARATOR .
            'data' .
            DIRECTORY_SEPARATOR;
    }

    /**
     * Set une valeur $value pour l'objet $entity en fonction de la clé $key
     * @param string $key
     * @param mixed $value
     * @param mixed $entity
     * @return void
     */
    protected function setData(string $key, mixed $value, mixed $entity): void
    {
        $func = 'set' . ucfirst($key);
        $entity->$func($value);
    }

    /**
     * Peuple une entité avec les $data et renvoi l'entité
     * @param array $data
     * @param mixed $entity
     * @return mixed
     */
    public function populateEntity(array $data, mixed $entity): mixed
    {
        foreach ($data as $key => $value) {
            $this->setData($key, $value, $entity);
        }
        return $entity;
    }

    /**
     * Retourne la valeur du cas $name de l'enum $enum
     * @param class-string<\BackedEnum> $enum
     * @param string $name
     * @return int|string
     */
    protected function enumValue(string $enum, string $name): int|string
    {
        return $this->enumCase($enum, $name)->value;
    }

    /**
     * Retourne le cas $name de l'enum $enum
     * @template T of \BackedEnum
     * @param class-string<T> $enum
     * @param string $name
     * @return T
     */
    protected function enumCase(string $enum, string $name): \BackedEnum
    {
        $case = $enum . '::' . $name;
        if (!defined($case)) {
            throw new \InvalidArgumentException(sprintf('Cas « %s » inconnu dans %s', $name, $enum));
        }
        return constant($case);
    }

    /**
     * Lit les fichiers YAML du dossier $folder et de ses sous-dossiers, par ordre de chemin.
     * Les fichiers dont le nom commence par « _ » sont ignorés
     * @param string $folder
     * @return array<string, array{data: array, dir: string}> référence => données et dossier du fichier
     */
    protected function loadYamlFolder(string $folder): array
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($folder, \FilesystemIterator::SKIP_DOTS),
        );
        foreach ($iterator as $file) {
            /** @var \SplFileInfo $file */
            if ($file->getExtension() === 'yaml' && !str_starts_with($file->getFilename(), '_')) {
                $files[] = $file->getPathname();
            }
        }
        sort($files);

        $return = [];
        foreach ($files as $file) {
            foreach (Yaml::parseFile($file) ?? [] as $ref => $data) {
                if (isset($return[$ref])) {
                    throw new \LogicException(sprintf('Référence « %s » déclarée deux fois (%s)', $ref, $file));
                }
                $return[$ref] = ['data' => $data, 'dir' => dirname($file)];
            }
        }
        return $return;
    }

    /**
     * Retourne les langues supportées
     * @return string[]
     */
    protected function getLocales(): array
    {
        return explode('|', $this->container->get('app.supported_locales'));
    }

    public function load(ObjectManager $manager): void
    {
        $manager->flush();
    }
}
