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
            'test' => $this->translator->trans('sql_manager.grid.update_at', domain: 'sql_manager')
        ];
    }
}
