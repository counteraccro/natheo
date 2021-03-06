<?php
/**
 * Fonctions globales pour les vues
 * @author Gourdon Aymeric
 * @version 1.0
 * @package App\Twig\Admin\System
 */

namespace App\Twig\Admin\System;

use App\Service\Admin\System\OptionService;
use App\Twig\Admin\AppExtension;
use Symfony\Component\Validator\Constraints\Date;
use Twig\Extension\RuntimeExtensionInterface;

class GlobalFunctionTwig extends AppExtension implements RuntimeExtensionInterface
{
    /**
     * Permet de générer un champ caché pour l'édition directement depuis un tableau
     * @param string $value
     * @param string $url
     * @return string
     */
    public function generateHiddenInput(string $value, string $url): string
    {
        $id = mt_rand();
        $html = '<span class="span-input-hidden">
                    <span class="txt-input-switch" id="label-' . $id . '">' . $value . '</span>
                    <span class="float-end text-success" id="success-label-' . $id . '" style="display: none"><i class="fa fa-check"></i> ' . $this->translator->trans('admin_system#Mise à jour réussi') . '</span>
                     <span id="input-label-' . $id . '" style="display: none">
                            <textarea class="form-control">' . $value . '</textarea>
                            <div class="btn btn-sm btn-primary btn-submit-input-switch mt-1" data-id="label-' . $id . '" data-url="' . $url . '"><i class="fa fa-check-circle"></i> ' . $this->translator->trans("admin_system#Valider") . '</div>
                            <div class="btn btn-sm btn-secondary btn-reset-input-switch mt-1 ms-1" data-id="label-' . $id . '"><i class="fa fa-undo"></i> ' . $this->translator->trans("admin_system#Annuler") . '</div>
                     </span>
                 </span>';
        return $html;
    }

    /**
     * Permet de générer un champ de recherche générique pour une recherche simple
     * @param array $fields
     * @param string $divId
     * @return string
     */
    public function generateSearchInput(array $fields, string $divId, string $keySession): string
    {

        $filter = $this->session->get($keySession);
        $value = '';
        $field = 'all';
        $strBtnSearch = $this->translator->trans('admin_system#Rechercher');
        $showBtnReset = "display: none";
        if ($filter != null) {
            $showBtnReset = '';
            $value = $filter['value'];
            $field = $filter['field'];
            if ($field != 'all') {
                $strBtnSearch = $this->translator->trans('admin_system#Rechercher sur le champ ') . $field;
            }
        }

        $id = mt_rand();

        $html = '<div class="input-group" id="' . $id . '">
                  <input type="text" class="form-control" id="input-search" value="' . $value . '" placeholder="' . $this->translator->trans('admin_system#Recherche...') . '">
                  <button type="button" class="btn btn-primary btn-search" data-id="' . $divId . '" data-value="' . $field . '" data-reset="' . $this->translator->trans('admin_system#Rechercher') . '" data-text="' . $this->translator->trans('admin_system#Rechercher sur le champ ') . '">' . $strBtnSearch . '</button>
                  <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="visually-hidden">Toggle Dropdown</span>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end">';

        foreach ($fields as $field => $label) {
            $html .= '<li><a class="dropdown-item" href="#" data-value="' . $field . '">' . $this->translator->trans($label) . '</a></li>';
        }

        $html .= '
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#" data-value="all">' . $this->translator->trans('admin_system#Rechercher sur tout') . '</a></li>
                  </ul>
                   <button class="btn btn-secondary" type="button" id="btn-reset-search" style="' . $showBtnReset . '">' . $this->translator->trans('admin_system#Annuler') . '</button>
                </div>';

        $html .= '<script>
        $( document ).ready(function() {
            System.EventSearch("#' . $id . '")
        });
    </script>';

        return $html;
    }

    /**
     * En fonction de l'option GO_ADM_CONFIRM_LEAVE affiche ou non le script JS de confirmation avant de quitter
     * une page avec un formulaire
     */
    public function checkBeforLeaveJS(): string
    {
        if ($this->optionService->getOptionConfirmLeave() === OptionService::GO_ADM_CONFIRM_LEAVE_YES_VALUE) {
            return "System.CheckBeforeLeave();";
        }

        return "";
    }

    /**
     * Vérifie si l'accès est autorisé pour la route envoyée en paramètre
     * @param string $route
     * @return bool
     */
    public function isGranted(string $route): bool
    {
        return $this->accessService->isGranted($route);
    }

    /**
     * Affiche correctement les dates du footer pour la création du projet
     */
    public function dateFooter(): string
    {
        $format = '%B %Y';
        $dateBegin = new \DateTime('10/01/2021');
        $dateEnd = new \DateTime();

        $return = ucfirst($this->dateService->getDateFormatTranslate($dateBegin, $format));
        $return .= ' - ' .  ucfirst($this->dateService->getDateFormatTranslate($dateEnd, $format));

        return $return;
    }

}