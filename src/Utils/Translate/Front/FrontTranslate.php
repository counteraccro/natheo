<?php
/**
 * Class pour la génération des traductions pour les scripts vue du front
 * @author Gourdon Aymeric
 * @version 1.0
 */
namespace App\Utils\Translate\Front;

use App\Utils\Translate\AppTranslate;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class FrontTranslate extends AppTranslate
{

    public function __construct(#[AutowireLocator([
        'translator' => TranslatorInterface::class,
        'parameterBag' => ContainerBagInterface::class,
    ])] private readonly ContainerInterface $handlers)
    {
        parent::__construct($handlers);
    }

    /**
     * Traduction front
     * @return array[]
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getTranslate(): array
    {
        return [
            'footer' => $this->getTranslateFooter()
        ];
    }

    /**
     * Traduction footer
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getTranslateFooter(): array
    {
        /** @var ContainerBagInterface $parameterBag */
        $parameterBag = $this->handlers->get('parameterBag');

        return [
            'credit' => $this->translator->trans('front.footer.credit', ['year' => date('Y'), 'version' => $parameterBag->get('app.version')], 'front'),
            'templateVersion' => $this->translator->trans('front.footer.template.version', ['version' => $parameterBag->get('app.natheo_horizon.version')], 'front'),
            'adminLink' => $this->translator->trans('front.footer.admin.link', domain: 'front'),
            'githubLink' => $this->translator->trans('front.footer.github.link', domain: 'front'),
        ];
    }
}