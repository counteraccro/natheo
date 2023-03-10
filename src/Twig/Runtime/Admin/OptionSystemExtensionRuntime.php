<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Permet de générer le formulaire de saisie des options systèmes
 */

namespace App\Twig\Runtime\Admin;

use App\Entity\Admin\OptionSystem;
use App\Service\Admin\OptionSystemService;
use App\Utils\Debug;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\RuntimeExtensionInterface;

class OptionSystemExtensionRuntime extends AppAdminExtensionRuntime implements RuntimeExtensionInterface
{

    /**
     * @var OptionSystemService
     */
    private OptionSystemService $optionSystemService;

    /**
     * Liste des options système récupéré depuis la base de donnée
     * @var array
     */
    private array $listOptionSystem;

    /**
     * Clé global du fichier de config
     * @var string
     */
    private $globalKey = '';

    /**
     * @param RouterInterface $router
     * @param TranslatorInterface $translator
     * @param OptionSystemService $optionSystemService
     */
    public function __construct(RouterInterface $router, TranslatorInterface $translator, OptionSystemService $optionSystemService)
    {
        $this->optionSystemService = $optionSystemService;
        parent::__construct($router, $translator);
    }

    /**
     * Point d'entrée pour la génération du formulaire des options systèmes
     * @return string
     * @throws Exception
     */
    public function getOptionSystem(): string
    {
        $optionsSystemConfig = $this->optionSystemService->getOptionsSystemConfig();
        $this->listOptionSystem = $this->optionSystemService->getAll();

        $this->globalKey = key($optionsSystemConfig);

        $categories = array_keys($optionsSystemConfig[$this->globalKey]);

        $html = $this->generateHeader($categories);
        $html .= $this->generateContent($categories, $optionsSystemConfig);

        return $html;
    }

    /**
     * Permet de générer les entêtes des catégories pour les options systèmes
     * @param array $categories
     * @return string
     */
    private function generateHeader(array $categories): string
    {
        $html = '<nav><div class="nav nav-pills mb-3" id="nav-tab-option-system" role="tablist">';
        foreach ($categories as $key => $category) {
            $active = '';
            if ($key === 0) {
                $active = 'active';
            }

            $html .= '<button class="nav-link ' . $active . '" id="nav-' . $key . '-tab" data-bs-toggle="tab" data-bs-target="#nav-' . $key . '" type="button" role="tab">
                    ' . $this->translator->trans($category) . '
                </button>';
        }
        $html .= '</div></nav>';

        return $html;
    }

