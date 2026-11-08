<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Permet de résoudre la validation de l'objet ApiFindPageDto
 */

namespace App\Resolver\Api\Content\Page;

use App\Dto\Api\Content\Page\ApiFindPageDto;
use App\Resolver\Api\AppApiResolver;
use App\Enum\Api\Parameters\Content\Page\ApiFindPageParameter;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ApiFindPageResolver extends AppApiResolver implements ValueResolverInterface
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
        if (!is_a($argumentType, ApiFindPageDto::class, true)) {
            return [];
        }

        $tabParameters = ApiFindPageParameter::PARAMS_REF;

        foreach ($tabParameters as $parameter => $value) {
            if (in_array($parameter, ['show_menus', 'show_tags', 'show_statistiques'])) {
                $value = $request->query->get($parameter, true);
            } elseif ($parameter === ApiFindPageParameter::PREVIEW->value) {
                $value = $request->query->get($parameter, false);
            } else {
                $value = $request->query->get($parameter, '');
            }

            if ($parameter === ApiFindPageParameter::MENU_POSITION->value && !empty($value)) {
                $tab = explode(',', $value);
                $value = [];
                foreach ($tab as $tmp) {
                    $value[intval($tmp)] = intval($tmp);
                }
            }

            // Accepte true/false, 1/0, yes/no, on/off ; toute autre valeur est refusée
            if (in_array($parameter, ['show_menus', 'show_tags', 'show_statistiques', 'preview'])) {
                $value = is_bool($value) ? $value : filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if ($value === null) {
                    throw new HttpException(
                        Response::HTTP_BAD_REQUEST,
                        $this->handlers
                            ->get('translator')
                            ->trans('api_errors.params.not.boolean', ['param' => $parameter], domain: 'api_errors'),
                    );
                }
            }

            if (!is_bool($value) && empty($value) && isset(ApiFindPageParameter::PARAMS_DEFAULT_VALUE[$parameter])) {
                $value = ApiFindPageParameter::PARAMS_DEFAULT_VALUE[$parameter];
            }

            $tabParameters[$parameter] = $value;
        }

        $tabParameters[ApiFindPageParameter::USER_TOKEN->value] = $this->getUserToken($request);

        $dto = new ApiFindPageDto(
            $tabParameters[ApiFindPageParameter::SLUG->value],
            $tabParameters[ApiFindPageParameter::LOCALE->value],
            $tabParameters[ApiFindPageParameter::SHOW_MENUS->value],
            $tabParameters[ApiFindPageParameter::SHOW_TAGS->value],
            $tabParameters[ApiFindPageParameter::SHOW_STATISTIQUES->value],
            $tabParameters[ApiFindPageParameter::MENU_POSITION->value],
            $tabParameters[ApiFindPageParameter::USER_TOKEN->value],
            $tabParameters[ApiFindPageParameter::PREVIEW->value],
        );

        $this->validateDto($dto);
        return [$dto];
    }
}
