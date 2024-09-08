<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Controller pour la gestion de la base de données
 */

namespace App\Controller\Admin\Tools;

use App\Message\Tools\DumpSql;
use App\Service\Admin\Tools\DatabaseManagerService;
use App\Utils\Breadcrumb;
use App\Utils\Global\DataBase;
use App\Utils\Translate\Tools\DatabaseManagerTranslate;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[\Symfony\Component\Routing\Annotation\Route('/admin/{_locale}/database-manager', name: 'admin_database_manager_',
    requirements: ['_locale' => '%app.supported_locales%'])]
#[IsGranted('ROLE_SUPER_ADMIN')]
class DatabaseManagerController extends AbstractController
{

    /**
     * Point d'entrée
     * @param DatabaseManagerTranslate $databaseManagerTranslate
     * @return Response
     */
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(DatabaseManagerTranslate $databaseManagerTranslate): Response
    {
        $breadcrumb = [
            Breadcrumb::DOMAIN => 'database_manager',
            Breadcrumb::BREADCRUMB => [
                'database_manager.index.page_title_h1' => '#'
            ]
        ];

        return $this->render('admin/tools/database_manager/index.html.twig', [
            'breadcrumb' => $breadcrumb,
            'translate' => $databaseManagerTranslate->getTranslate(),
            'urls' => [
                'load_schema_database' => $this->generateUrl('admin_database_manager_load_schema_database'),
                'load_schema_table' => $this->generateUrl('admin_database_manager_load_schema_table'),
                'load_tables_database' => $this->generateUrl('admin_database_manager_load_tables_database'),
                'save_database' => $this->generateUrl('admin_database_manager_save_database'),
                'all_dump_file' => $this->generateUrl('admin_database_manager_all_dump_file'),
            ]
        ]);
    }

    /**
     * Schema de la base de donnée
     * @param DatabaseManagerService $databaseManagerService
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/load-schema-database', name: 'load_schema_database', methods: ['GET'])]
    public function schemaDatabase(DatabaseManagerService $databaseManagerService): JsonResponse
    {
        return $this->json(['query' => $databaseManagerService->getAllInformationSchemaDatabase()]);
    }

    /**
     * Schéma d'une table
     * @param DatabaseManagerService $databaseManagerService
     * @param $table
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/load-schema-table/{table}', name: 'load_schema_table', methods: ['GET'])]
    public function schemaTable(DatabaseManagerService $databaseManagerService, $table = null): JsonResponse
    {
        $result = $databaseManagerService->getSchemaTableByTable($table);
        return $this->json(['result' => $result]);
    }

    /**
     * Retourne la liste des tables de la base de données
     * @param DataBase $dataBase
     * @return JsonResponse
     */
    #[Route('/ajax/load-tables-database', name: 'load_tables_database', methods: ['GET'])]
    public function listeTablesDatabase(DataBase $dataBase): JsonResponse
    {
        return $this->json(['tables' => $dataBase->getAllNameAndColumn()]);
    }

    /**
     * @param MessageBusInterface $bus
     * @param Request $request
     * @param TranslatorInterface $translator
     * @return JsonResponse
     * @throws ExceptionInterface
     */
    #[Route('/ajax/save-database', name: 'save_database', methods: ['POST'])]
    public function saveBdd(
        MessageBusInterface $bus,
        Request             $request,
        TranslatorInterface $translator
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $bus->dispatch(new DumpSql($data['options'], $this->getUser()->getId()));
        $return['msg'] = $translator->trans('database_manager.success.dump', domain: 'database_manager');
        $return['success'] = true;
        return $this->json($return);
    }

    #[Route('/ajax/all-dump-file', name: 'all_dump_file', methods: ['GET'])]
    public function getAllFileDump(
        DatabaseManagerService $databaseManagerService
    ):JsonResponse
    {
        return $this->json(['result' => $databaseManagerService->getAllDump()]);
    }
}
