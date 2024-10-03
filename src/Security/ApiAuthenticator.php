<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * authenticate pour API
 */

namespace App\Security;

use App\Http\Api\ApiResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Contracts\Translation\TranslatorInterface;

class ApiAuthenticator extends AbstractAuthenticator
{
    public function __construct(private TranslatorInterface $translator)
    {

    }

    /**
     * @param Request $request
     * @return bool|null
     */
    public function supports(Request $request): ?bool
    {
        return $request->headers->has('Authorization') && str_contains($request->headers->get('Authorization'), 'Bearer ');
    }

    /**
     * @param Request $request
     * @return Passport
     */
    public function authenticate(Request $request): Passport
    {
        $identifier = trim(str_replace('Bearer ', '', $request->headers->get('Authorization')));
        return new SelfValidatingPassport(
            new UserBadge($identifier)
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $message =  $this->translator->trans('api_errors.authentication.failure', domain: 'api_errors');
        return new ApiResponse($message, null, [], Response::HTTP_UNAUTHORIZED);
    }
}