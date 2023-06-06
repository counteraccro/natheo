<?php
/**
 * Authentification sur le site
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Controller;

use App\Service\Admin\TranslateService;
use App\Service\Admin\User\UserService;
use App\Service\SecurityService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('{_locale}/security/', name: 'auth_', requirements: ['_locale' => '%app.supported_locales%'],
    defaults: ["_locale" => "%app.default_locale%"])]
class SecurityController extends AbstractController
{
    /**
     * Authentification
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    #[Route(path: 'user/login', name: 'user_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/admin/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: 'logout', name: 'logout')]
    public function logout(): void
    {
        //throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * Permet de changer le mot de passe d'un User (Accès Admin)
     * @param string $key
     * @return Response
     */
    #[Route(path: 'change-password/{key}', name: 'change_password', methods: ['GET'])]
    public function changePasswordAdm(
        string              $key,
        SecurityService     $securityService,
        TranslatorInterface $translator,
        UserService $userService
    ): Response
    {
        $user = $securityService->canChangePassword($key);

        if ($user === null) {
            throw $this->createNotFoundException($translator->trans('user.change_password.error_404', domain: 'user'));
        }

        echo $user->getLogin();

        return $this->render('security/admin/change_password.html.twig', [
            'changePasswordTranslate' => $userService->getTranslateChangePassword(),
        ]);
    }
}
