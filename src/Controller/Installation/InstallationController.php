<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Controller pour l'installation du CMS
 */

namespace App\Controller\Installation;

use App\Entity\Admin\System\User;
use App\Enum\Installation\Env;
use App\Enum\Installation\KeyEnv;
use App\Enum\Installation\OptionInstallation;
use App\Service\Admin\CommandService;
use App\Service\Installation\InstallationService;
use App\Service\Installation\InstallRequirementsChecker;
use App\Utils\Global\Database\DataBase;
use App\Utils\Translate\Installation\InstallationTranslate;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}/installation', name: 'installation_', requirements: ['_locale' => '%app.supported_locales%'])]
class InstallationController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->redirectToRoute('installation_step_0');
    }

    /**
     * Étape zero de l'installation
     * @param Request $request
     * @param InstallationService $installationService
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/step-0', name: 'step_0', methods: ['GET'])]
    public function stepZero(
        InstallationService $installationService,
        InstallRequirementsChecker $installRequirementsChecker,
    ): Response {
        return $this->render('installation/installation/step_zero.html.twig', [
            'allSteps' => $installationService->getAllSteps(),
            'requirement' => $installRequirementsChecker->getInfoRequired(),
        ]);
    }

    /**
     * Etape 1 de l'installation
     * @param InstallationTranslate $installationTranslate
     * @param InstallationService $installationService
     * @param ParameterBagInterface $parameterBag
     * @param InstallRequirementsChecker $installRequirementsChecker
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/step-1', name: 'step_1', methods: ['GET'])]
    public function stepOne(
        InstallationTranslate $installationTranslate,
        InstallationService $installationService,
        ParameterBagInterface $parameterBag,
        InstallRequirementsChecker $installRequirementsChecker,
    ): Response {
        if (!$installRequirementsChecker->isAllRequirements()) {
            return $this->redirectToRoute('installation_step_0');
        }

        if ($installationService->checkSchema()) {
            if ($installationService->checkDataExiste(User::class)) {
                return $this->redirectToRoute('auth_user_login');
            }
            return $this->redirectToRoute('installation_step_3');
        }

        return $this->render('installation/installation/step_one.html.twig', [
            'allSteps' => $installationService->getAllSteps(),
            'urls' => [
                'check_action_bdd' => $this->generateUrl('installation_check_action_bdd'),
                'update_env' => $this->generateUrl('installation_update_env'),
                'step_2' => $this->generateUrl('installation_step_2'),
                'step_0' => $this->generateUrl('installation_step_0'),
            ],
            'translate' => $installationTranslate->getTranslateStepOne(),
            'locales' => $installationService->getLocales(),
            'datas' => [
                'bdd_config' => $installationService->getDatabaseUrl(),
                'config_key' => [
                    'database_url' => KeyEnv::DATABASE_URL->value,
                ],
                'option_check' => [
                    'connexion' => OptionInstallation::CONNEXION->value,
                    'database_exist' => OptionInstallation::DATABASE_EXIST->value,
                ],
            ],
        ]);
    }

    /**
     * Permet de tester la bdd en fonction d'une action
     * @param Request $request
     * @param DataBase $dataBase
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/check-action-bdd', name: 'check_action_bdd', methods: ['POST'])]
    public function CheckActionBdd(Request $request, DataBase $dataBase): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['action'])) {
            return $this->json(['error' => 'Action bdd is required.'], Response::HTTP_BAD_REQUEST);
        }

        return $this->json([$data['action'] => $dataBase->check($data['action'], $data['config'])]);
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
            $installationService->updateValueByKeyInEnvFile(KeyEnv::DATABASE_URL->value, $newValue);

            if ($data['type'] === OptionInstallation::DATABASE_EXIST->value) {
                $installationService->updateValueByKeyInEnvFile(
                    KeyEnv::NATHEO_SCHEMA->value,
                    KeyEnv::NATHEO_SCHEMA->value . '="' . $data['config']['bdd_name'] . '"',
                );
            }

            return $this->json(['success' => true]);
        } catch (\Throwable $e) {
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
            $installationService->updateValueByKeyInEnvFile(
                KeyEnv::APP_SECRET->value,
                $installationService->generateSecret(),
            );
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
        } catch (NotFoundExceptionInterface | ContainerExceptionInterface $e) {
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
        } catch (NotFoundExceptionInterface | ContainerExceptionInterface $e) {
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
     * @param InstallRequirementsChecker $installRequirementsChecker
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/step-2', name: 'step_2', methods: ['GET'])]
    public function stepTwo(
        InstallationTranslate $installationTranslate,
        InstallationService $installationService,
        ParameterBagInterface $parameterBag,
        InstallRequirementsChecker $installRequirementsChecker,
    ): Response {
        if (!$installRequirementsChecker->isAllRequirements()) {
            return $this->redirectToRoute('installation_step_0');
        }

        if ($installationService->checkSchema()) {
            if ($installationService->checkDataExiste(User::class)) {
                return $this->redirectToRoute('auth_user_login');
            }
            return $this->redirectToRoute('installation_step_3');
        }

        return $this->render('installation/installation/step_two.html.twig', [
            'allSteps' => $installationService->getAllSteps(),
            'urls' => [
                'check_action_bdd' => $this->generateUrl('installation_check_action_bdd'),
                'create_bdd' => $this->generateUrl('installation_create_bdd'),
                'create_schema' => $this->generateUrl('installation_create_schema'),
                'update_app_secret' => $this->generateUrl('installation_update_app_secret'),
                'step_3' => $this->generateUrl('installation_step_3'),
                'step_1' => $this->generateUrl('installation_step_1'),
                'update_env' => $this->generateUrl('installation_update_env'),
            ],
            'translate' => $installationTranslate->getTranslateStepOne(),
            'locales' => $installationService->getLocales(),
            'datas' => [
                'bdd_config' => $installationService->getDatabaseUrl(),
                'config_key' => [
                    'database_url' => KeyEnv::DATABASE_URL->value,
                ],
                'option_check' => [
                    'database_exist' => OptionInstallation::DATABASE_EXIST->value,
                ],
                'bdd_params' => [
                    'database_schema' => $parameterBag->get('app.default_database_schema'),
                    'database_prefix' => $parameterBag->get('app.default_database_prefix'),
                ],
            ],
        ]);
    }

    /**
     * Installation étape 3
     * @param InstallationTranslate $installationTranslate
     * @param InstallationService $installationService
     * @param ParameterBagInterface $parameterBag
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/step-3', name: 'step_3', methods: ['GET'])]
    public function stepThree(
        InstallationTranslate $installationTranslate,
        InstallationService $installationService,
        ParameterBagInterface $parameterBag,
    ): Response {
        if (!$installationService->checkSchema()) {
            return $this->redirectToRoute('installation_step_1');
        }
        if ($installationService->checkDataExiste(User::class)) {
            return $this->redirectToRoute('auth_user_login');
        }

        return $this->render('installation/installation/step_three.html.twig', [
            'allSteps' => $installationService->getAllSteps(),
            'urls' => [
                'create_user' => $this->generateUrl('installation_create_user'),
                'change_env' => $this->generateUrl('installation_change_env'),
                'load_fixtures' => $this->generateUrl('installation_load_fixtures'),
                'clear_cache' => $this->generateUrl('installation_clear_cache'),
                'step_4' => $this->generateUrl('installation_step_4'),
            ],
            'translate' => $installationTranslate->getTranslateStepTwo(),
            'locales' => $installationService->getLocales(),
            'datas' => [
                'debug_mode' => $parameterBag->get('app.debug_mode'),
            ],
        ]);
    }

    #[Route('/step-4', name: 'step_4', methods: ['GET'])]
    public function stepFour(InstallationTranslate $installationTranslate, InstallationService $installationService)
    {
        return $this->render('installation/installation/step_four.html.twig', [
            'allSteps' => $installationService->getAllSteps(),
            'urls' => [
                'auth' => $this->generateUrl('auth_user_login'),
            ],
            'translate' => $installationTranslate->getTranslateStepFour(),
            'locales' => $installationService->getLocales(),
            'datas' => [],
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
            $value = KeyEnv::APP_ENV->value . '=' . Env::DEV->value;
            $installationService->updateValueByKeyInEnvFile(KeyEnv::APP_ENV->value, $value);
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
    public function loadFixtures(
        CommandService $commandService,
        InstallationService $installationService,
        ParameterBagInterface $parameterBag,
    ): JsonResponse {
        try {
            $commandService->loadFixtures();
            if ($parameterBag->get('app.debug_mode') === false) {
                $installationService->createNotificationFondateur();
            }
            return $this->json(['success' => true]);
        } catch (NotFoundExceptionInterface | ContainerExceptionInterface $e) {
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
            $value = KeyEnv::APP_ENV->value . '=' . Env::PROD->value;
            $installationService->updateValueByKeyInEnvFile(KeyEnv::APP_ENV->value, $value);
            $commandService->reloadCache();
            return $this->json(['success' => true]);
        } catch (NotFoundExceptionInterface | ContainerExceptionInterface $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
