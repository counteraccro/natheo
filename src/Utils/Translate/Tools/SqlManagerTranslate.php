<?php

namespace App\Utils\Translate\Tools;

use App\Utils\Translate\AppTranslate;

class SqlManagerTranslate extends AppTranslate
{
    /**
     * @return array
     */
    public function getTranslate(): array
    {
        return [
            'alert_waring_title' => $this->translator->trans('sql_manager.alert.warning.title', domain: 'sql_manager'),
            'alert_waring_msg' => $this->translator->trans('sql_manager.alert.warning.msg', domain: 'sql_manager'),
            'label_textarea_query' => $this->translator->trans('sql_manager.label.textarea.query',
                domain: 'sql_manager'),
            'btn_execute_query' => $this->translator->trans('sql_manager.btn.execute.query', domain: 'sql_manager'),
            'btn_save_query' => $this->translator->trans('sql_manager.btn.save.query', domain: 'sql_manager'),
            'btn_disabled_query' => $this->translator->trans('sql_manager.btn.disabled.query', domain: 'sql_manager'),
            'btn_enabled_query' => $this->translator->trans('sql_manager.btn.enabled.query', domain: 'sql_manager'),
            'bloc_query' => $this->translator->trans('sql_manager.bloc.query', domain: 'sql_manager'),
            'label_list_table' => $this->translator->trans('sql_manager.label.liste.table', domain: 'sql_manager'),
            'btn_add_table' => $this->translator->trans('sql_manager.btn.add.table', domain: 'sql_manager'),
            'label_list_field' => $this->translator->trans('sql_manager.label.liste.field', domain: 'sql_manager'),
            'label_list_field_2' => $this->translator->trans('sql_manager.label.liste.field.2', domain: 'sql_manager'),
            'placeholder_table' => $this->translator->trans('sql_manager.placeholder.table', domain: 'sql_manager'),
            'placeholder_field' => $this->translator->trans('sql_manager.placeholder.field', domain: 'sql_manager'),
            'bloc_result' => $this->translator->trans('sql_manager.bloc.result', domain: 'sql_manager'),

        ];
    }
}
