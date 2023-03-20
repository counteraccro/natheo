<?php
/**
 * User
 * @author Gourdon Aymeric
 * @version 1.0
 */
namespace App\Controller\Admin;

use App\Entity\Admin\User;
use App\Service\Admin\OptionUserService;
use App\Service\Admin\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/{_locale}/user', name: 'admin_user_', requirements: ['_locale' => '%app.supported_locales%'])]
class UserController extends AbstractController
{
    /**
     * point d'entrée
     * @return Response
     */
    #[Route('/', name: 'index')]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function index(): Response
    {
        $breadcrumb = [
            'user.page_title_h1' => '#'
        ];

        return $this->render('admin/user/index.html.twig', [
            'breadcrumb' => $breadcrumb,
            'page' => 1,
            'limit' => 10,
        ]);
    }

    /**
     * Permet au user courant (connecté) de pouvoir gérer ses options
     * @return Response
     */
    #[Route('/my-option', name: 'my_option')]
    #[IsGranted('ROLE_USER')]
    public function userOption(): Response
    {
        $breadcrumb = [
            'user.my_option_title_h1' => '#'
        ];

        return $this->render('admin/user/my_options.html.twig', [
            'breadcrumb' => $breadcrumb,
        ]);
    }

    /**
     * Met à jour une option
     * @param Request $request
     * @param OptionUserService $optionUserService
     * @return JsonResponse
     */
    #[Route('/ajax/update', name: 'ajax_update_my_option', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function updateMyOption(Request $request, OptionUserService $optionUserService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $optionUserService->saveValueByKee($data['key'], $data['value']);
        return $this->json(['success' => 'true']);
    }

    /**
     * Charge le tableau grid de sidebar en ajax
     * @param Request $request
     * @param UserService $userService
     * @return JsonResponse
     */
    #[Route('/ajax/load-grid-data', name: 'load_grid_data' , methods: ['POST'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function loadGridData(Request $request, UserService $userService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $grid = $userService->getAllFormatToGrid($data['page'], $data['limit']);
        return $this->json($grid);
    }

    /**
     * Disabled ou non un user
     * @param User $user
     * @param UserService $userService
     * @param TranslatorInterface $translator
     * @return JsonResponse
     */
    #[Route('/ajax/update-disabled/{id}', name: 'update_disabled', methods: ['POST'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function updateDisabled(User $user, UserService $userService, TranslatorInterface $translator): JsonResponse
    {
        $user->setDisabled(!$user->isDisabled());
        $userService->save($user);

        $msg = $translator->trans('user.success.no.disabled', ['login' => $user->getLogin()]);
        if($user->isDisabled())
        {
            $msg = $translator->trans('user.success.disabled', ['login' => $user->getLogin()]);
        }

        return $this->json(['type' => 'success', 'msg' => $msg]);
    }

    #[Route('/ajax/delete/{id}', name: 'delete', methods: ['POST'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function delete(User $user, UserService $userService): JsonResponse
    {
        return $this->json(['type' => 'success', 'msg' => 'delete']);
    }

    /**
     * Permet de modifier un user
     * @param User $user
     * @param UserService $userService
     * @return Response
     */
    #[Route('/ajax/update/{id}', name: 'update', methods: ['GET'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function update(User $user, UserService $userService): Response
    {
        $breadcrumb = [
            'user.page_title_h1' => 'admin_user_index',
            'user.page_update_title_h1' => '#'
        ];

        return $this->render('admin/user/update.html.twig', [
            'breadcrumb' => $breadcrumb,
        ]);
    }
}
