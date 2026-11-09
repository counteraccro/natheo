<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Processor qui compte la lecture d'une page publiée
 */

namespace App\Api\V2\State\Processor\Content;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Api\V2\Resource\Content\PageRead;
use App\Service\Api\Content\Page\ApiPageService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @implements ProcessorInterface<PageRead, null>
 */
final readonly class CreatePageReadProcessor implements ProcessorInterface
{
    public function __construct(private ApiPageService $apiPageService, private TranslatorInterface $translator) {}

    /**
     * @param PageRead $data
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): null
    {
        $page = $this->apiPageService->findPublishedPage((int) $data->page?->id);
        if ($page === null) {
            throw new NotFoundHttpException(
                $this->translator->trans('api_errors.find.page.not.found', domain: 'api_errors'),
            );
        }
        $this->apiPageService->incrementReadCount($page);
        return null;
    }
}
