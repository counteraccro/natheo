<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Controller pour l'installation du CMS
 */

namespace App\Controller\Installation;

use App\Service\Installation\ParseEnvService;
use App\Utils\Global\DataBase;
use Doctrine\DBAL\Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[\Symfony\Component\Routing\Annotation\Route('/{_locale}/installation', name: 'installation_',
    requirements: ['_locale' => '%app.supported_locales%'])]
class InstallationController extends AbstractController
{
    public function __construct(#[AutowireLocator([
        'dataBase' => DataBase::class,
    ])] private readonly ContainerInterface $handlers)
    {
    }

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(ParseEnvService $parseEnvService): Response
    {
        return $this->redirectToRoute('installation_no_schema');
    }

    /**
     * @param ParseEnvService $parseEnvService
     * @return Response
     * @throws Exception
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/config-database', name: 'no_schema', methods: ['GET'])]
    public function configDatabase(ParseEnvService $parseEnvService): Response
    {
        $forceToRedirect = $this->forceRedirect();
        if ($forceToRedirect !== null) {
            return $forceToRedirect;
        }
        $parseResult = $parseEnvService->parseEnvFile();

        return $this->render('installation/installation/config_database.html.twig', [
            'file' => $parseResult['file'], 'envPath' => $parseEnvService->getPathEnvFile(), 'errors' => $parseResult['errors']
        ]);
    }

    /**
     * @return RedirectResponse
     * @throws Exception
     */
    private function forceRedirect(): ?RedirectResponse
    {
        /** @var DataBase $dataBase */
        $dataBase = $this->handlers->get('dataBase');
        if ($dataBase->isTableExiste()) {
            return $this->redirectToRoute('admin_dashboard_index');
        }
        return null;
    }
}
