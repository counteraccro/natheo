<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Controller pour l'installation du CMS
 */

namespace App\Controller\Installation;

use App\Entity\Admin\System\User;
use App\Service\Admin\CommandService;
use App\Service\Installation\InstallationService;
use App\Utils\Global\Database\DataBase;
use App\Utils\Global\EnvFile;
use App\Utils\Installation\InstallationConst;
use App\Utils\Translate\Installation\InstallationTranslate;
use Doctrine\DBAL\Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[\Symfony\Component\Routing\Annotation\Route('/{_locale}/installation', name: 'installation_',
    requirements: ['_locale' => '%app.supported_locales%'])]
class InstallationController extends AbstractController
{

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->redirectToRoute('installation_step_1');
    }

    /**
     * Etape 1 de l'installation
     * @param InstallationTranslate $installationTranslate
     * @param InstallationService $installationService
     * @param ParameterBagInterface $parameterBag
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws Exception
     * @throws NotFoundExceptionInterface
     */
    #[Route('/step-1', name: 'step_1', methods: ['GET'])]
    public function stepOne(
        InstallationTranslate $installationTranslate,
        InstallationService   $installationService,
        ParameterBagInterface $parameterBag
    ): Response
    {

        if ($installationService->checkSchema()) {
            if ($installationService->checkDataExiste(User::class)) {
                return $this->redirectToRoute('auth_user_login');
            }
            return $this->redirectToRoute('installation_step_2');
        }

        return $this->render('installation/installation/step_one.html.twig', [
            'urls' => [
                'check_database' => $this->generateUrl('installation_check_database'),
                'update_env' => $this->generateUrl('installation_update_env'),
                'create_bdd' => $this->generateUrl('installation_create_bdd'),
                'create_schema' => $this->generateUrl('installation_create_schema'),
                'update_app_secret' => $this->generateUrl('installation_update_app_secret'),
                'step_2' => $this->generateUrl('installation_step_2')
            ],
            'translate' => $installationTranslate->getTranslateStepOne(),
            'locales' => $installationService->getLocales(),
            'datas' => [
                'bdd_config' => $installationService->getDatabaseUrl(),
                'config_key' => [
                    'database_url' => EnvFile::KEY_DATABASE_URL
                ],
                'option_connexion' => [
                    'test_connexion' => InstallationConst::OPTION_DATABASE_URL_TEST,
                    'create_database' => InstallationConst::OPTION_DATABASE_URL_CREATE_DATABASE
                ],
                'bdd_params' => [
                    'database_schema' => $parameterBag->get('app.default_database_schema'),
                    'database_prefix' => $parameterBag->get('app.default_database_prefix'),
                ]
            ]
        ]);
    }

    /**
     * Test la connexion de la base de données
     * @param DataBase $dataBase
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/check-database', name: 'check_database', methods: ['GET'])]
    public function testConnexionDatabase(
        DataBase $dataBase): JsonResponse
    {
        return $this->json(['connexion' => $dataBase->isConnected()]);
    }

    /**
     * Mise à jour du fichier env
     * @param Request $request
     * @param InstallationService $installationService
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/update-env', name: 'update_env', methods: ['POST'])]
    public function updateEnvConfig(Request $request, InstallationService $installationService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $newValue = $installationService->formatDatabaseUrlForEnvFile($data['config'], $data['type']);

        try {
            $installationService->updateValueByKeyInEnvFile(EnvFile::KEY_DATABASE_URL, $newValue);

            if ($data['type'] === InstallationConst::OPTION_DATABASE_URL_CREATE_DATABASE) {
                $installationService->updateValueByKeyInEnvFile(EnvFile::KEY_NATHEO_SCHEMA, EnvFile::KEY_NATHEO_SCHEMA . '="' . $data['config']['bdd_name'] . '"');
            }

            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Met à jour APP_SECRET du fichier env
     * @param InstallationService $installationService
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/update-app-secret', name: 'update_app_secret', methods: ['GET'])]
    public function updateSecret(InstallationService $installationService): JsonResponse
    {
        try {
            $installationService->updateValueByKeyInEnvFile(EnvFile::KEY_APP_SECRET, $installationService->generateSecret());
            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Création de la base de données
     * @param CommandService $commandService
     * @return JsonResponse
     */
    #[Route('/create-bdd', name: 'create_bdd', methods: ['GET'])]
    public function createDatabase(CommandService $commandService): JsonResponse
    {
        try {
            $commandService->createDatabase();
            return $this->json(['success' => true]);
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Création du schema SQL
     * @param CommandService $commandService
     * @return JsonResponse
     */
    #[Route('/create-schema', name: 'create_schema', methods: ['GET'])]
    public function createSchema(CommandService $commandService): JsonResponse
    {
        try {
            $commandService->createSchema();
            return $this->json(['success' => true]);
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Installation étape 2
     * @param InstallationTranslate $installationTranslate
     * @param InstallationService $installationService
     * @param ParameterBagInterface $parameterBag
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/step-2', name: 'step_2', methods: ['GET'])]
    public function stepTwo(
        InstallationTranslate $installationTranslate,
        InstallationService   $installationService,
        ParameterBagInterface $parameterBag
    ): Response
    {

        if (!$installationService->checkSchema()) {
            return $this->redirectToRoute('installation_step_1');
        }
        if ($installationService->checkDataExiste(User::class)) {
            return $this->redirectToRoute('auth_user_login');
        }

        return $this->render('installation/installation/step_two.html.twig', [
            'urls' => [
                'create_user' => $this->generateUrl('installation_create_user'),
                'change_env' => $this->generateUrl('installation_change_env'),
                'load_fixtures' => $this->generateUrl('installation_load_fixtures'),
                'clear_cache' => $this->generateUrl('installation_clear_cache'),
                'auth' => $this->generateUrl('auth_user_login'),
            ],
            'translate' => $installationTranslate->getTranslateStepTwo(),
            'locales' => $installationService->getLocales(),
            'datas' => [
                'debug_mode' => $parameterBag->get('app.debug_mode')
            ]
        ]);
    }

    /**
     * Créer le compte fondateur lors de l'installation du CMS
     * @param Request $request
     * @param InstallationService $installationService
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/create-user', name: 'create_user', methods: ['POST'])]
    public function createUser(Request $request, InstallationService $installationService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $return = $installationService->createUser($data['user']);

        return $this->json($return);

    }

    /**
     * Force le mode dev pour pouvoir lancer les fixtures
     * @param InstallationService $installationService
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/change-env', name: 'change_env', methods: ['GET'])]
    public function changeEnv(InstallationService $installationService): JsonResponse
    {
        try {
            $value = EnvFile::KEY_APP_ENV . '=' . EnvFile::ENV_DEV;
            $installationService->updateValueByKeyInEnvFile(EnvFile::KEY_APP_ENV, $value);
            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Charge les fixtures dans la base de données
     * @param CommandService $commandService
     * @param InstallationService $installationService
     * @param ParameterBagInterface $parameterBag
     * @return JsonResponse
     * @throws \Exception
     */
    #[Route('/load-fixtures', name: 'load_fixtures', methods: ['GET'])]
    public function loadFixtures(CommandService        $commandService,
                                 InstallationService   $installationService,
                                 ParameterBagInterface $parameterBag): JsonResponse
    {
        try {
            $commandService->loadFixtures();
            if ($parameterBag->get('app.debug_mode') === false) {
                $installationService->createNotificationFondateur();
            }
            return $this->json(['success' => true]);
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Nettoyage du cache
     * @param CommandService $commandService
     * @param InstallationService $installationService
     * @return JsonResponse
     * @throws \Exception
     */
    #[Route('/clear-cache', name: 'clear_cache', methods: ['GET'])]
    public function clearCache(CommandService $commandService, InstallationService $installationService): JsonResponse
    {
        try {

            $value = EnvFile::KEY_APP_ENV . '=' . EnvFile::ENV_PROD;
            $installationService->updateValueByKeyInEnvFile(EnvFile::KEY_APP_ENV, $value);
            $commandService->reloadCache();
            return $this->json(['success' => true]);
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
