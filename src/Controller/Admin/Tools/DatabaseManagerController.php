<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Controller pour la gestion de la base de données
 */

namespace App\Controller\Admin\Tools;

use App\Enum\Admin\Global\Breadcrumb;
use App\Enum\Admin\Tools\DatabaseManager\DatabaseManagerData;
use App\Message\Tools\DumpSql;
use App\Service\Admin\Tools\DatabaseManagerService;
use App\Utils\Global\Database\DataBase;
use App\Utils\Translate\Tools\DatabaseManagerTranslate;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[
    Route(
        '/admin/{_locale}/database-manager',
        name: 'admin_database_manager_',
        requirements: ['_locale' => '%app.supported_locales%'],
    ),
]
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
            Breadcrumb::DOMAIN->value => 'database_manager',
            Breadcrumb::BREADCRUMB->value => [
                'database_manager.index.page_title_h1' => '#',
            ],
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
                'delete_dump_file' => $this->generateUrl('admin_database_manager_delete_dump_file'),
            ],
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
     * @param string $table
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \Doctrine\DBAL\Exception
     */
    #[Route('/ajax/load-schema-table/{table}', name: 'load_schema_table', methods: ['GET'])]
    public function schemaTable(DatabaseManagerService $databaseManagerService, string $table = ''): JsonResponse
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
     * Lance la génération d'un dump SQL en arrière-plan
     * @param MessageBusInterface $bus
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param DatabaseManagerService $databaseManagerService
     * @return JsonResponse
     * @throws ExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/save-database', name: 'save_database', methods: ['POST'])]
    public function saveBdd(
        MessageBusInterface $bus,
        Request $request,
        TranslatorInterface $translator,
        DatabaseManagerService $databaseManagerService,
    ): JsonResponse {
        if (!$this->isCsrfTokenValid('database_manager_save_database', $request->headers->get('X-CSRF-TOKEN'))) {
            return $this->jsonCsrfError($translator);
        }

        $data = json_decode($request->getContent(), true);
        $options = is_array($data) ? $data['options'] ?? null : null;

        $all = filter_var($options['all'] ?? true, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $tables = $options['tables'] ?? [];
        if (
            !is_array($options) ||
            $all === null ||
            !is_array($tables) ||
            array_filter($tables, fn($table) => !is_string($table)) !== [] ||
            (!$all && $tables === []) ||
            !in_array($options['data'] ?? null, DatabaseManagerData::getDataTypes(), true)
        ) {
            return $this->json([
                'success' => false,
                'msg' => $translator->trans('database_manager.error.dump.options', domain: 'database_manager'),
            ]);
        }

        $filename = trim((string) ($options['filename'] ?? ''));
        if ($filename !== '' && !DatabaseManagerData::isValidName($filename)) {
            return $this->json([
                'success' => false,
                'msg' => $translator->trans('database_manager.error.dump.filename', domain: 'database_manager'),
            ]);
        }
        if ($filename !== '' && $databaseManagerService->isDumpExist($filename)) {
            return $this->json([
                'success' => false,
                'msg' => $translator->trans(
                    'database_manager.error.dump.filename.exist',
                    ['fichier' => $filename . DatabaseManagerData::FILE_DUMP_EXTENSION->value],
                    domain: 'database_manager',
                ),
            ]);
        }

        $options = [
            'filename' => $filename,
            'all' => $all,
            'tables' => array_values($tables),
            'data' => $options['data'],
        ];
        $bus->dispatch(new DumpSql($options, $this->getUser()->getId(), $request->getLocale()));

        return $this->json([
            'success' => true,
            'msg' => $translator->trans('database_manager.success.dump', domain: 'database_manager'),
        ]);
    }

    /**
     * Retourne la liste des dumpSQL
     * @param DatabaseManagerService $databaseManagerService
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/all-dump-file', name: 'all_dump_file', methods: ['GET'])]
    public function getAllFileDump(DatabaseManagerService $databaseManagerService): JsonResponse
    {
        return $this->json(['result' => $databaseManagerService->getAllDump()]);
    }

    /**
     * Supprime un fichier dump
     * @param DatabaseManagerService $databaseManagerService
     * @param TranslatorInterface $translator
     * @param Request $request
     * @param string $filename
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/delete-dump-file/{filename}', name: 'delete_dump_file', methods: ['DELETE'])]
    public function deleteDumpFile(
        DatabaseManagerService $databaseManagerService,
        TranslatorInterface $translator,
        Request $request,
        string $filename = '',
    ): JsonResponse {
        if (!$this->isCsrfTokenValid('database_manager_delete_dump_file', $request->headers->get('X-CSRF-TOKEN'))) {
            return $this->jsonCsrfError($translator);
        }

        $result = $databaseManagerService->deleteDumpFile($filename);

        $return['msg'] = $translator->trans(
            'database_manager.success.delete.file.dump',
            ['fichier' => $filename],
            domain: 'database_manager',
        );
        $return['success'] = true;
        if ($result !== '') {
            $return['msg'] = $result;
            $return['success'] = false;
        }

        return $this->json($return);
    }

    /**
     * Télécharge un fichier dump
     * @param DatabaseManagerService $databaseManagerService
     * @param string $filename
     * @return BinaryFileResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/download-dump-file/{filename}', name: 'download_dump_file', methods: ['GET'])]
    public function downloadDumpFile(
        DatabaseManagerService $databaseManagerService,
        string $filename,
    ): BinaryFileResponse {
        $path = $databaseManagerService->getDumpFilePath($filename);
        if ($path === null) {
            throw $this->createNotFoundException();
        }

        return $this->file($path, $filename, ResponseHeaderBag::DISPOSITION_ATTACHMENT);
    }

    /**
     * Retourne une réponse JSON d'erreur pour un jeton CSRF invalide
     * @param TranslatorInterface $translator
     * @return JsonResponse
     */
    private function jsonCsrfError(TranslatorInterface $translator): JsonResponse
    {
        return $this->json(
            [
                'success' => false,
                'msg' => $translator->trans('database_manager.error.csrf', domain: 'database_manager'),
            ],
            Response::HTTP_FORBIDDEN,
        );
    }
}
