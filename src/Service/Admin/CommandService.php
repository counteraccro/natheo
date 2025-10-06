<?php
/**
 * Service qui permet d'exécuter une commande console
 * @author Gourdon Aymeric
 * @version 1.1
 */

namespace App\Service\Admin;

use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;

class CommandService extends AppAdminService
{
    /**
     * Permet de recharger le cache applicatif de Symfony
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    public function reloadCache(): void
    {
        $application = $this->getApplication();

        $input = new ArrayInput([
            'command' => 'cache:clear',
        ]);

        $output = new NullOutput();
        $application->run($input, $output);
    }

    /**
     * Créer le schema SQL
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    public function createSchema(): void
    {
        $application = $this->getApplication();

        $input = new ArrayInput([
            'command' => 'doctrine:schema:create',
        ]);

        $output = new NullOutput();
        $application->run($input, $output);
    }

    /**
     * drop le schema SQL
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    public function dropSchema(): void
    {
        $application = $this->getApplication();

        $input = new ArrayInput([
            'command' => 'doctrine:schema:drop',
            '--force' => true,
        ]);

        $output = new NullOutput();
        $application->run($input, $output);
    }

    /**
     * drop de la base de données
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    public function dropDatabase(): void
    {
        $application = $this->getApplication();

        $input = new ArrayInput([
            'command' => 'doctrine:database:drop',
            '--force' => true,
        ]);

        $output = new NullOutput();
        $application->run($input, $output);
    }

    /**
     * Créer la base de données
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    public function createDatabase(): void
    {
        $application = $this->getApplication();

        $input = new ArrayInput([
            'command' => 'doctrine:database:create',
        ]);

        $output = new NullOutput();
        $application->run($input, $output);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    public function loadFixtures(): void
    {
        $application = $this->getApplication();

        $input = new ArrayInput([
            'command' => 'doctrine:fixtures:load',
            '--append' => true,
        ]);

        $output = new BufferedOutput();
        $application->doRun($input, $output);
    }

    /**
     * Retourne un objet Application
     * @return Application
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getApplication(): Application
    {
        if (!defined('STDIN')) {
            define('STDIN', fopen('php://stdin', 'r'));
        }
        $kernel = $this->getKernel();

        $application = new Application($kernel);
        $application->setAutoExit(false);

        return $application;
    }
}