    /**
     * Permet de générer le contenu du tab-content
     * @param array $categories
     * @param array $optionsSystemConfig
     * @return string
     */
    private function generateContent(array $categories, array $optionsSystemConfig): string
    {
        $html = '<div class="tab-content" id="nav-tab-option-system-content">';

        foreach ($categories as $key => $category) {
            $active = '';
            if ($key === 0) {
                $active = 'show active';
            }
            $html .= '<div class="tab-pane fade ' . $active . '" id="nav-' . $key . '" role="tabpanel" tabindex="0">';

            $html .= '<h5>' . $this->translator->trans($optionsSystemConfig[$this->globalKey][$category]['title']) . '</h5>';
            $html .= '<p>' . $this->translator->trans($optionsSystemConfig[$this->globalKey][$category]['description']) . '</p>';

            foreach ($optionsSystemConfig[$this->globalKey][$category]['options'] as $key => $element) {
                switch ($element['type']) {
                    case 'text' :
                        $html .= $this->generateInputText($key, $element);
                        break;
                    case 'boolean' :
                        $html .= $this->generateRadioButton($key, $element);
                        break;
                    case 'textarea':
                        $html .= $this->generateTextarea($key, $element);
                        break;
                    case 'select':
                        $html .= $this->generateSelect($key, $element);
                        break;
                    default:
                }
                $html .= '<br />';
            }
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Génère le champ de type input text
     * @param string $key
     * @param array $element
     * @return string
     */
    private function generateInputText(string $key, array $element): string
    {
        $require = $msg_error = $placeholder = '';
        if (isset($element['required'])) {
            $require = 'required="required"';
            $msg_error = $this->getError($key, $element);
        }

        if (isset($element['placeholder'])) {
            $placeholder = 'placeholder="' . $this->translator->trans($element['placeholder']) . '"';
        }

        $html = '<label for="' . $key . '" class="form-label">' . $this->translator->trans($element['label']) . '</label>
            <input type="text" class="form-control event-input" id="' . $key . '" ' . $require . ' ' . $placeholder . ' value="' . $this->getValueByKey($key) . '">' . $msg_error;

        $html .= $this->getSuccess($key, $element);
        $html .= $this->getSpinner($key);
        $html .= $this->getHelp($key, $element);

        return $html;
    }

    /**
     * Génère le champ de type radio button
     * @param string $key
     * @param array $element
     * @return string
     */
    private function generateRadioButton(string $key, array $element): string
    {
        $value = $this->getValueByKey($key);

        $checked = '';
        if($value === "1")
        {
            $checked = 'checked';
        }

        $html = '<div class="form-check form-switch">
            <input class="form-check-input event-input" type="checkbox" role="switch" id="' . $key . '" ' . $checked . '>
            <label class="form-check-label" for="' . $key . '">' . $this->translator->trans($element['label']) . '</label>';

        $html .= $this->getSuccess($key, $element);
        $html .= '</div>';

        $html .= $this->getSpinner($key);
        $html .= $this->getHelp($key, $element);


        return $html;
    }

    /**
     * Génère le champ de type textarea
     * @param string $key
     * @param array $element
     * @return string
     */
    private function generateTextarea(string $key, array $element): string
    {
        $require = $msg_error = $placeholder = '';
        if (isset($element['required'])) {
            $require = 'required="required"';
            $msg_error = $this->getError($key, $element);
        }

        if (isset($element['placeholder'])) {
            $placeholder = 'placeholder="' . $this->translator->trans($element['placeholder']) . '"';
        }

        $html = '<label for="' . $key . '" class="form-label">' . $this->translator->trans($element['label']) . '</label>
            <textarea class="form-control event-input" rows="5" id="' . $key . '" ' . $require . ' ' . $placeholder . '>' . $this->getValueByKey($key) . '</textarea>' . $msg_error;

        $html .= $this->getSuccess($key, $element);
        $html .= $this->getSpinner($key);
        $html .= $this->getHelp($key, $element);

        return $html;
    }

    /**
     * Génère le champ de type select
     * @param string $key
     * @param array $element
     * @return string
     */
    private function generateSelect(string $key, array $element): string
    {
        $html = '';

        return $html;
    }

    /**
     * Retourne un block help si celui-ci est demandé
     * @param String $key
     * @param array $element
     * @return string
     */
    private function getHelp(string $key, array $element): string
    {
        if (isset($element['help'])) {
            return '<div id="help-' . $key . '" class="form-text"><i class="bi bi-info-circle"></i> <i>' . $this->translator->trans($element['help']) . '</i></div>';
        }
        return '';
    }

    /**
     * Retourne le message de succès
     * @param string $key
     * @param array $element
     * @return string
     */
    private function getSuccess(string $key, array $element): string
    {
        if(isset($element['success'])) {
            return '<div id="success-' . $key . '" class="valid-feedback visually-hidden"><b><i class="bi bi-check-circle"></i>  ' . $this->translator->trans($element['success']) . '</b></div>';
        }
        return '<div id="success-' . $key . '" class="valid-feedback visually-hidden"><b><i class="bi bi-check-circle"></i>  ' . $this->translator->trans('options_system.default_msg_success') . '</b></div>';
    }

    /**
     * Retourne le message d'erreur
     * @param string $key
     * @param array $element
     * @return string
     */
    private function getError(string $key, array $element): string
    {
        if (isset($element['msg_error'])) {
            return '<div id="error-' . $key . '" class="invalid-feedback"><b><i class="bi bi-exclamation-circle"></i>  ' . $this->translator->trans($element['msg_error']) . '</b></div>';
        }
        return '<div id="error-' . $key . '" class="invalid-feedback"><b><i class="bi bi-exclamation-circle"></i>  ' . $this->translator->trans('options_system:default_msg_error') . '</b></div>';
    }

    /**
     * Retourne un spinner
     * @return string
     */
    private function getSpinner(string $key): string
    {
        return '<div id="spinner-' . $key . '" class="float-end visually-hidden"><div class="spinner-border spinner-border-sm text-primary" role="status">
                </div> <span class="text-primary"><i> &#8239;&#8239; ' . $this->translator->trans('global.loading_save') . '</i></span></div>';
    }

    /**
     * Retourne la valeur d'une option en fonction de sa clé
     * @param string $key
     * @return string
     */
    private function getValueByKey(string $key): string
    {
        foreach ($this->listOptionSystem as $optionSystem) {
            /* @var OptionSystem $optionSystem */
            if ($optionSystem->getKey() === $key) {
                return $optionSystem->getValue();
            }
        }
        return '';
    }
}
