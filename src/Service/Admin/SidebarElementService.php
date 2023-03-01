<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Service lier à l'objet SidebarElement
 */
namespace App\Service\Admin;

use App\Entity\Admin\SidebarElement;

class SidebarElementService extends AdminAppService
{
    /**
     * Récupère l'ensemble des sidebarElement parent
     * @param bool $disabled
     * @return mixed
     */
    public function getAllParent(bool $disabled = false): mixed
    {
        $sidebarElementRepo = $this->entityManager->getRepository(SidebarElement::class);
        return $sidebarElementRepo->getAllParent($disabled);
    }
}