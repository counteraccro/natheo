<?php
/**
 * Service qui permet d'executer une commande console
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Service\Admin;

use App\Service\AppService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CommandService extends AppAdminService
{
    /**
     * @var KernelInterface
     */
    private KernelInterface $kernel;

    /**
     * @param EntityManagerInterface $entityManager
     * @param ContainerBagInterface $containerBag
     * @param TranslatorInterface $translator
     * @param UrlGeneratorInterface $router
     * @param Security $security
     * @param RequestStack $requestStack
     * @param KernelInterface $kernel
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ContainerBagInterface  $containerBag,
        TranslatorInterface    $translator,
        UrlGeneratorInterface  $router, Security $security,
        RequestStack           $requestStack,
        KernelInterface        $kernel,
    )
    {
        $this->kernel = $kernel;
        parent::__construct($entityManager, $containerBag, $translator, $router, $security, $requestStack);
    }

    /**
     * Permet de recharger le cache applicatif de Symfony
     * @throws Exception
     */
    public function reloadCache(): void
    {
        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'cache:clear',
        ]);

        $output = new NullOutput();
        $application->run($input, $output);
    }
}
