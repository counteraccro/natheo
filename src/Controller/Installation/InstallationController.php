<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Controller pour l'installation du CMS
 */

namespace App\Controller\Installation;

use App\Service\Installation\InstallationService;
use App\Utils\Global\DataBase;
use App\Utils\Translate\Installation\InstallationTranslate;
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
    public function index(): Response
    {
        return $this->redirectToRoute('installation_no_schema');
    }

    /**
     * @return Response
     * @throws Exception
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/step-1', name: 'no_schema', methods: ['GET'])]
    public function configDatabase(
        InstallationTranslate $installationTranslate,
        InstallationService $installationService
    ): Response
    {
        $forceToRedirect = $this->forceRedirect();
        if ($forceToRedirect !== null) {
            return $forceToRedirect;
        }

        $installationService->getDatabaseUrl();

        return $this->render('installation/installation/step_one.html.twig', [
            'urls' => [],
            'translate' => $installationTranslate->getTranslateStepOne(),
            'locales' => $installationService->getLocales(),
        ]);
    }

    /**
     * @return RedirectResponse|null
     * @throws ContainerExceptionInterface
     * @throws Exception
     * @throws NotFoundExceptionInterface
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
