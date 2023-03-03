<?php
/**
 * Index, point d'entrée sur le site
 * @author Gourdon Aymeric
 * @version 1.0
 */
namespace App\Controller;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/{_locale}', name: 'index_', requirements: ['_locale' => 'en|es|fr'])]
class IndexController extends AbstractController
{
    /**
     * @throws Exception
     */
    #[Route('/', name: 'app_index')]
    public function index(Connection $connection): Response
    {
        $cs = $connection->createSchemaManager();
        try {
            var_dump($cs->listDatabases());
        } catch (Exception $exception)
        {
            echo 'oki';
        }

        return $this->render('index/index.html.twig', [
            'controller_name' => 'IndexController',
        ]);
    }
}
