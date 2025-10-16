<?php

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Breadcrumb, permet de générer les fils d'Arianes
 */

namespace App\Twig\Extension\Admin;

use App\Enum\Admin\Global\Breadcrumb;
use Twig\Attribute\AsTwigFunction;

class BreadcrumbExtension extends AppAdminExtension
{
    /**
     * Génère le breadcrumb
     * @param array $elements Tableau contenant les elements suivants <br />
     *  [domain => domaine pour les traductions] <br />
     *  [breadcrumb][key-translate => route] <br />
     * Le premier élément est généré automatiquement (dashboard)
     * @return string
     */
    #[AsTwigFunction('breadcrumb', isSafe: ['html'])]
    public function getBreadcrumb(array $elements): string
    {
        $domain = $elements[Breadcrumb::DOMAIN->value];
        $html = '';
        $nb = count($elements[Breadcrumb::BREADCRUMB->value]);
        $i = 1;

        $html =
            '<nav class="flex mb-4" aria-label="Breadcrumb">
  <ol class="inline-flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse">
    <li class="inline-flex items-center">
      <a href="' .
            $this->router->generate('admin_dashboard_index') .
            '" class="inline-flex items-center text-sm font-medium link">
        <svg class="w-3 h-3 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
          <path d="m19.707 9.293-2-2-7-7a1 1 0 0 0-1.414 0l-7 7-2 2a1 1 0 0 0 1.414 1.414L2 10.414V18a2 2 0 0 0 2 2h3a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1h3a2 2 0 0 0 2-2v-7.586l.293.293a1 1 0 0 0 1.414-1.414Z"/>
        </svg>
        ' .
            $this->translator->trans('global.dashboard', domain: 'global') .
            '
      </a>
    </li>';

        foreach ($elements[Breadcrumb::BREADCRUMB->value] as $label => $route) {
            $html .= '<li>
      <div class="flex items-center">
        <svg class="rtl:rotate-180 block w-3 h-3 mx-1 text-gray-400 " aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
        </svg>';

            if ($nb === $i) {
                $html .=
                    '<span class="ms-1 text-sm font-medium text-gray-500 md:ms-2 dark:text-gray-400">' .
                    $this->translator->trans($label, domain: $domain) .
                    '</span>';
            } else {
                $html .=
                    '<a href="' .
                    $this->router->generate($route) .
                    '" class="ms-1 text-sm font-medium link">' .
                    $this->translator->trans($label, domain: $domain) .
                    '</a>';
            }

            $html .= '</div></li>';
            $i++;
        }

        $html .= '</ol></nav>';

        /*foreach ($elements[Breadcrumb::BREADCRUMB->value] as $label => $route) {
            $lastLink = '';
            if ($nb - 1 === $i) {
                $lastLink = 'last-link';
            }

            if ($nb === $i) {
                $html .=
                    '<li class="breadcrumb-item pl-0 active text-uppercase pl-4">' .
                    $this->translator->trans($label, domain: $domain) .
                    '</li>';
            } else {
                $html .=
                    '<li class="breadcrumb-item pl-0 ' .
                    $lastLink .
                    '"><a href="' .
                    $this->router->generate($route) .
                    '" class="text-uppercase">' .
                    $this->translator->trans($label, domain: $domain) .
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
                                <li class="breadcrumb-item ' .
            $lastLink .
            '">
                                    <a href="' .
            $this->router->generate('admin_dashboard_index') .
            '" class="text-uppercase ' .
            $pl3 .
            '">
                                        ' .
            $this->translator->trans('global.dashboard', domain: 'global') .
            '
                                    </a>
                                </li>
                                ' .
            $html .
            '
                            </ol>
                        </nav>
                  </div>';*/

        return $html;
    }
}
