<?php

declare(strict_types=1);
/**
 * Class pour la génération des traductions pour les scripts vue pour Tag
 * @author Gourdon Aymeric
 * @version 1.0
 */
namespace App\Translate\Admin\Content;

use App\Translate\AppTranslate;

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
            'formInputColorError' => $this->translator->trans('tag.form.input.color.error', domain: 'tag'),
            'formInputColorHelp' => $this->translator->trans('tag.form.input.color.help', domain: 'tag'),
            'formInputLabelLabel' => $this->translator->trans('tag.form.input.label.label', domain: 'tag'),
            'formInputLabelPlaceholder' => $this->translator->trans('tag.form.input.label.placeholder', domain: 'tag'),
            'formInputLabelError' => $this->translator->trans('tag.form.input.label.error', domain: 'tag'),
            'colorTitle' => $this->translator->trans('tag.form.color.title', domain: 'tag'),
            'autoCopy' => $this->translator->trans('tag.form.check.auto.copy', domain: 'tag'),
            'renduTitle' => $this->translator->trans('tag.form.render.title', domain: 'tag'),
            'btnSubmitUpdate' => $this->translator->trans('tag.form.submit.update', domain: 'tag'),
            'btnSubmitCreate' => $this->translator->trans('tag.form.submit.create', domain: 'tag'),
            'statTitle' => $this->translator->trans('tag.form.stat.title', domain: 'tag'),
            'btnCancel' => $this->translator->trans('tag.form.button.cancel', domain: 'tag'),
            'btnDelete' => $this->translator->trans('tag.form.button.delete', domain: 'tag'),
            'successSave' => $this->translator->trans('tag.form.success.save', domain: 'tag'),
            'errorSave' => $this->translator->trans('tag.form.error.save', domain: 'tag'),
            'modaleConfirmDeleteTitle' => $this->translator->trans(
                'tag.form.modal.confirm.delete.title',
                domain: 'tag',
            ),
            'modaleConfirmDeleteMessage' => $this->translator->trans(
                'tag.form.modal.confirm.delete.message',
                domain: 'tag',
            ),
            'modaleConfirmDeleteBtnOK' => $this->translator->trans(
                'tag.form.modal.confirm.delete.button.ok',
                domain: 'tag',
            ),
            'modaleConfirmDeleteBtnKo' => $this->translator->trans(
                'tag.form.modal.confirm.delete.button.ko',
                domain: 'tag',
            ),
            'styleTitle' => $this->translator->trans('tag.form.style.title', domain: 'tag'),
            'styleDescription' => $this->translator->trans('tag.form.style.description', domain: 'tag'),
            'textTitle' => $this->translator->trans('tag.form.text.title', domain: 'tag'),
            'textDescription' => $this->translator->trans('tag.form.text.description', domain: 'tag'),
        ];
    }
}
