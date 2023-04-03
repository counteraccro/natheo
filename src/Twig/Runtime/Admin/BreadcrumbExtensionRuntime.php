<?php

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Breadcrumb, permet de générer les fils d'Arianes
 */

namespace App\Twig\Runtime\Admin;

use App\Service\Admin\Breadcrumb;
use Doctrine\ORM\Query\AST\BetweenExpression;
use Twig\Extension\RuntimeExtensionInterface;

class BreadcrumbExtensionRuntime extends AppAdminExtensionRuntime implements RuntimeExtensionInterface
{

    /**
     * Génère le breadcrumb
     * @param array $elements Tableau contenant les elements suivants <br />
     *  [domain => domaine pour les traductions] <br />
     *  [breadcrumb][key-translate => route] <br />
     * Le premier élément est généré automatiquement (dashboard)
     * @return string
     */
    public function getBreadcrumb(array $elements): string
    {
        $domain = $elements[Breadcrumb::DOMAIN];
        $html = '';
        $nb = count($elements[Breadcrumb::BREADCRUMB]);
        $i = 1;
        foreach ($elements[Breadcrumb::BREADCRUMB] as $label => $route) {
            $lastLink = '';
            if ($nb - 1 === $i) {
                $lastLink = 'last-link';
            }

            if ($nb === $i) {
                $html .= '<li class="breadcrumb-item pl-0 active text-uppercase pl-4">' .
                    $this->translator->trans($label, domain: $domain) . '</li>';
            } else {
                $html .= '<li class="breadcrumb-item pl-0 ' . $lastLink . '"><a href="' .
                    $this->router->generate($route) . '" class="text-uppercase">' . $this->translator
                        ->trans($label, domain: $domain) .
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
                                        ' . $this->translator->trans('global.dashboard', domain: 'global') . '
                                    </a>
                                </li>
                                ' . $html . '
                            </ol>
                        </nav>
                  </div>';
    }
}
