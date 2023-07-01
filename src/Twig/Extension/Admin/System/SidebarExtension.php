<?php

namespace App\Twig\Extension\Admin\System;

use App\Twig\Runtime\Admin\System\SidebarExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SidebarExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/3.x/advanced.html#automatic-escaping
            //new TwigFilter('doSomething', [SidebarExtensionRuntime::class, 'doSomething', ['is_safe' => ['html']]]),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('getSidebar', [SidebarExtensionRuntime::class, 'getSidebar'], ['is_safe' => ['html']]),
        ];
    }
}
