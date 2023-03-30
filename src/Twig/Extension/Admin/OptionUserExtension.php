<?php

namespace App\Twig\Extension\Admin;

use App\Twig\Runtime\Admin\OptionUserExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class OptionUserExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/3.x/advanced.html#automatic-escaping
            new TwigFilter('filter_name', [OptionUserExtensionRuntime::class, 'doSomething']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_option_user_value_by_key',
                [OptionUserExtensionRuntime::class, 'getOptionValueByKey']
            ),
        ];
    }
}
