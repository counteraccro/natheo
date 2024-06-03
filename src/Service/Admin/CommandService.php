<?php
/**
 * Service qui permet d'exécuter une commande console
 * @author Gourdon Aymeric
 * @version 1.1
 */

namespace App\Service\Admin;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class CommandService extends AppAdminService
{

    /**
     * Permet de recharger le cache applicatif de Symfony
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function reloadCache(): void
    {
        $kernel = $this->getKernel();

        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'cache:clear',
        ]);

        $output = new NullOutput();
        $application->run($input, $output);
    }
}
