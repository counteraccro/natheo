<?php

/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Permet de générer le formulaire de saisie des options systèmes et options users
 */

namespace App\Twig\Extension\Admin\System;

use App\Entity\Admin\System\OptionSystem;
use App\Entity\Admin\System\OptionUser;
use App\Service\Admin\System\OptionSystemService;
use App\Service\Admin\System\OptionUserService;
use App\Twig\Extension\Admin\AppAdminExtension;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Attribute\AsTwigFunction;

class OptionExtension extends AppAdminExtension
{
    /**
     * @var OptionSystemService
     */
    private OptionSystemService $optionSystemService;

    /**
     * @var OptionUserService
     */
    private OptionUserService $optionUserService;

    /**
     * Liste des options système récupéré depuis la base de donnée
     * @var array
     */
    private array $listOptionSystem;

    /**
     * Liste des options users
     * @var array
     */
    private array $listOptionUser;

    /**
     * Clé globale du fichier de config
     * @var string
     */
    private string $globalKey = '';

    /**
     * @param ContainerInterface $handlers
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        #[
            AutowireLocator([
                'translator' => TranslatorInterface::class,
                'router' => RouterInterface::class,
                'optionUserService' => OptionUserService::class,
                'optionSystemService' => OptionSystemService::class,
            ]),
        ]
        private readonly ContainerInterface $handlers,
    ) {
        $this->optionUserService = $this->handlers->get('optionUserService');
        $this->optionSystemService = $this->handlers->get('optionSystemService');
        parent::__construct($this->handlers);
    }

    /**
     * Point d'entrée pour la génération du formulaire des options systèmes
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[AsTwigFunction('option_system_form', isSafe: ['html'])]
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
     * Point d'entrée pour la génération du formulaire des options user
     * @return string
     * @throws Exception
     */
    #[AsTwigFunction('option_user_form', isSafe: ['html'])]
    public function getOptionUser(): string
    {
        $optionUserConfig = $this->optionUserService->getOptionsUserConfig();
        $this->listOptionSystem = $this->optionUserService->getAll();

        $this->globalKey = key($optionUserConfig);

        $categories = array_keys($optionUserConfig[$this->globalKey]);

        $html = $this->generateHeader($categories);
        $html .= $this->generateContent($categories, $optionUserConfig);

        return $html;
    }

