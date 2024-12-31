<?php

namespace App\Utils\Translate;

class GlobalSearchTranslate extends AppTranslate
{
    /**
     * Retourne les traductions pour la recherche globale
     * @return array
     */
    public function getTranslate(): array
    {
        return [
            'subTitlePage' => $this->translator->trans('global_search.sub.title.page', domain: 'global_search'),
            'loadingPage' => $this->translator->trans('global_search.page.loading', domain: 'global_search'),
        ];
    }
}
