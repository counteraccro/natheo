<?php

declare(strict_types=1);

/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Permet de générer le formulaire de saisie des options systèmes et options users
 */

namespace App\Twig\Extension\Admin\System;

use App\Entity\Admin\System\OptionSystem;
use App\Service\Admin\System\OptionSystemService;
use App\Service\Admin\System\OptionUserService;
use App\Service\Global\SupportedLocales;
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
     * Liste des options (système ou user) récupérées depuis la base de donnée
     * @var array
     */
    private array $listOptionSystem;

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
                'supportedLocales' => SupportedLocales::class,
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
        $html = '<div class="mb-4 mt-4 border-b border-gray-200 dark:border-gray-700" id="nav-tab-option-system">
    <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="default-styled-tab" data-tabs-toggle="#nav-tab-option-system-content" data-tabs-active-classes="text-[var(--primary)] hover:text-[var(--primary-hover)] border-[var(--primary)] bg-[var(--primary-lighter)]" data-tabs-inactive-classes="dark:border-transparent text-gray-500 hover:text-gray-600 dark:text-gray-400 border-gray-100 hover:border-gray-300 dark:border-gray-700 dark:hover:text-gray-300" role="tablist">';
        foreach ($categories as $key => $category) {
            $html .=
                '<li class="me-2" role="presentation"><button class="inline-block ps-4 pt-2 pe-4 pb-2 border-b-2 rounded-t-sm cursor-pointer" id="nav-' .
                $key .
                '-tab" data-tabs-target="#tab-' .
                $key .
                '" type="button" role="tab" aria-controls="' .
                $this->translator->trans($category) .
                '" aria-selected="false">
                    ' .
                $this->translator->trans($category) .
                '
                </button></li>';
        }
        $html .= '</ul></div>';

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
        $html = '<div id="nav-tab-option-system-content" class="card">';

        foreach ($categories as $key => $category) {
            $html .=
                '<div class="hidden" id="tab-' .
                $key .
                '" role="tabpanel" aria-labelledby="profile-tab"' .
                ($this->isMultilingual($optionsConfig[$this->globalKey][$category])
                    ? $this->getMultilingualAttributes()
                    : '') .
                '>';

            $html .= '<div class="card-header">';

            $html .= '<div>';

            $html .= '<div class="card-title">';
            $html .= '<svg class="card-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
            $html .= '<path stroke-width="2" d="' . $optionsConfig[$this->globalKey][$category]['icon'] . '"/>';
            $html .= '</svg>';

            $html .= $this->translator->trans($optionsConfig[$this->globalKey][$category]['title']);
            $html .= '</div>';

            $html .= '<p class="card-subtitle">';
            $html .= $this->translator->trans($optionsConfig[$this->globalKey][$category]['description']);
            $html .= '</p>';

            $html .= '</div>';

            if ($this->isMultilingual($optionsConfig[$this->globalKey][$category])) {
                $html .= $this->generateLocaleSwitch();
            }

            $html .= '</div>';

            foreach ($optionsConfig[$this->globalKey][$category]['options'] as $keyOption => $element) {
                $html .= '<div class="border-b border-[var(--border-color)] p-5 last:border-0">';

                switch ($element['type']) {
                    case 'text':
                        $html .= empty($element['translatable'])
                            ? $this->generateInputText($keyOption, $element)
                            : $this->generateTranslatableField($keyOption, $element, 'input');
                        break;
                    case 'boolean':
                        $html .= $this->generateRadioButton($keyOption, $element);
                        break;
                    case 'textarea':
                        $html .= empty($element['translatable'])
                            ? $this->generateTextarea($keyOption, $element)
                            : $this->generateTranslatableField($keyOption, $element, 'textarea');
                        break;
                    case 'select':
                        $html .= $this->generateSelect($keyOption, $element);
                        break;
                    case 'action':
                        $html .= $this->generateAction($keyOption, $element);
                        break;
                }

                $html .= '</div>';
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
        $require = $placeholder = $labelRequire = '';
        $msgError = $this->getError($key, $element);
        if (isset($element['required'])) {
            $require = 'required="required"';
            $labelRequire = ' <span class="text-error-text">*</span>';
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
            $labelRequire .
            '</label>
            <input type="text" class="form-input no-control event-input" id="' .
            $key .
            '" ' .
            $require .
            ' ' .
            $placeholder .
            ' value="' .
            $this->escape($this->getValueByKey($key)) .
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

        $checked = $active = '';
        if ($value === '1') {
            $checked = 'checked';
            $active = 'active';
        }

        $disabled = '';
        if (isset($element['disabled'])) {
            $disabled = 'disabled';
        }

        $html =
            '<div class="form-switch form-switch-inline">
            <input class="switch-input no-control event-input ' .
            $active .
            '" type="checkbox" role="switch" ' .
            $disabled .
            '
                id="' .
            $key .
            '" ' .
            $checked .
            '>
            <label class="switch-toggle" for="' .
            $key .
            '"></label>
            <label class="swith-label" for="' .
            $key .
            '"><span class="switch-label-text">
                ' .
            $this->translator->trans($element['label']) .
            '</span></label>';
        $html .= '</div>';

        $html .= $this->getSuccess($key, $element);
        $html .= $this->getSpinner($key);
        $html .= $this->getHelp($key, $element);

        return $html;
    }

    /**
     * Génère le champ de type textarea
     * @param string $key
     * @param array $element
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function generateTextarea(string $key, array $element): string
    {
        $html = $this->renderField(
            'textarea',
            $key,
            $this->translator->trans($element['label']),
            $this->getValueByKey($key),
            $element,
            isset($element['required']),
        );
        $html .= $this->getHelp($key, $element);

        return $html;
    }

    /**
     * Génère une option traduisible : un bloc par langue du site, seul celui de la langue par défaut est affiché,
     * le sélecteur de langue de l'onglet bascule les autres
     * @param string $key
     * @param array $element
     * @param string $tag input ou textarea
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function generateTranslatableField(string $key, array $element, string $tag): string
    {
        $default = $this->optionSystemService->getDefaultLanguage();
        $translations = $this->optionSystemService->decodeTranslations($this->getValueByKey($key));
        $label = $this->translator->trans($element['label']);

        $html = '';
        foreach ($this->getLocales() as $locale) {
            $id = $key . '-' . $locale;
            $attributes = ['data-key' => $key, 'data-locale' => $locale];
            if (!empty($element['fallback_label'])) {
                $attributes['data-fallback-label'] = '1';
            }

            $html .=
                '<div data-option-locale="' .
                $locale .
                '"' .
                ($locale === $default ? '' : ' class="hidden"') .
                '>' .
                $this->renderField(
                    $tag,
                    $id,
                    $label .
                        ' <span class="text-fg-muted">(' .
                        $this->translator->trans('global.locale.' . $locale) .
                        ')</span>',
                    $translations[$locale] ?? '',
                    $element,
                    isset($element['required']) && $locale === $default,
                    $attributes,
                ) .
                '<div id="fallback-' .
                $id .
                '" class="hidden form-text text-warning" aria-live="polite"></div>' .
                '</div>';
        }
        $html .= $this->getHelp($key, $element);

        return $html;
    }

    /**
     * Sélecteur de la langue de saisie d'un onglet multilingue, langue par défaut sélectionnée, avec le nombre de
     * champs vides de chaque langue
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function generateLocaleSwitch(): string
    {
        $default = $this->optionSystemService->getDefaultLanguage();
        $countLabel = $this->escape($this->translator->trans('options_system.multilingual.empty_count_label'));

        $html =
            '<div class="flex flex-wrap gap-2" role="group" data-option-locale-switch aria-label="' .
            $this->escape($this->translator->trans('options_system.multilingual.switch_label')) .
            '">';
        foreach ($this->getLocales() as $locale) {
            $selected = $locale === $default;
            $html .=
                '<button type="button" class="btn btn-xs ' .
                ($selected ? 'btn-primary' : 'btn-outline-dark') .
                '" data-option-locale-button="' .
                $locale .
                '" aria-pressed="' .
                ($selected ? 'true' : 'false') .
                '">' .
                $this->translator->trans('global.locale.' . $locale) .
                '<span class="hidden rounded-full bg-alert-warning-bg px-1.5 text-xs text-alert-warning-text" aria-hidden="true" data-option-locale-count="' .
                $locale .
                '"></span><span class="sr-only" data-option-locale-count-label="' .
                $locale .
                '" data-label="' .
                $countLabel .
                '"></span></button>';
        }
        return $html . '</div>';
    }

    /**
     * Attributs du panneau d'un onglet multilingue : langue par défaut et messages affichés sous un champ vide
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getMultilingualAttributes(): string
    {
        $default = $this->optionSystemService->getDefaultLanguage();
        $messages = [
            'data-default-locale' => $default,
            'data-msg-fallback-default' => $this->translator->trans(
                'options_system.multilingual.empty_fallback_default',
                [
                    'language' => $this->translator->trans('global.locale.' . $default),
                ],
            ),
            'data-msg-fallback-other' => $this->translator->trans('options_system.multilingual.empty_fallback_other'),
            'data-msg-empty-nothing' => $this->translator->trans('options_system.multilingual.empty_nothing'),
            'data-msg-empty-theme' => $this->translator->trans('options_system.multilingual.empty_theme_label'),
        ];

        $html = ' data-option-multilingual';
        foreach ($messages as $name => $value) {
            $html .= ' ' . $name . '="' . $this->escape($value) . '"';
        }
        return $html;
    }

    /**
     * Vrai si la catégorie se saisit langue par langue (clé multilingual de la config)
     * @param array $category
     * @return bool
     */
    private function isMultilingual(array $category): bool
    {
        return !empty($category['multilingual']);
    }

    /**
     * Langues du site, langue par défaut en premier
     * @return string[]
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getLocales(): array
    {
        return array_values(
            array_unique([
                $this->optionSystemService->getDefaultLanguage(),
                ...$this->handlers->get('supportedLocales')->getAll(),
            ]),
        );
    }

    /**
     * Rendu d'un label et de son champ (input texte ou textarea), suivis des messages d'erreur, de succès et du spinner
     * @param string $tag input ou textarea
     * @param string $id id du champ
     * @param string $label libellé déjà traduit
     * @param string $value
     * @param array $element configuration de l'option
     * @param bool $required
     * @param array<string, string> $attributes attributs supplémentaires du champ
     * @return string
     */
    private function renderField(
        string $tag,
        string $id,
        string $label,
        string $value,
        array $element,
        bool $required,
        array $attributes = [],
    ): string {
        if ($required) {
            $attributes['required'] = 'required';
        }
        if (isset($element['placeholder'])) {
            $attributes['placeholder'] = $this->translator->trans($element['placeholder']);
            $attributes['data-placeholder'] = $attributes['placeholder'];
        }

        $htmlAttributes = '';
        foreach ($attributes as $name => $attributeValue) {
            $htmlAttributes .= ' ' . $name . '="' . $this->escape($attributeValue) . '"';
        }
        if (isset($element['disabled'])) {
            $htmlAttributes .= ' disabled';
        }

        $field =
            $tag === 'textarea'
                ? '<textarea class="form-input no-control event-input" rows="5" id="' .
                    $id .
                    '"' .
                    $htmlAttributes .
                    '>' .
                    $this->escape($value) .
                    '</textarea>'
                : '<input type="text" class="form-input no-control event-input" id="' .
                    $id .
                    '"' .
                    $htmlAttributes .
                    ' value="' .
                    $this->escape($value) .
                    '">';

        return '<label for="' .
            $id .
            '" class="form-label">' .
            $label .
            ($required ? ' <span class="text-error-text">*</span>' : '') .
            '</label>' .
            $field .
            $this->getError($id, $element) .
            $this->getSuccess($id, $element) .
            $this->getSpinner($id);
    }

    /**
     * Génère une option de type action : un bouton qui pose la valeur par sa propre route, et la date de la dernière
     * action
     * @param string $key
     * @param array $element
     * @return string
     */
    private function generateAction(string $key, array $element): string
    {
        $value = $this->getValueByKey($key);
        $timestamp = $value === '' ? false : strtotime($value);
        $last =
            $timestamp === false
                ? $this->translator->trans($element['never'])
                : $this->translator->trans($element['last'], ['date' => $timestamp]);

        $html =
            '<p class="form-label">' .
            $this->translator->trans($element['label']) .
            '</p><button type="button" class="btn btn-sm btn-outline-dark" data-option-action="' .
            $key .
            '"><svg class="icon" aria-hidden="true" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.651 7.65a7.131 7.131 0 0 0-12.68 3.15M18.001 4v4h-4m-7.652 8.35a7.13 7.13 0 0 0 12.68-3.15M6 20v-4h4"/></svg>' .
            $this->translator->trans($element['button']) .
            '</button><p id="last-' .
            $key .
            '" class="form-text">' .
            $last .
            '</p>';
        $html .= $this->getSuccess($key, $element);
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

            $optionLabel = $this->translator->trans($option[1]);
            $optionHtml .=
                '<option value="' . $this->escape($option[0]) . '" ' . $selected . '>' . $optionLabel . '</option>';
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
            '" class="form-input no-control event-input" ' .
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
                '" class="form-text">
                 <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                <i>' .
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
        $html = '<div id="success-' . $key . '" class="hidden form-text text-success"> ✓ ';

        if (isset($element['success'])) {
            $msg = $this->translator->trans($element['success']);
        } else {
            $msg = $this->translator->trans('options_system.default.success');
        }

        $html .= $msg . '</div>';
        return $html;
    }

    /**
     * Retourne le message d'erreur
     * @param string $key
     * @param array $element
     * @return string
     */
    private function getError(string $key, array $element): string
    {
        $html = '<div id="error-' . $key . '" class="hidden form-text text-error"> ✗ ';

        if (isset($element['msg_error'])) {
            $msg = $this->translator->trans($element['msg_error']);
        } else {
            $msg = $this->translator->trans('options_system.default.error');
        }
        $html .= $msg . '</div>';
        return $html;
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
            '" class="float-end hidden">
            <svg aria-hidden="true" class="inline w-4 h-4 text-gray-200 animate-spin dark:text-gray-600 fill-[var(--primary)]" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
        <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
    </svg>
             <span class="form-text"><i> ' .
            $this->translator->trans('global.options.loading_save') .
            '</i></span></div>';
    }

    /**
     * Retourne la valeur d'une option en fonction de sa clé
     * @param string $key
     * @return string
     */
    private function getValueByKey(string $key): string
    {
        foreach ($this->listOptionSystem as $option) {
            /* @var OptionSystem $option */
            if ($option->getKey() === $key) {
                return $option->getValue() ?? '';
            }
        }
        // Option absente en base (ajoutée après l'installation) : on affiche sa valeur par défaut
        return (string) ($this->optionSystemService->getOptionConfigByKey($key)['default'] ?? '');
    }

    /**
     * Échappe une valeur pour l'injecter dans le HTML (contenu ou attribut)
     * @param string $value
     * @return string
     */
    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
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
        return $this->optionSystemService->getValueByKey($key) ?? '';
    }
}
