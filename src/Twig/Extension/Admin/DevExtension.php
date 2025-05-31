<?php

namespace App\Twig\Extension\Admin;

use App\Twig\Runtime\Admin\DevExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class DevExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/3.x/advanced.html#automatic-escaping
            //new TwigFilter('getVersion', [DevExtensionRuntime::class, 'getVersion'], ['is_safe' => ['html']]),
            //new TwigFilter('getPhpInfo', [DevExtensionRuntime::class, 'getPhpInfo'], ['is_safe' => ['html']]),
        ];
    }

    public function getFunctions(): array
    {
        return [
           // new TwigFunction('function_name', [DevExtensionRuntime::class, 'doSomething']),
        ];
    }
}
