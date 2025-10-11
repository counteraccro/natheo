<?php
/**
 * Authentification sur le site
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Controller;

use App\Entity\Admin\System\User;
use App\Service\Admin\System\MailService;
use App\Service\Admin\System\OptionSystemService;
use App\Service\Admin\System\User\UserDataService;
use App\Service\Admin\System\User\UserService;
use App\Service\Installation\InstallationService;
use App\Service\SecurityService;
use App\Utils\System\Mail\KeyWord;
use App\Utils\System\Mail\MailKey;
use App\Utils\System\User\UserdataKey;
use App\Utils\Translate\System\UserTranslate;
use Doctrine\ORM\NonUniqueResultException;
use League\CommonMark\Exception\CommonMarkException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\String\ByteString;
use Symfony\Contracts\Translation\TranslatorInterface;

#[
    Route(
        '{_locale}/admin/',
        name: 'auth_',
        requirements: ['_locale' => '%app.supported_locales%'],
        defaults: ['_locale' => '%app.default_locale%'],
    ),
]
class SecurityController extends AbstractController
{
    /**
     * Authentification
     * @param AuthenticationUtils $authenticationUtils
     * @param InstallationService $installationService
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route(path: 'login', name: 'user_login')]
    public function login(AuthenticationUtils $authenticationUtils, InstallationService $installationService): Response
    {
        if (!$installationService->checkSchema()) {
            return $this->redirectToRoute('installation_step_1');
        }

        if (!$installationService->checkDataExiste(User::class)) {
            return $this->redirectToRoute('installation_step_2');
        }

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
     * @param SecurityService $securityService
     * @param TranslatorInterface $translator
     * @param UserTranslate $userTranslate
     * @param Request $request
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \DateMalformedIntervalStringException
     */
    #[Route(path: 'change-password/{key}', name: 'change_password_user', methods: ['GET'])]
    #[Route(path: 'new-password/{key}', name: 'change_new_password_user', methods: ['GET'])]
    public function changePasswordAdm(
        string $key,
        SecurityService $securityService,
        TranslatorInterface $translator,
        UserTranslate $userTranslate,
        Request $request,
    ): Response {
        $user = $securityService->canChangePassword($key);

        if ($user === null) {
            throw $this->createNotFoundException($translator->trans('user.change_password.error_404', domain: 'user'));
        }

        $new = false;
        if ($request->attributes->get('_route') === 'auth_change_new_password_user') {
            $new = true;
        }

        return $this->render('security/admin/change_password.html.twig', [
            'changePasswordTranslate' => $userTranslate->getTranslateChangePassword(),
            'user' => $user,
            'new' => $new,
        ]);
    }

    /**
     * Changement de mot passe du user
     * @param User $user
     * @param UserService $userService
     * @param Request $request
     * @param TranslatorInterface $translator
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/change-password/update/{id}', name: 'change_password_update_user', methods: ['POST'])]
    public function updatePassword(
        #[MapEntity(id: 'id')] User $user,
        UserService $userService,
        Request $request,
        TranslatorInterface $translator,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $userService->updatePassword($user, $data['data']);

        return $this->json([
            'status' => 'success',
            'msg' => $translator->trans('user.change_password.success', domain: 'user'),
            'redirect' => $this->generateUrl('auth_user_login'),
        ]);
    }

    /**
     * @param Request $request
     * @param UserService $userService
     * @param MailService $mailService
     * @param OptionSystemService $optionSystemService
     * @param UserDataService $userDataService
     * @param TranslatorInterface $translator
     * @return Response
     * @throws CommonMarkException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws TransportExceptionInterface
     */
    #[Route('reset-password/update', name: 'reset_password_user', methods: ['GET', 'POST'])]
    public function resetPassword(
        Request $request,
        UserService $userService,
        MailService $mailService,
        OptionSystemService $optionSystemService,
        UserDataService $userDataService,
        TranslatorInterface $translator,
    ): Response {
        $msg = '';
        $email = $request->get('email');

        if (!empty($email)) {
            $msg = $translator->trans('user.reset_password.success', domain: 'user');

            $user = $userService->findOneBy(User::class, 'email', $email);

            if ($user != null) {
                $key = ByteString::fromRandom(48)->toString();
                $userDataService->update(UserDataKey::KEY_RESET_PASSWORD, $key, $user);

                $mail = $mailService->getByKey(MailKey::MAIL_CHANGE_PASSWORD);
                $keyWord = new KeyWord($mail->getKey());
                $tabKeyWord = $keyWord->getMailChangePassword(
                    $user,
                    $this->generateUrl('auth_change_password_user', ['key' => $key]),
                    $optionSystemService,
                );
                $params = $mailService->getDefaultParams($mail, $tabKeyWord);
                $params[MailService::TO] = $user->getEmail();

                $mailService->sendMail($params);
            }
        }

        return $this->render('security/admin/reset_password.html.twig', [
            'msg' => $msg,
        ]);
    }

    /**
     * Vérification pour authentification
     * @return never
     */
    #[Route('/login_check', name: 'login_check')]
    public function check(): never
    {
        throw new \LogicException('This code should never be reached');
    }
}
