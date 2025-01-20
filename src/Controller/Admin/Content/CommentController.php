<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Controller pour la gestion des commentaires
 */
namespace App\Controller\Admin\Content;

use App\Controller\Admin\AppAdminController;
use App\Service\Admin\Content\Comment\CommentService;
use App\Utils\Breadcrumb;
use App\Utils\System\Options\OptionUserKey;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/{_locale}/comment', name: 'admin_comment_', requirements: ['_locale' => '%app.supported_locales%'])]
#[IsGranted('ROLE_CONTRIBUTEUR')]
class CommentController extends AppAdminController
{
    /**
     * Point d'entrée de la gestion des commentaires
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        $breadcrumb = [
            Breadcrumb::DOMAIN => 'comment',
            Breadcrumb::BREADCRUMB => [
                'comment.index.page_title_h1' => '#'
            ]
        ];

        return $this->render('admin/content/comment/index.html.twig', [
            'breadcrumb' => $breadcrumb,
            'page' => 1,
            'limit' => $this->optionUserService->getValueByKey(OptionUserKey::OU_NB_ELEMENT)
        ]);
    }

    /**
     * Charge le tableau grid de faq en ajax
     * @param CommentService $commentService
     * @param Request $request
     * @param int $page
     * @param int $limit
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/load-grid-data/{page}/{limit}', name: 'load_grid_data', methods: ['GET'])]
    public function loadGridData(
        CommentService $commentService,
        Request    $request,
        int        $page = 1,
        int        $limit = 20
    ): JsonResponse
    {
        $search = $request->query->get('search');
        $filter = $request->query->get('filter');

        $userId = null;
        if ($filter === self::FILTER_ME) {
            $userId = $this->getUser()->getId();
        }
        $grid = $commentService->getAllFormatToGrid($page, $limit, $search, $userId);
        return $this->json($grid);
    }
}
