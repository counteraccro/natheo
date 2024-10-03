<?php
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Class qui va controller les paramètres reçu en fonction des paramètres attendu
 */

namespace App\Utils\Api;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Contracts\Translation\TranslatorInterface;

class ApiParametersParser
{

    /**
     * Tableau d'erreur
     * @var array
     */
    private $tabError = [];

    /**
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;

    /**
     * @param ContainerInterface $handlers
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        #[AutowireLocator([
            'translator' => TranslatorInterface::class,
        ])]
        private readonly ContainerInterface $handlers
    )
    {
        $this->translator = $this->handlers->get('translator');
    }

    /**
     * Parse les paramètres de l'API
     * @param array $refParameter
     * @param array $apiParameters
     * @return array
     */
    public function parse(array $refParameter, array $apiParameters): array
    {
        foreach ($refParameter as $parameterName => $parameterType) {

            if (!isset($apiParameters[$parameterName])) {
                $this->tabError[] = $this->translator->trans('api_errors.params.name.not.found', ['param' => $parameterName], domain: 'api_errors');
                continue;
            }

            switch ($parameterType) {
                case ApiParametersRef::TYPE_STRING:
                    $this->checkTypeString($parameterName, $apiParameters[$parameterName]);
                    break;
                default:
                    $this->tabError[] = $this->translator->trans('api_errors.params.type.not.found', ['param' => $parameterName], domain: 'api_errors');

            }
        }

        return $this->tabError;
    }

    /**
     * Vérifie si la valeur est de type string
     * @param $name
     * @param $value
     * @return void
     */
    private function checkTypeString($name, $value): void
    {
        if (!is_string($value)) {
            $this->tabError[] = $this->translator->trans('api_errors.params.not.string', ['param' => $name], domain: 'api_errors');
        }
    }
}