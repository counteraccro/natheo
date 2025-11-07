<?php
/**
 * Class pour la génération des traductions pour les scripts vue pour Tag
 * @author Gourdon Aymeric
 * @version 1.0
 */
namespace App\Utils\Translate\Content;

use App\Utils\Translate\AppTranslate;

class TagTranslate extends AppTranslate
{
    /**
     * Retourne les traductions pour les tags
     * @return array
     */
    public function getTranslate(): array
    {
        return [
            'loading' => $this->translator->trans('tag.form.loading', domain: 'tag'),
            'formTitleCreate' => $this->translator->trans('tag.form.title.create', domain: 'tag'),
            'formTitleUpdate' => $this->translator->trans('tag.form.title.update', domain: 'tag'),
            'formInputColorLabel' => $this->translator->trans('tag.form.input.color.label', domain: 'tag'),
            'formInputColorError' => $this->translator->trans('tag.form.input.color.error', domain: 'tag'),
            'formInputLabelLabel' => $this->translator->trans('tag.form.input.label.label', domain: 'tag'),
            'formInputLabelPlaceholder' => $this->translator->trans('tag.form.input.label.placeholder', domain: 'tag'),
            'formInputLabelError' => $this->translator->trans('tag.form.input.label.error', domain: 'tag'),
            'linkColorChoice' => $this->translator->trans('tag.form.link.color.choice', domain: 'tag'),
            'colorTitle' => $this->translator->trans('tag.form.color.title', domain: 'tag'),
            'colorDescription' => $this->translator->trans('tag.form.color.description', domain: 'tag'),
            'labelCurrent' => $this->translator->trans('tag.form.title.label.current', domain: 'tag'),
            'labelOther' => $this->translator->trans('tag.form.title.label.other', domain: 'tag'),
            'autoCopy' => $this->translator->trans('tag.form.check.auto.copy', domain: 'tag'),
            'renduTitle' => $this->translator->trans('tag.form.rendu.title', domain: 'tag'),
            'btnSubmitUpdate' => $this->translator->trans('tag.form.submit.update', domain: 'tag'),
            'btnSubmitCreate' => $this->translator->trans('tag.form.submit.create', domain: 'tag'),
            'statTitle' => $this->translator->trans('tag.form.stat.title', domain: 'tag'),
            'formDisabledLabel' => $this->translator->trans('tag.form.input.disabled.label', domain: 'tag'),
            'btnCancel' => $this->translator->trans('tag.form.button.cancel', domain: 'tag'),
            'btnDelete' => $this->translator->trans('tag.form.button.delete', domain: 'tag'),
        ];
    }
}
