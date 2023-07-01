<?php

namespace App\Twig\Extension\Admin\System;

use App\Twig\Runtime\Admin\System\OptionExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class OptionExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/3.x/advanced.html#automatic-escaping
            new TwigFilter('filter_name', [OptionExtensionRuntime::class, 'doSomething']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('option_system_form', [OptionExtensionRuntime::class, 'getOptionSystem'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction('option_user_form', [OptionExtensionRuntime::class, 'getOptionUser'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction('get_option_system_value_by_key', [OptionExtensionRuntime::class, 'getOptionValueByKey']),
        ];
    }
}
