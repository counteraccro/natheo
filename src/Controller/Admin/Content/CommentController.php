<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Controller pour la gestion des commentaires
 */
namespace App\Controller\Admin\Content;

use App\Controller\Admin\AppAdminController;
use App\Entity\Admin\Content\Comment\Comment;
use App\Entity\Admin\Content\Page\Page;
use App\Service\Admin\Content\Comment\CommentService;
use App\Utils\Breadcrumb;
use App\Utils\System\Options\OptionUserKey;
use App\Utils\Translate\Content\CommentTranslate;
use App\Utils\Translate\MarkdownEditorTranslate;
use League\CommonMark\Exception\CommonMarkException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

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
     * @param MarkdownEditorTranslate $markdownEditorTranslate
     * @param CommentTranslate $commentTranslate
     * @return Response
     */
    #[Route('/see/{id}', name: 'see', methods: ['GET'])]
    public function see(
        int $id,
        Request $request,
        CommentService $commentService,
        MarkdownEditorTranslate $markdownEditorTranslate,
        CommentTranslate $commentTranslate): Response
    {
        $breadcrumb = [
            Breadcrumb::DOMAIN => 'comment',
            Breadcrumb::BREADCRUMB => [
                'comment.index.page_title_h1' => 'admin_comment_index',
                'comment.see.page_title' => '#'
            ]
        ];

        $translate = $commentTranslate->getTranslateCommentSee();
        $translate['markdown'] = $markdownEditorTranslate->getTranslate();

        return $this->render('admin/content/comment/see.html.twig', [
            'breadcrumb' => $breadcrumb,
            'translate' => $translate,
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
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ExceptionInterface
     */
    #[Route('/load/{id}', name: 'load', methods: ['GET'])]
    public function getComment(
        Request $request,
        CommentService $commentService,
        int $id = null
    ): Response
    {
        /** @var Comment $comment */
        $comment = $commentService->findOneById(Comment::class, $id);

        /** @var Page $page */
        $page = $commentService->findOneById(Page::class, $comment->getPage()->getId());
        $title = $page->getPageTranslationByLocale($commentService->getLocales()['current'])->getTitre();

        $commentArray = $commentService->convertEntityToArray($comment, ['page', 'userModeration']);

        if($comment->getUserModeration() !== null)
        {
            $user = $comment->getUserModeration();
            $commentArray['userModeration'] = ['login' => $user->getLogin(), 'email' => $user->getEmail()];
        }

        $commentArray['page'] = ['title' => $title, 'url' => $this->generateUrl('admin_page_update', ['id' => $page->getId()])];

        return $this->json(['comment' => $commentArray]);
    }
}
