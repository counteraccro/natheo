<?php
/**
 * Class Globale pour la génération des traductions pour les scripts vue
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Utils\Translate;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Contracts\Translation\TranslatorInterface;

class AppTranslate
{
    /**
     * @var TranslatorInterface
     */
    protected TranslatorInterface $translator;

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        #[
            AutowireLocator([
                'translator' => TranslatorInterface::class,
            ]),
        ]
        private readonly ContainerInterface $handlers,
    ) {
        $this->translator = $this->handlers->get('translator');
    }
}
