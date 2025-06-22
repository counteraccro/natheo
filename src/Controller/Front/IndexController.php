<?php
/**
 * Index, point d'entrée sur le site
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Controller\Front;

use App\Service\Front\OptionSystemFrontService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/', name: 'front_', requirements: ['_locale' => '%app.supported_locales%'],
    defaults: ["_locale" => "%app.default_locale%"])]
class IndexController extends AppFrontController
{
    /**
     * Route qui sert uniquement à redirigé vers la connexion avec la bonne local
     * @return RedirectResponse
     */
    #[Route('/', name: 'no_local')]
    public function indexNoLocale(): RedirectResponse
    {
        $defaultLocal = $this->getParameter('app.default_locale');
        return $this->redirectToRoute('front_index', ['_locale' => $defaultLocal]);
    }

    /**
     * Redirige vers la connexion
     * @param string|null $slug
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/{_locale}/{slug}', name: 'index')]
    public function index(?string $slug = null): Response
    {
        return $this->render($this->getPathTemplate() . DIRECTORY_SEPARATOR . 'index.html.twig', []);
    }
}
