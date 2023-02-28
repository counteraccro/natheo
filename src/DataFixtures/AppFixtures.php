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

    public function __construct(ContainerBagInterface $params)
    {
        $kernel = $params->get('kernel.project_dir');
        $this->pathDataFixtures = $kernel . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'DataFixtures' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR;
    }


    public function load(ObjectManager $manager): void
    {


        // $product = new Product();
        // $manager->persist($product);

        $manager->flush();
    }
}
