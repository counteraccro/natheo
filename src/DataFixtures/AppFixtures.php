<?php
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
    public function __construct(#[AutowireLocator([
        'container' => ContainerBagInterface::class
    ])] private readonly ContainerInterface $handlers)
    {
        $this->container = $this->handlers->get('container');

        $kernel = $this->container->get('kernel.project_dir');
        $this->pathDataFixtures = $kernel . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR .
            'DataFixtures' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR;
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


    public function load(ObjectManager $manager): void
    {
        $manager->flush();
    }
}
