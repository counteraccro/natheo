<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Service lier aux options système
 */
namespace App\Service\Admin;

use App\Entity\Admin\OptionSystem;

class OptionSystemService extends AppAdminService
{
    /**
     * Clé option nom du site
     * @var string
     */
    const OS_SITE_NAME = 'OS_SITE_NAME';

    /**
     * Clé option site ouvert
     * @var string
     */
    const OS_OPEN_SITE = 'OS_OPEN_SITE';

    /**
     * Clé option script header
     * @var string
     */
    const OS_FRONT_SCRIPT_TOP = 'OS_FRONT_SCRIPT_TOP';

    /**
     * Clé option script début body
     * @var string
     */
    const OS_FRONT_SCRIPT_START_BODY = 'OS_FRONT_SCRIPT_START_BODY';

    /**
     * Clé option script fin body
     * @var string
     */
    const OS_FRONT_SCRIPT_END_BODY = 'OS_FRONT_SCRIPT_END_BODY';

    /**
     * Clé option remplacement user delete
     * @var string
     */
    const OS_REPLACE_DELETE_USER = 'OS_REPLACE_DELETE_USER';

    /**
     * Clé option confirmation quitter form
     * @var string
     */
    const OS_CONFIRM_LEAVE_FORM = 'OS_CONFIRM_LEAVE_FORM';

    /**
     * Clé option authorisation suppression de données
     */
    const OS_ALLOW_DELETE_DATA = 'OS_ALLOW_DELETE_DATA';

    /**
     * Retourne l'ensemble des options systèmes
     * @return array|object[]
     */
    public function getAll(): array
    {
        $optionServiceRepo = $this->entityManager->getRepository(OptionSystem::class);
        return $optionServiceRepo->findAll();
    }

    /**
     * Retourne une option système en fonction de sa clé
     * @param string $key
     * @return object|null
     */
    public function getByKey(string $key): null|object
    {
        $optionServiceRepo = $this->entityManager->getRepository(OptionSystem::class);
        return $optionServiceRepo->findOneBy(['key' => $key]);
    }
}