    /**
     * Permet de générer les entêtes des catégories pour les options systèmes
     * @param array $categories
     * @return string
     */
    private function generateHeader(array $categories): string
    {
        $html = '

<div class="mb-4 mt-4 border-b border-gray-200 dark:border-gray-700">
    <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="default-styled-tab" data-tabs-toggle="#default-styled-tab-content" data-tabs-active-classes="text-[var(--primary)] hover:text-[var(--primary-hover)] border-[var(--primary)] bg-[var(--primary-lighter)]" data-tabs-inactive-classes="dark:border-transparent text-gray-500 hover:text-gray-600 dark:text-gray-400 border-gray-100 hover:border-gray-300 dark:border-gray-700 dark:hover:text-gray-300" role="tablist">
        <li class="me-2" role="presentation">
            <button class="inline-block ps-4 pt-2 pe-4 pb-2 border-b-2 rounded-t-sm" id="profile-styled-tab" data-tabs-target="#styled-profile" type="button" role="tab" aria-controls="profile" aria-selected="false">Profile</button>
        </li>
        <li class="me-2" role="presentation">
            <button class="inline-block ps-4 pt-2 pe-4 pb-2 border-b-2 rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300" id="dashboard-styled-tab" data-tabs-target="#styled-dashboard" type="button" role="tab" aria-controls="dashboard" aria-selected="false">Dashboard</button>
        </li>
        <li class="me-2" role="presentation">
            <button class="inline-block ps-4 pt-2 pe-4 pb-2 border-b-2 rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300" id="settings-styled-tab" data-tabs-target="#styled-settings" type="button" role="tab" aria-controls="settings" aria-selected="false">Settings</button>
        </li>
        <li role="presentation">
            <button class="inline-block ps-4 pt-2 pe-4 pb-2 border-b-2 rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300" id="contacts-styled-tab" data-tabs-target="#styled-contacts" type="button" role="tab" aria-controls="contacts" aria-selected="false">Contacts</button>
        </li>
    </ul>
</div>
<div id="default-styled-tab-content">
    <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800" id="styled-profile" role="tabpanel" aria-labelledby="profile-tab">
        <p class="text-sm text-gray-500 dark:text-gray-400">This is some placeholder content the <strong class="font-medium text-gray-800 dark:text-white">Profile tabs associated content</strong>. Clicking another tab will toggle the visibility of this one for the next. The tab JavaScript swaps classes to control the content visibility and styling.</p>
    </div>
    <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800" id="styled-dashboard" role="tabpanel" aria-labelledby="dashboard-tab">
        <p class="text-sm text-gray-500 dark:text-gray-400">This is some placeholder content the <strong class="font-medium text-gray-800 dark:text-white">Dashboard tabs associated content</strong>. Clicking another tab will toggle the visibility of this one for the next. The tab JavaScript swaps classes to control the content visibility and styling.</p>
    </div>
    <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800" id="styled-settings" role="tabpanel" aria-labelledby="settings-tab">
        <p class="text-sm text-gray-500 dark:text-gray-400">This is some placeholder content the <strong class="font-medium text-gray-800 dark:text-white">Settings tabs associated content</strong>. Clicking another tab will toggle the visibility of this one for the next. The tab JavaScript swaps classes to control the content visibility and styling.</p>
    </div>
    <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800" id="styled-contacts" role="tabpanel" aria-labelledby="contacts-tab">
        <p class="text-sm text-gray-500 dark:text-gray-400">This is some placeholder content the <strong class="font-medium text-gray-800 dark:text-white">Contacts tabs associated content</strong>. Clicking another tab will toggle the visibility of this one for the next. The tab JavaScript swaps classes to control the content visibility and styling.</p>
    </div>
</div>


<div class="nmb-4 border-b border-gray-200 dark:border-gray-700" id="nav-tab-option-system">
    <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="default-styled-tab" data-tabs-toggle="#default-styled-tab-content" data-tabs-active-classes="text-purple-600 hover:text-purple-600 dark:text-purple-500 dark:hover:text-purple-500 border-purple-600 dark:border-purple-500" data-tabs-inactive-classes="dark:border-transparent text-gray-500 hover:text-gray-600 dark:text-gray-400 border-gray-100 hover:border-gray-300 dark:border-gray-700 dark:hover:text-gray-300" role="tablist">';
        foreach ($categories as $key => $category) {
            $active = '';
            if ($key === 0) {
                $active = 'active';
            }

            $html .=
                '<button class="nav-link ' .
                $active .
                '" id="nav-' .
                $key .
                '-tab" data-bs-toggle="tab"
            data-bs-target="#nav-' .
                $key .
                '" type="button" role="tab">
                    ' .
                $this->translator->trans($category) .
                '
                </button>';
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * Permet de générer le contenu du tab-content
     * @param array $categories
     * @param array $optionsConfig
     * @return string
     */
    private function generateContent(array $categories, array $optionsConfig): string
    {
        $html = '<div class="tab-content" id="nav-tab-option-system-content">';

        foreach ($categories as $key => $category) {
            $active = '';
            if ($key === 0) {
                $active = 'show active';
            }
            $html .= '<div class="tab-pane fade ' . $active . '" id="nav-' . $key . '" role="tabpanel" tabindex="0">';

            $html .= '<h5>' . $this->translator->trans($optionsConfig[$this->globalKey][$category]['title']) . '</h5>';
            $html .=
                '<p>' . $this->translator->trans($optionsConfig[$this->globalKey][$category]['description']) . '</p>';

            foreach ($optionsConfig[$this->globalKey][$category]['options'] as $keyOption => $element) {
                switch ($element['type']) {
                    case 'text':
                        $html .= $this->generateInputText($keyOption, $element);
                        break;
                    case 'boolean':
                        $html .= $this->generateRadioButton($keyOption, $element);
                        break;
                    case 'textarea':
                        $html .= $this->generateTextarea($keyOption, $element);
                        break;
                    case 'select':
                        $html .= $this->generateSelect($keyOption, $element);
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
        $require = $msgError = $placeholder = '';
        if (isset($element['required'])) {
            $require = 'required="required"';
            $msgError = $this->getError($key, $element);
        }

        if (isset($element['placeholder'])) {
            $placeholder = 'placeholder="' . $this->translator->trans($element['placeholder']) . '"';
        }

        $disabled = '';
        if (isset($element['disabled'])) {
            $disabled = 'disabled';
        }

        $html =
            '<label for="' .
            $key .
            '" class="form-label">
            ' .
            $this->translator->trans($element['label']) .
            '</label>
            <input type="text" class="form-control no-control event-input" id="' .
            $key .
            '" ' .
            $require .
            ' ' .
            $placeholder .
            ' value="' .
            $this->getValueByKey($key) .
            '" ' .
            $disabled .
            '>' .
            $msgError;

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
        if ($value === '1') {
            $checked = 'checked';
        }

        $disabled = '';
        if (isset($element['disabled'])) {
            $disabled = 'disabled';
        }

        $html =
            '<div class="form-check form-switch">
            <input class="form-check-input no-control event-input" type="checkbox" role="switch" ' .
            $disabled .
            '
                id="' .
            $key .
            '" ' .
            $checked .
            '>
            <label class="form-check-label" for="' .
            $key .
            '">
                ' .
            $this->translator->trans($element['label']) .
            '</label>';

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
        $require = $msgError = $placeholder = '';
        if (isset($element['required'])) {
            $require = 'required="required"';
            $msgError = $this->getError($key, $element);
        }

        if (isset($element['placeholder'])) {
            $placeholder = 'placeholder="' . $this->translator->trans($element['placeholder']) . '"';
        }

        $disabled = '';
        if (isset($element['disabled'])) {
            $disabled = 'disabled';
        }

        $html =
            '<label for="' .
            $key .
            '" class="form-label">' .
            $this->translator->trans($element['label']) .
            '</label>
            <textarea class="form-control no-control event-input" rows="5"
            id="' .
            $key .
            '" ' .
            $require .
            ' ' .
            $placeholder .
            ' ' .
            $disabled .
            '>' .
            $this->getValueByKey($key) .
            '</textarea>' .
            $msgError;

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
        $value = $this->getValueByKey($key);
        $tab = explode('|', $element['list_value']);

        $selectStyle = '';
        $optionHtml = '';

        $disabled = '';
        if (isset($element['disabled'])) {
            $disabled = 'disabled';
        }

        foreach ($tab as $select) {
            $option = explode(':', $select);
            $selected = '';
            if ($value === $option[0]) {
                $selected = 'selected';
            }

            if (!str_starts_with($option[1], '&#')) {
                $optionLabel = $this->translator->trans($option[1]);
            } else {
                $optionLabel = $option[1];
                $selectStyle = 'style="font-family: bootstrap-icons"';
            }

            $optionHtml .= '<option value="' . $option[0] . '" ' . $selected . '>' . $optionLabel . '</option>';
        }

        $html =
            '<label for="' .
            $key .
            '" class="form-label">
        ' .
            $this->translator->trans($element['label']) .
            '</label>
            <select id="' .
            $key .
            '" class="form-select no-control event-input" ' .
            $selectStyle .
            ' ' .
            $disabled .
            '>
            ' .
            $optionHtml .
            '</select>';

        $html .= $this->getSuccess($key, $element);
        $html .= $this->getSpinner($key);
        $html .= $this->getHelp($key, $element);

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
            return '<div id="help-' .
                $key .
                '" class="form-text"><i class="bi bi-info-circle"></i> <i>' .
                $this->translator->trans($element['help']) .
                '</i></div>';
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
        if (isset($element['success'])) {
            return '<div id="success-' .
                $key .
                '" class="valid-feedback visually-hidden"><b>
                <i class="bi bi-check-circle"></i>  ' .
                $this->translator->trans($element['success']) .
                '</b></div>';
        }
        return '<div id="success-' .
            $key .
            '" class="valid-feedback visually-hidden"><b>
            <i class="bi bi-check-circle"></i>
            ' .
            $this->translator->trans('options_system.default_msg_success') .
            '</b></div>';
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
            return '<div id="error-' .
                $key .
                '" class="invalid-feedback"><b>
           <i class="bi bi-exclamation-circle"></i>  ' .
                $this->translator->trans($element['msg_error']) .
                '</b></div>';
        }
        return '<div id="error-' .
            $key .
            '" class="invalid-feedback"><b>
        <i class="bi bi-exclamation-circle"></i> ' .
            $this->translator->trans('options_system.default_msg_error') .
            '</b></div>';
    }

    /**
     * Retourne un spinner
     * @param string $key
     * @return string
     */
    private function getSpinner(string $key): string
    {
        return '<div id="spinner-' .
            $key .
            '" class="float-end visually-hidden">
            <div class="spinner-border spinner-border-sm text-primary" role="status">
                </div> <span class="text-primary"><i> &#8239;&#8239; ' .
            $this->translator->trans('global.loading_save') .
            '</i></span></div>';
    }

    /**
     * Retourne la valeur d'une option en fonction de sa clé
     * @param string $key
     * @return string
     */
    private function getValueByKey(string $key): string
    {
        if (!empty($this->optionSystemService)) {
            foreach ($this->listOptionSystem as $optionSystem) {
                /* @var OptionSystem $optionSystem */
                if ($optionSystem->getKey() === $key) {
                    return $optionSystem->getValue();
                }
            }
        } else {
            foreach ($this->listOptionUser as $optionUser) {
                /* @var OptionUser $optionUser */
                if ($optionUser->getKey() === $key) {
                    return $optionUser->getValue();
                }
            }
        }
        return '';
    }

    /**
     * Retourne la valeur de l'option en fonction de sa clé
     * @param string $key
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[AsTwigFunction('get_option_system_value_by_key')]
    public function getOptionValueByKey(string $key): string
    {
        return $this->optionSystemService->getValueByKey($key);
    }
}
