<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Service qui permet de gérer les traitements pour le TagComponent de vueJS
 */
namespace App\Service\Admin\Content\Tag;

class TagComponentService extends TagService
{

    /**
     * Génère l'ensemble des routes nécessaires au bon fonctionnement du composant tag
     * @return array
     */
    public function getAllRoute(): array
    {
        return [
            'search' => $this->router->generate('admin_tag_search'),
            'init_data' => $this->router->generate('admin_tag_init_data_comp')
        ];
    }

    /**
     *
     * @return array
     */
    public function getTranslateTagComponent(): array
    {
        return [
            'title' => $this->translator->trans('tag.component.title', domain: 'tag'),
        ];
    }
}
