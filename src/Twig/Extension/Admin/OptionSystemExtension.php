<?php

namespace App\Twig\Extension\Admin;

use App\Twig\Runtime\Admin\OptionSystemExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class OptionSystemExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/3.x/advanced.html#automatic-escaping
            new TwigFilter('filter_name', [OptionSystemExtensionRuntime::class, 'doSomething']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('option_system_form', [OptionSystemExtensionRuntime::class, 'getOptionSystem'], ['is_safe' => ['html']]),
        ];
    }
}
