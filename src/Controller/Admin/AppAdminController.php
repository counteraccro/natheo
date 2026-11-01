<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Service\Admin\System\OptionUserService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class AppAdminController extends AbstractController
{
    /**
     * Filtre pour n'afficher que mes données
     * @var string
     */
    const string FILTER_ME = 'me';

    /**
     * Header HTTP portant le jeton CSRF des actions AJAX
     * @var string
     */
    const string CSRF_HEADER = 'X-CSRF-TOKEN';

    /**
     * @var OptionUserService
     */
    protected OptionUserService $optionUserService;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @param ContainerInterface $handlers
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        #[
            AutowireLocator([
                'optionUserService' => OptionUserService::class,
                'logger' => LoggerInterface::class,
                'translator' => TranslatorInterface::class,
            ]),
        ]
        private readonly ContainerInterface $handlers,
    ) {
        $this->optionUserService = $this->handlers->get('optionUserService');
        $this->logger = $this->handlers->get('logger');
    }

    /**
     * Retourne une réponse JSON d'erreur au format attendu par les toasts Vue
     * @param string $msg
     * @param int $status
     * @return JsonResponse
     */
    protected function jsonError(string $msg, int $status = Response::HTTP_FORBIDDEN): JsonResponse
    {
        return $this->json(['success' => false, 'msg' => $msg], $status);
    }

    /**
     * Retourne l'erreur JSON 403 de jeton CSRF invalide
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function jsonCsrfError(): JsonResponse
    {
        return $this->jsonError($this->getTranslator()->trans('global.error.csrf', domain: 'global'));
    }

    /**
     * Vérifie le jeton CSRF envoyé dans le header X-CSRF-TOKEN
     * Retourne null si le jeton est valide, sinon l'erreur JSON à renvoyer
     * @param string $tokenId identifiant du jeton
     * @param Request $request
     * @return JsonResponse|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function checkCsrf(string $tokenId, Request $request): ?JsonResponse
    {
        if ($this->isCsrfTokenValid($tokenId, $request->headers->get(self::CSRF_HEADER))) {
            return null;
        }
        return $this->jsonCsrfError();
    }

    /**
     * Vérifie un droit sur un contenu (attributs de ContentVoter)
     * Retourne null si l'accès est accordé, sinon l'erreur JSON 403 traduite dans le domaine $domain
     * (clé <domain>.error.forbidden)
     * @param string $attribute attribut ContentVoter::EDIT, DISABLE ou DELETE
     * @param object $subject contenu concerné
     * @param string $domain domaine de traduction du module
     * @return JsonResponse|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function checkContentAccess(string $attribute, object $subject, string $domain): ?JsonResponse
    {
        if ($this->isGranted($attribute, $subject)) {
            return null;
        }
        return $this->jsonError($this->getTranslator()->trans($domain . '.error.forbidden', domain: $domain));
    }

    /**
     * Retourne l'erreur JSON 404 d'un contenu introuvable, traduite dans le domaine $domain
     * (clé <domain>.error.not_found)
     * @param string $domain domaine de traduction du module
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function jsonNotFound(string $domain): JsonResponse
    {
        return $this->jsonError(
            $this->getTranslator()->trans($domain . '.error.not_found', domain: $domain),
            Response::HTTP_NOT_FOUND,
        );
    }

    /**
     * @return TranslatorInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getTranslator(): TranslatorInterface
    {
        return $this->handlers->get('translator');
    }
}
