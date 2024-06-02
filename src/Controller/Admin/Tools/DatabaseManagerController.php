<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Controller pour la gestion de la base de données
 */
namespace App\Controller\Admin\Tools;

use App\Message\Tools\DumpSql;
use App\Service\Admin\Tools\SqlManagerService;
use App\Utils\Breadcrumb;
use App\Utils\Tools\DatabaseManager\Query\RawPostgresQuery;
use App\Utils\Translate\Tools\DatabaseManagerTranslate;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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

        //$query = RawPostgresQuery::getQueryStructureTable('faq');


        //Debug::printR($dataBase->executeRawQuery($query));

        return $this->render('admin/tools/database_manager/index.html.twig', [
            'breadcrumb' => $breadcrumb,
            'translate' => $databaseManagerTranslate->getTranslate(),
            'urls' => [
                'load_schema_database' => $this->generateUrl('admin_database_manager_load_schema_database'),
                'save_database' => $this->generateUrl('admin_database_manager_save_database'),
            ]
        ]);
    }

    /**
     * @return JsonResponse
     */
    #[Route('/ajax/load-schema-database', name: 'load_schema_database', methods: ['GET'])]
    public function schemaDataBase(): JsonResponse
    {
        $query = RawPostgresQuery::getQueryAllInformationSchema('natheo');
        //$dataBase->executeRawQuery($query)
        return $this->json([]);
    }

    /**
     * @return JsonResponse
     */
    #[Route('/ajax/save-database/{option}', name: 'save_database', methods: ['GET'])]
    public function saveBdd(
        MessageBusInterface $bus,
        SqlManagerService $sqlManagerService,
        string $option = null
    ): JsonResponse
    {
        /* A faire en asyncrone
        Voir ici https://symfony.com/doc/current/messenger.html#creating-a-message-handler

        Génération schema : https://www.doctrine-project.org/projects/doctrine-orm/en/3.2/reference/tools.html#database-schema-generation
        */

        $bus->dispatch(new DumpSql(['option'], $this->getUser()->getId()));
        return $this->json($sqlManagerService->getResponseAjax());
    }
}
