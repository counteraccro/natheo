<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Class globale pour les fixtures
 */
namespace App\DataFixtures;

use App\Kernel;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class AppFixtures extends Fixture
{
    /**
     * Chemin d'accès aux données de fixtures
     * @var string
     */
    protected string $pathDataFixtures = './data/';

    /**
     * Lié à toute les données user
     * @var string
     */
    protected const GROUP_USER = 'user';

    /**
     * Lié à toutes les données de membres
     */
    protected const GROUP_REGISTERED = 'registered';

    /**
     * Lié à tous éléments en rapports au devtools
     */
    protected const GROUP_DEVTOOLS = 'devTools';

    /**
     * Lié à tous les éléments de sidebar
     */
    protected const GROUP_SIDEBAR_ELEMENT = 'sidebarElement';

    /**
     * Lié à tous les éléments de system
     */
    protected const GROUP_SYSTEM = 'system';

    /**
     * Lié à tous les éléments des options système
     */
    protected const GROUP_OPTION_SYSTEM = 'option_system';

    public function __construct(ContainerBagInterface $params)
    {
        $kernel = $params->get('kernel.project_dir');
        $this->pathDataFixtures = $kernel . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR .
            'DataFixtures' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR;
    }


    public function load(ObjectManager $manager): void
    {
        $manager->flush();
    }
}
