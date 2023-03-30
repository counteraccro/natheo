<?php

namespace App\Twig\Extension\Admin;

use App\Twig\Runtime\Admin\BreadcrumbExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class BreadcrumbExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/3.x/advanced.html#automatic-escaping
            //new TwigFilter('filter_name', [BreadcrumbExtensionRuntime::class, 'doSomething']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('breadcrumb', [BreadcrumbExtensionRuntime::class, 'getBreadcrumb'],
                ['is_safe' =>['html']]
            ),
        ];
    }
}
