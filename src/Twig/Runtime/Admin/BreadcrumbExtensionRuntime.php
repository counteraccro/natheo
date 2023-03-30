<?php

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Breadcrumb, permet de générer les fils d'Arianes
 */

namespace App\Twig\Runtime\Admin;

use Twig\Extension\RuntimeExtensionInterface;

class BreadcrumbExtensionRuntime extends AppAdminExtensionRuntime implements RuntimeExtensionInterface
{

    /**
     * Génère le breadcrumb
     * @param array $elements Tableau contenant les elements suivants
     *  [key-translate => route] <br />
     * Le premier élément est généré automatiquement (dashboard)
     * @return string
     */
    public function getBreadcrumb(array $elements): string
    {
        $html = '';
        $nb = count($elements);
        $i = 1;
        foreach ($elements as $label => $route) {
            $lastLink = '';
            if ($nb - 1 === $i) {
                $lastLink = 'last-link';
            }

            if ($nb === $i) {
                $html .= '<li class="breadcrumb-item pl-0 active text-uppercase pl-4">' .
                    $this->translator->trans($label) . '</li>';
            } else {
                $html .= '<li class="breadcrumb-item pl-0 ' . $lastLink . '"><a href="' .
                    $this->router->generate($route) . '" class="text-uppercase">' . $this->translator->trans($label) .
                    '</a></li>';
            }
            $i++;
        }

        $lastLink = '';
        $pl3 = 'pl-3';
        if ($nb === 1) {
            $lastLink = 'last-link';
            $pl3 = '';
        }

        return '<div id="adm-breadcrumb">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb breadcrumb-arrow p-0 bg-light">
                                <li class="breadcrumb-item ' . $lastLink . '">
                                    <a href="' . $this->router->generate('admin_dashboard_index') .
                                        '" class="text-uppercase ' . $pl3 . '">
                                        ' . $this->translator->trans('global.dashboard') . '
                                    </a>
                                </li>
                                ' . $html . '
                            </ol>
                        </nav>
                  </div>';
    }
}
