<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * authenticate pour API
 */

namespace App\Security;

use App\Http\Api\ApiResponse;
use App\Service\Admin\System\OptionSystemService;
use App\Utils\System\Options\OptionSystemKey;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Contracts\Translation\TranslatorInterface;

class ApiAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private TranslatorInterface $translator,
        private OptionSystemService $optionSystemService,
    ) {}

    /**
     * @param Request $request
     * @return bool|null
     */
    public function supports(Request $request): ?bool
    {
        return $request->headers->has('Authorization') &&
            str_contains($request->headers->get('Authorization'), 'Bearer ');
    }

    /**
     * @param Request $request
     * @return Passport
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function authenticate(Request $request): Passport
    {
        $this->isOpenApi();

        $identifier = trim(str_replace('Bearer ', '', $request->headers->get('Authorization')));
        return new SelfValidatingPassport(new UserBadge($identifier));
    }

    /**
     * @param Request $request
     * @param TokenInterface $token
     * @param string $firewallName
     * @return Response|null
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    /**
     * @param Request $request
     * @param AuthenticationException $exception
     * @return Response|null
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        throw new HttpException(
            Response::HTTP_UNAUTHORIZED,
            $this->translator->trans('api_errors.authentication.failure', domain: 'api_errors'),
        );
    }

    /**
     * Vérifie si l'API est ouverte ou non
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function isOpenApi(): void
    {
        $isOpenApi = intval($this->optionSystemService->getValueByKey(OptionSystemKey::OS_OPEN_SITE));
        if ($isOpenApi === 0) {
            throw new HttpException(
                Response::HTTP_FORBIDDEN,
                $this->translator->trans('api_errors.api.not.open', domain: 'api_errors'),
            );
        }
    }
}
