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
use App\Entity\Admin\System\OptionSystem;
use App\Service\Admin\Content\Comment\CommentService;
use App\Service\Admin\Content\Page\PageService;
use App\Service\Admin\System\OptionSystemService;
use App\Utils\Breadcrumb;
use App\Utils\Content\Comment\CommentConst;
use App\Utils\Content\Comment\CommentPopulate;
use App\Utils\System\Options\OptionSystemKey;
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
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/{_locale}/comment', name: 'admin_comment_', requirements: ['_locale' => '%app.supported_locales%'])]
#[IsGranted('ROLE_CONTRIBUTEUR')]
class CommentController extends AppAdminController
{
    /**
     * Point d'entrée de la gestion des commentaires
     * @param OptionSystemService $optionSystemService
     * @param CommentService $commentService
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/', name: 'index')]
    public function index(OptionSystemService $optionSystemService, CommentService $commentService): Response
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
            'limit' => $this->optionUserService->getValueByKey(OptionUserKey::OU_NB_ELEMENT),
            'isOpenComment' => $optionSystemService->getValueByKey(OptionSystemKey::OS_OPEN_COMMENT),
            'isModerate' => $optionSystemService->getValueByKey(OptionSystemKey::OS_NEW_COMMENT_WAIT_VALIDATION),
            'nbCommentWaitValidation' => $commentService->getNbCommentByStatus(CommentConst::WAIT_VALIDATION)
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
        Request        $request,
        int            $page = 1,
        int            $limit = 20
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
     * @param PageService $pageService
     * @param CommentTranslate $commentTranslate
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/moderate', name: 'moderate_comments', methods: ['GET'])]
    public function moderateComments(
        Request          $request,
        CommentService   $commentService,
        PageService      $pageService,
        CommentTranslate $commentTranslate
    ): Response
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
            'translate' => $commentTranslate->getTranslateCommentModeration(),
            'urls' => [
                'filter' => $this->generateUrl('admin_comment_moderate_comments_filter'),
                'update' => $this->generateUrl('admin_comment_update_moderate_comment'),
            ],
            'datas' => [
                'status' => $commentService->getAllStatus(),
                'pages' => $pageService->getListeTitlePageByLocale($commentService->getLocales()['current']),
                'defaultStatus' => CommentConst::WAIT_VALIDATION,
                'page' => 1,
                'limit' => $this->optionUserService->getValueByKey(OptionUserKey::OU_NB_ELEMENT),
            ]
        ]);
    }

    /**
     * Retourne la liste des commentaires à modérer
     * @param CommentService $commentService
     * @param int $status
     * @param int $idPage
     * @param int $page
     * @param int $limit
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/moderate/ajax/filter/{status}/{idPage}/{page}/{limit}', name: 'moderate_comments_filter', methods: ['GET'])]
    public function filterCommentsModeration(
        CommentService $commentService,
        int            $status = CommentConst::WAIT_VALIDATION,
        int            $idPage = 0,
        int            $page = 1,
        int            $limit = 20
    ): JsonResponse
    {
        $return = $commentService->getCommentFilter($status, $idPage, $page, $limit);
        return $this->json($return);
    }

    /**
     * Permet de voir un commentaire
     * @param int $id
     * @param Request $request
     * @param CommentService $commentService
     * @param MarkdownEditorTranslate $markdownEditorTranslate
     * @param CommentTranslate $commentTranslate
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/see/{id}', name: 'see', methods: ['GET'])]
    public function see(
        int                     $id,
        Request                 $request,
        CommentService          $commentService,
        MarkdownEditorTranslate $markdownEditorTranslate,
        CommentTranslate        $commentTranslate): Response
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
                'save' => $this->generateUrl('admin_comment_save'),
            ],
            'datas' => [
                'id' => $id,
                'status' => $commentService->getAllStatus(),
                'statusModerate' => CommentConst::MODERATE
            ]
        ]);
    }

    /**
     * Retourne un commentaire en fonction de son id
     * @param CommentService $commentService
     * @param int|null $id
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws ExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/load/{id}', name: 'load', methods: ['GET'])]
    public function getComment(
        CommentService $commentService,
        ?int $id = null
    ): Response
    {
        /** @var Comment $comment */
        $comment = $commentService->findOneById(Comment::class, $id);

        /** @var Page $page */
        $page = $commentService->findOneById(Page::class, $comment->getPage()->getId());
        $title = $page->getPageTranslationByLocale($commentService->getLocales()['current'])->getTitre();

        $commentArray = $commentService->convertEntityToArray($comment, ['page', 'userModeration']);

        if ($comment->getUserModeration() !== null) {
            $user = $comment->getUserModeration();
            $commentArray['userModeration'] = ['login' => $user->getLogin(), 'email' => $user->getEmail()];
        }

        $commentArray['page'] = ['title' => $title, 'url' => $this->generateUrl('admin_page_update', ['id' => $page->getId()])];
        $commentArray['createdAt'] = $comment->getCreatedAt()->format('d/m/y H:i');
        $commentArray['updateAt'] = $comment->getUpdateAt()->format('d/m/y H:i');
        $commentArray['statusStr'] = $commentService->getStatusFormatedByCode($comment->getStatus());

        return $this->json(['comment' => $commentArray]);
    }

    /**
     * Met à jour un commentaire
     * @param Request $request
     * @param CommentService $commentService
     * @param TranslatorInterface $translator
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/save', name: 'save', methods: ['PUT'])]
    public function updateComment(Request $request, CommentService $commentService, TranslatorInterface $translator): Response
    {
        $data = json_decode($request->getContent(), true);
        unset($data['comment']['statusStr']);

        $comment = $commentService->findOneById(Comment::class, $data['comment']['id']);

        $commentPopulate = new CommentPopulate($comment, $data['comment']);
        $comment = $commentPopulate->populate()->getComment();

        if ($comment->getStatus() === CommentConst::MODERATE) {
            $comment->setUserModeration($this->getUser());
        } else {
            $comment->setUserModeration(null);
            $comment->setModerationComment(null);
        }

        $commentService->save($comment);

        return $this->json($commentService->getResponseAjax($translator->trans('comment.see.save.success', domain: 'comment')));
    }

    /**
     * Mise à jour des commentaires pour modération
     * @param CommentService $commentService
     * @param Request $request
     * @param TranslatorInterface $translator
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/ajax/update-moderate-comments', name: 'update_moderate_comment', methods: ['POST'])]
    public function updateCommentModerate(CommentService $commentService, Request $request, TranslatorInterface $translator): Response
    {
        $data = json_decode($request->getContent(), true);

        $commentService->updateMultipleComment($data, $this->getUser());

        $msg = $translator->trans('comment.moderation.update.success', domain: 'comment');
        return $this->json($commentService->getResponseAjax($msg));
    }
}
