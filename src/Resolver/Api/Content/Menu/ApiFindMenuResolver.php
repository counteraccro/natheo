<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Permet de résoudre la validation de l'objet ApiAuthUserDto
 */

namespace App\Resolver\Api\Content\Menu;

use App\Dto\Api\Content\Menu\ApiFindMenuDto;
use App\Resolver\Api\AppApiResolver;
use App\Enum\Api\Parameters\Content\Menu\ApiFindMenuParameter;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

class ApiFindMenuResolver extends AppApiResolver implements ValueResolverInterface
{
    /**
     * Permet de mapper ApiAuthUserDto avec Request
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
        if (!is_a($argumentType, ApiFindMenuDto::class, true)) {
            return [];
        }

        $tabParameters = ApiFindMenuParameter::PARAMS_REF;
        foreach ($tabParameters as $parameter => $value) {
            $value = $request->query->get($parameter, '');
            if (empty($value) && isset(ApiFindMenuParameter::PARAMS_DEFAULT_VALUE[$parameter])) {
                $value = ApiFindMenuParameter::PARAMS_DEFAULT_VALUE[$parameter];
            }
            $tabParameters[$parameter] = $value;
        }

        $this->checkParameters($tabParameters);

        $tabParameters[ApiFindMenuParameter::USER_TOKEN->value] = $this->getUserToken($request);

        $dto = new ApiFindMenuDto(
            intval($tabParameters[ApiFindMenuParameter::ID->value]),
            $tabParameters[ApiFindMenuParameter::PAGE_SLUG->value],
            intval($tabParameters[ApiFindMenuParameter::POSITION->value]),
            $tabParameters[ApiFindMenuParameter::LOCALE->value],
            $tabParameters[ApiFindMenuParameter::USER_TOKEN->value],
        );

        $this->validateDto($dto);
        return [$dto];
    }

    private function checkParameters(array $parameters): void
    {
        /** @var TranslatorInterface $translator */
        $translator = $this->handlers->get('translator');

        // Cas id et page_slug sont vides (n'existe pas dans la query)
        if (
            empty($parameters[ApiFindMenuParameter::ID->value]) &&
            empty($parameters[ApiFindMenuParameter::PAGE_SLUG->value])
        ) {
            throw new HttpException(
                Response::HTTP_BAD_REQUEST,
                $translator->trans('api_errors.find.menu.not.id.slug.together', domain: 'api_errors'),
            );
        }

        // Cas id et page_slug ensemble
        if (
            !empty($parameters[ApiFindMenuParameter::ID->value]) &&
            !empty($parameters[ApiFindMenuParameter::PAGE_SLUG->value])
        ) {
            throw new HttpException(
                Response::HTTP_BAD_REQUEST,
                $translator->trans('api_errors.find.menu.id.slug.together', domain: 'api_errors'),
            );
        }
    }
}
