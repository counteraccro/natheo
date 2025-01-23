<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Controller pour la gestion des commentaires
 */
namespace App\Controller\Admin\Content;

use App\Controller\Admin\AppAdminController;
use App\Entity\Admin\Content\Comment\Comment;
use App\Entity\Admin\Content\Tag\Tag;
use App\Service\Admin\Content\Comment\CommentService;
use App\Utils\Breadcrumb;
use App\Utils\System\Options\OptionUserKey;
use App\Utils\Translate\Content\CommentTranslate;
use League\CommonMark\Exception\CommonMarkException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
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
     * @throws CommonMarkException
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

    /**
     * Permet de modérer les commentaires
     * @param Request $request
     * @param CommentService $commentService
     * @return Response
     */
    #[Route('/moderate', name: 'moderate_comments', methods: ['GET'])]
    public function moderateComments(Request $request, CommentService $commentService): Response
    {
        $breadcrumb = [
            Breadcrumb::DOMAIN => 'comment',
            Breadcrumb::BREADCRUMB => [
                'comment.index.page_title_h1' => 'admin_comment_index',
                'comment.moderate.comments.page_title' => '#'
            ]
        ];

        return $this->render('admin/content/comment/moderate_comments.html.twig', [
            'breadcrumb' => $breadcrumb,
        ]);
    }

    /**
     * Permet de voir un commentaire
     * @param int $id
     * @param Request $request
     * @param CommentService $commentService
     * @param CommentTranslate $commentTranslate
     * @return Response
     */
    #[Route('/see/{id}', name: 'see', methods: ['GET'])]
    public function see(
        int $id,
        Request $request,
        CommentService $commentService,
        CommentTranslate $commentTranslate): Response
    {
        $breadcrumb = [
            Breadcrumb::DOMAIN => 'comment',
            Breadcrumb::BREADCRUMB => [
                'comment.index.page_title_h1' => 'admin_comment_index',
                'comment.see.page_title' => '#'
            ]
        ];

        return $this->render('admin/content/comment/see.html.twig', [
            'breadcrumb' => $breadcrumb,
            'translate' => $commentTranslate->getTranslateCommentSee(),
            'urls' => [
                'load_comment' => $this->generateUrl('admin_comment_load'),
            ],
            'id' => $id,
        ]);
    }

    /**
     * Retourne un commentaire en fonction de son id
     * @param Request $request
     * @param CommentService $commentService
     * @param int|null $id
     * @return Response
     */
    #[Route('/load/{id}', name: 'load', methods: ['GET'])]
    public function getComment(
        Request $request,
        CommentService $commentService,
        int $id = null
        //#[MapEntity(id: 'id')] Comment $comment = null
    ): Response
    {
        return $this->json($id);
    }
}
