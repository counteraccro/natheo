<?php

namespace App\Security;

use App\Entity\Admin\System\User;
use App\Service\Admin\System\User\UserDataService;
use App\Service\LoggerService;
use App\Utils\System\User\UserdataKey;
use Exception;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class UserAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'auth_user_login';

    private LoggerService $loggerService;

    private UserDataService $userDataService;

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        LoggerService $loggerService,
        UserDataService $userDataService
    )
    {
        $this->loggerService = $loggerService;
        $this->userDataService = $userDataService;
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('email', '');
        $request->getSession()->set(Security::LAST_USERNAME, $email);

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($request->request->get('password', '')),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {

        /** @var User $user */
        $user = $token->getUser();
        $ip = $request->getClientIp();
        $this->loggerService->logAuthAdmin($user->getEmail(), $ip);

        $date = new \DateTime();
        $this->userDataService->update(UserdataKey::KEY_LAST_CONNEXION, $date->getTimestamp(), $user);


        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        // For example:
        // return new RedirectResponse($this->urlGenerator->generate('some_route'));
        throw new Exception('TODO: provide a valid redirect inside '.__FILE__);
    }

    /**
     * @param Request $request
     * @param AuthenticationException $exception
     * @return Response
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $ip = $request->getClientIp();
        $email = $request->request->get('email', '');

        $this->loggerService->logAuthAdmin($email, $ip, false);
        return parent::onAuthenticationFailure($request, $exception);
    }


    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
