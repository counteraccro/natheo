<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Permet de résoudre la validation de l'objet ApiFindPageTagDto
 */

namespace App\Resolver\Api\Content\Page;

use App\Dto\Api\Content\Page\ApiFindPageTagDto;
use App\Resolver\Api\AppApiResolver;
use App\Enum\Api\Parameters\Content\Page\ApiFindPageCategoryParameter;
use App\Enum\Api\Parameters\Content\Page\ApiFindPageTagParameter;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class ApiFindPageTagResolver extends AppApiResolver implements ValueResolverInterface
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
        if (!is_a($argumentType, ApiFindPageTagDto::class, true)) {
            return [];
        }

        $tabParameters = ApiFindPageTagParameter::PARAMS_REF;

        foreach ($tabParameters as $parameter => $value) {
            $value = $request->query->get($parameter, '');

            if (empty($value) && isset(ApiFindPageTagParameter::PARAMS_DEFAULT_VALUE[$parameter])) {
                $value = ApiFindPageTagParameter::PARAMS_DEFAULT_VALUE[$parameter];
            }
            $tabParameters[$parameter] = $value;
        }

        $tabParameters[ApiFindPageCategoryParameter::USER_TOKEN->value] = $this->getUserToken($request);

        $dto = new ApiFindPageTagDto(
            $tabParameters[ApiFindPageTagParameter::TAG->value],
            $tabParameters[ApiFindPageTagParameter::LOCALE->value],
            intval($tabParameters[ApiFindPageTagParameter::PAGE->value]),
            intval($tabParameters[ApiFindPageTagParameter::LIMIT->value]),
            $tabParameters[ApiFindPageTagParameter::USER_TOKEN->value],
        );

        $this->validateDto($dto);
        return [$dto];
    }
}
