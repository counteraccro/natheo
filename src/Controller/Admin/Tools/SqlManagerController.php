<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Controller pour la gestion des requêtes SQL
 */

namespace App\Controller\Admin\Tools;

use App\Controller\Admin\AppAdminController;
use App\Entity\Admin\Tools\SqlManager;
use App\Service\Admin\Tools\SqlManagerService;
use App\Utils\Breadcrumb;
use App\Utils\Global\Database\DataBase;
use App\Utils\System\Options\OptionUserKey;
use App\Utils\Translate\Tools\SqlManagerTranslate;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/{_locale}/sql-manager', name: 'admin_sql_manager_',
    requirements: ['_locale' => '%app.supported_locales%'])]
#[IsGranted('ROLE_SUPER_ADMIN')]
class SqlManagerController extends AppAdminController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        $breadcrumb = [
            Breadcrumb::DOMAIN => 'sql_manager',
            Breadcrumb::BREADCRUMB => [
                'sql_manager.index.page_title_h1' => '#'
            ]
        ];

        return $this->render('admin/tools/sql_manager/index.html.twig', [
            'breadcrumb' => $breadcrumb,
            'page' => 1,
            'limit' => $this->optionUserService->getValueByKey(OptionUserKey::OU_NB_ELEMENT)
        ]);
    }

    /**
     * Charge le tableau grid de faq en ajax
     * @param SqlManagerService $sqlManagerService
     * @param Request $request
     * @param int $page
     * @param int $limit
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/load-grid-data/{page}/{limit}', name: 'load_grid_data', methods: ['GET'])]
    public function loadGridData(
        SqlManagerService $sqlManagerService,
        Request           $request,
        int               $page = 1,
        int               $limit = 20
    ): JsonResponse
    {
        $search = $request->query->get('search');
        $grid = $sqlManagerService->getAllFormatToGrid($page, $limit, $search);
        return $this->json($grid);
    }

    /**
     * Active ou désactive une requête SQL
     * @param SqlManager $sqlManager
     * @param SqlManagerService $sqlManagerService
     * @param TranslatorInterface $translator
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/disabled/{id}', name: 'disabled', methods: ['PUT'])]
    public function updateDisabled(
        #[MapEntity(id: 'id')] SqlManager $sqlManager,
        SqlManagerService                 $sqlManagerService,
        TranslatorInterface               $translator): JsonResponse
    {

        $sqlManager->setDisabled(!$sqlManager->isDisabled());

        $msg = $translator->trans('sql_manager.success.no.disabled',
            ['label' => $sqlManager->getName()], 'sql_manager');
        if ($sqlManager->isDisabled()) {
            $msg = $translator->trans('sql_manager.success.disabled',
                ['label' => $sqlManager->getName()], 'sql_manager');
        }
        $sqlManagerService->save($sqlManager);
        return $this->json($sqlManagerService->getResponseAjax($msg));
    }

    /**
     * Supprime une FAQ
     * @param SqlManager $sqlManager
     * @param SqlManagerService $sqlManagerService
     * @param TranslatorInterface $translator
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/delete/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(
        #[MapEntity(id: 'id')] SqlManager          $sqlManager,
        SqlManagerService   $sqlManagerService,
        TranslatorInterface $translator): JsonResponse
    {
        $msg = $translator->trans('sql_manager.remove.success', ['label' => $sqlManager->getName()],
            domain: 'sql_manager');
        $sqlManagerService->remove($sqlManager);
        return $this->json($sqlManagerService->getResponseAjax($msg));
    }

    /**
     * Création / édition d'une faq
     * @param SqlManagerTranslate $sqlManagerTranslate
     * @param SqlManagerService $sqlManagerService
     * @param int|null $id
     * @param bool $isExecute
     * @return Response
     */
    #[Route('/add/', name: 'add')]
    #[Route('/update/{id}', name: 'update')]
    #[Route('/execute/{id}/{isExecute}', name: 'execute')]
    public function add(
        SqlManagerTranslate $sqlManagerTranslate,
        SqlManagerService   $sqlManagerService,
        int                 $id = null,
        bool                $isExecute = false,
    ): Response
    {
        $breadcrumbTitle = 'sql_manager.update.page_title_h1';
        if ($id === null) {
            $breadcrumbTitle = 'sql_manager.add.page_title_h1';
        } elseif ($isExecute) {
            $breadcrumbTitle = 'sql_manager.execute.page_title_h1';
        }

        if ($id !== null) {
            /** @var SqlManager $sqlManager */
            $sqlManager = $sqlManagerService->findOneById(SqlManager::class, $id);
            if ($sqlManager->isDisabled()) {
                return $this->redirect($this->generateUrl('admin_sql_manager_index'));
            }
        }

        $breadcrumb = [
            Breadcrumb::DOMAIN => 'sql_manager',
            Breadcrumb::BREADCRUMB => [
                'sql_manager.index.page_title' => 'admin_sql_manager_index',
                $breadcrumbTitle => '#'
            ]
        ];

        return $this->render('admin/tools/sql_manager/add_update.html.twig', [
            'breadcrumb' => $breadcrumb,
            'translate' => $sqlManagerTranslate->getTranslate(),
            'id' => $id,
            'isExecute' => $isExecute,
            'schema' => $this->getParameter('app.default_database_schema'),
            'datas' => [
            ],
            'urls' => [
                'load_sql_manager' => $this->generateUrl('admin_sql_manager_load_data', ['id' => $id]),
                'load_data_database' => $this->generateUrl('admin_sql_manager_load_data_database'),
                'execute_sql' => $this->generateUrl('admin_sql_manager_execute_sql'),
                'save' => $this->generateUrl('admin_sql_manager_save'),
            ]

        ]);
    }

    /**
     * Charge les données de SQLManager
     * @param SqlManagerService $sqlManagerService
     * @param int|null $id
     * @return JsonResponse
     * @throws ExceptionInterface
     */
    #[Route('/ajax/load-data/{id}', name: 'load_data', methods: ['GET'])]
    public function loadData(
        SqlManagerService $sqlManagerService,
        int               $id = null
    ): JsonResponse
    {
        if ($id === null) {
            $sqlManager = new SqlManager();
        } else {
            $sqlManager = $sqlManagerService->findOneById(SqlManager::class, $id);
        }
        $sqlManagerArray = $sqlManagerService->convertEntityToArray($sqlManager, ['user']);
        return $this->json(['sqlManager' => $sqlManagerArray]);
    }

    /**
     * Charge les informations de la base de données
     * @param DataBase $dataBase
     * @return JsonResponse
     */
    #[Route('/ajax/load-data-database', name: 'load_data_database', methods: ['GET'])]
    public function loadDataDataBase(
        DataBase $dataBase,
    ): JsonResponse
    {
        $dataBdd = $dataBase->getAllNameAndColumn();
        return $this->json(['dataInfo' => $dataBdd]);
    }

    /**
     * Charge les informations de la base de données
     * @param DataBase $dataBase
     * @param SqlManagerService $sqlManagerService
     * @param TranslatorInterface $translator
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/ajax/execute', name: 'execute_sql', methods: ['POST'])]
    public function executeSQL(
        DataBase            $dataBase,
        SqlManagerService   $sqlManagerService,
        TranslatorInterface $translator,
        Request             $request
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if ($sqlManagerService->isOnlySelectQuery($data['query'])) {
            $result = $dataBase->executeRawQuery($data['query']);
        } else {
            $result = [
                'result' => [],
                'header' => [],
                'error' => $translator->trans('sql_manager.error.not.only.select.query', domain: 'sql_manager')
            ];
        }

        return $this->json(['data' => $result]);
    }

    /**
     * Sauvegarde une query
     * @param SqlManagerService $sqlManagerService
     * @param Request $request
     * @param TranslatorInterface $translator
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/save', name: 'save', methods: ['POST'])]
    public function save(
        SqlManagerService   $sqlManagerService,
        Request             $request,
        TranslatorInterface $translator
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if ($data['name'] === null || $data['query'] === null) {
            return $this->json($sqlManagerService->getResponseAjax(null,
                $translator->trans('sql_manager.save.error.validate', domain: 'sql_manager')));
        }

        if (!$sqlManagerService->isOnlySelectQuery($data['query'])) {
            return $this->json($sqlManagerService->getResponseAjax(null,
                $translator->trans('sql_manager.save.error.no.select', domain: 'sql_manager')));
        }


        $redirect = false;
        if ($data['id'] === null) {
            $sqlManager = new SqlManager();
            $sqlManager->setUser($this->getUser())->setName($data['name'])
                ->setQuery($data['query'])
                ->setDisabled(false);
            $redirect = true;
        } else {
            /** @var SqlManager $sqlManager */
            $sqlManager = $sqlManagerService->findOneById(SqlManager::class, $data['id']);
            $sqlManager->setQuery($data['query'])->setName($data['name']);
        }
        $sqlManagerService->save($sqlManager);


        $returnArray = $sqlManagerService->getResponseAjax(
            $translator->trans('sql_manager.save.success', domain: 'sql_manager'));
        $returnArray['url_redirect'] = $this->generateUrl('admin_sql_manager_update', ['id' => $sqlManager->getId()]);
        $returnArray['redirect'] = $redirect;
        return $this->json($returnArray);
    }

    /**
     * Sauvegarde les requêtes SQL généré par le CMS
     * @param SqlManagerService $sqlManagerService
     * @param Request $request
     * @param TranslatorInterface $translator
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/save-generic-query', name: 'save_generic_query', methods: ['POST'])]
    public function saveGenericQuery(
        SqlManagerService   $sqlManagerService,
        Request             $request,
        TranslatorInterface $translator
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $sqlManager = new SqlManager();
        $sqlManager->setUser($this->getUser())
            ->setName($translator->trans('sql_manager.name.generic.query.success', domain: 'sql_manager'))
            ->setQuery($data['query'])->setDisabled(false);

        $sqlManagerService->save($sqlManager);

        return $this->json($sqlManagerService->getResponseAjax(
            $translator->trans('sql_manager.save.generic.query.success', domain: 'sql_manager'))
        );
    }
}
