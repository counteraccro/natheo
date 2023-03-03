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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/', name: 'index_', requirements: ['_locale' => '%app.supported_locales%'], defaults: ["_locale" => "%app.default_locale%"])]
class IndexController extends AbstractController
{
    /**
     * Route qui sert uniquement à redirigé vers l'index avec la bonne local
     * @return RedirectResponse
     */
    #[Route('/', name: 'no_local')]
    public function indexNoLocale(): RedirectResponse
    {
        $default_local = $this->getParameter('app.default_locale');
        return $this->redirectToRoute('index_index', ['_locale' => $default_local]);
    }

    /**
     * @throws Exception
     */
    #[Route('/{_locale}/', name: 'index')]
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
