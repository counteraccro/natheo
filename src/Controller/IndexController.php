<?php
/**
 * Index, point d'entrée sur le site
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Controller;

use App\Service\Admin\System\OptionUserService;
use App\Utils\Global\DataBase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/', name: 'index_', requirements: ['_locale' => '%app.supported_locales%'],
    defaults: ["_locale" => "%app.default_locale%"])]
class IndexController extends AbstractController
{
    /**
     * Route qui sert uniquement à redirigé vers la connexion avec la bonne local
     * @return RedirectResponse
     */
    #[Route('/', name: 'no_local')]
    public function indexNoLocale(): RedirectResponse
    {
        $defaultLocal = $this->getParameter('app.default_locale');
        return $this->redirectToRoute('auth_user_login', ['_locale' => $defaultLocal]);
    }

    /**
     * Redirige vers la connexion
     * @return Response
     */
    #[Route('/{_locale}/', name: 'index')]
    public function index(): Response
    {
        return $this->redirectToRoute('auth_user_login');
    }
}
