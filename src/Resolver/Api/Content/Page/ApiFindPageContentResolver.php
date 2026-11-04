<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Permet de résoudre la validation de l'objet ApiFindPageContent
 */

namespace App\Resolver\Api\Content\Page;

use App\Dto\Api\Content\Page\ApiFindPageContentDto;
use App\Resolver\Api\AppApiResolver;
use App\Enum\Api\Parameters\Content\Page\ApiFindPageContentParameter;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class ApiFindPageContentResolver extends AppApiResolver implements ValueResolverInterface
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
        if (!is_a($argumentType, ApiFindPageContentDto::class, true)) {
            return [];
        }

        $tabParameters = ApiFindPageContentParameter::PARAMS_REF;

        foreach ($tabParameters as $parameter => $value) {
            $value = $request->query->get($parameter, '');

            if (empty($value) && isset(ApiFindPageContentParameter::PARAMS_DEFAULT_VALUE[$parameter])) {
                $value = ApiFindPageContentParameter::PARAMS_DEFAULT_VALUE[$parameter];
            }
            $tabParameters[$parameter] = $value;
        }

        $tabParameters[ApiFindPageContentParameter::USER_TOKEN->value] = $this->getUserToken($request);

        $dto = new ApiFindPageContentDto(
            intval($tabParameters[ApiFindPageContentParameter::ID->value]),
            $tabParameters[ApiFindPageContentParameter::LOCALE->value],
            intval($tabParameters[ApiFindPageContentParameter::PAGE->value]),
            intval($tabParameters[ApiFindPageContentParameter::LIMIT->value]),
            $tabParameters[ApiFindPageContentParameter::USER_TOKEN->value],
        );

        $this->validateDto($dto);
        return [$dto];
    }
}
