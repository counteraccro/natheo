<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Catalogue des composants du design system, déclaré pour l'environnement dev seulement (config/routes/design_system.yaml)
 */

namespace App\Controller\Admin\Dev;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Translation\TranslatorBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[IsGranted('ROLE_CONTRIBUTEUR')]
class DesignSystemController extends AbstractController
{
    /**
     * Page du catalogue, avec les messages du domaine admin_ui de la langue courante
     * @param TranslatorInterface $translator
     * @param string $_locale
     * @return Response
     */
    public function index(TranslatorInterface $translator, string $_locale): Response
    {
        $messages = [];
        if ($translator instanceof TranslatorBagInterface) {
            $catalogue = $translator->getCatalogue($_locale);
            $messages = $catalogue->all('admin_ui+intl-icu') + $catalogue->all('admin_ui');
        }

        return $this->render('admin/dev/design_system.html.twig', [
            'messages' => $messages,
            'locale' => $_locale,
        ]);
    }
}
