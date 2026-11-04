<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Permet de résoudre la validation de l'objet ApiFindPageCategoryDto
 */

namespace App\Resolver\Api\Content\Page;

use App\Dto\Api\Content\Page\ApiFindPageCategoryDto;
use App\Resolver\Api\AppApiResolver;
use App\Enum\Api\Parameters\Content\Page\ApiFindPageCategoryParameter;
use App\Enum\Api\Parameters\Content\Page\ApiFindPageContentParameter;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class ApiFindPageCategoryResolver extends AppApiResolver implements ValueResolverInterface
{
    /**
     * Permet de mapper ApiFindPageContent avec Request
     * @param Request $request
     * @param ArgumentMetadata $argument
     * @return iterable
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        // Test pour éviter que ce résolver soit appeler pour autre chose que ApiAuthUserDto
        $argumentType = $argument->getType();
        if (!is_a($argumentType, ApiFindPageCategoryDto::class, true)) {
            return [];
        }

        $tabParameters = ApiFindPageCategoryParameter::PARAMS_REF;

        foreach ($tabParameters as $parameter => $value) {
            $value = $request->query->get($parameter, '');

            if (empty($value) && isset(ApiFindPageCategoryParameter::PARAMS_DEFAULT_VALUE[$parameter])) {
                $value = ApiFindPageCategoryParameter::PARAMS_DEFAULT_VALUE[$parameter];
            }
            $tabParameters[$parameter] = $value;
        }

        $tabParameters[ApiFindPageCategoryParameter::USER_TOKEN->value] = $this->getUserToken($request);

        $dto = new ApiFindPageCategoryDto(
            $tabParameters[ApiFindPageCategoryParameter::CATEGORY->value],
            $tabParameters[ApiFindPageCategoryParameter::LOCALE->value],
            intval($tabParameters[ApiFindPageCategoryParameter::PAGE->value]),
            intval($tabParameters[ApiFindPageCategoryParameter::LIMIT->value]),
            $tabParameters[ApiFindPageCategoryParameter::USER_TOKEN->value],
        );

        $this->validateDto($dto);
        return [$dto];
    }
}
