<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Service lier à l'objet mail
 */

namespace App\Service\Admin;

use App\Entity\Admin\Mail;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class MailService extends AppAdminService
{

    /**
     * @var GridService
     */
    private GridService $gridService;

    /**
     * @param EntityManagerInterface $entityManager
     * @param ContainerBagInterface $containerBag
     * @param TranslatorInterface $translator
     * @param UrlGeneratorInterface $router
     * @param Security $security
     * @param RequestStack $requestStack
     * @param GridService $gridService
     */
    public function __construct(EntityManagerInterface $entityManager, ContainerBagInterface $containerBag,
                                TranslatorInterface    $translator, UrlGeneratorInterface $router,
                                Security               $security, RequestStack $requestStack, GridService $gridService)
    {
        $this->gridService = $gridService;

        parent::__construct($entityManager, $containerBag, $translator, $router, $security, $requestStack);
    }


    /**
     * Retourne une liste de mail formaté pour vueJs et automatiquement traduit en fonction de langue par défaut
     * @param string $locale
     * @param Mail $mail
     * @return array
     */
    public function getMailFormat(string $locale, Mail $mail): array
    {
        $return = [];
        $mailTranslation = $mail->geMailTranslationByLocale($locale);
        $return[$mail->getId()] = [
            'id' => $mail->getId(),
            'key' => rand(1, 9) . rand(1, 9) . rand(1, 9) . rand(1, 9),
            'title' => $this->translator->trans($mail->getTitle()),
            'description' => $this->translator->trans($mail->getDescription()),
            'keyWords' => $this->formatKeyWord($mail->getKeyWords()),
            'titleTrans' => $mailTranslation->getTitle(),
            'contentTrans' => $mailTranslation->getContent()
        ];
        return $return;
    }

    /**
     * Format la string keyWord en tableau avec traduction
     * @param string $keyWord
     * @return array
     */
    private function formatKeyWord(string $keyWord): array
    {
        $tab = explode('|', $keyWord);

        $return = [];
        foreach ($tab as $keyWord) {
            $return[$keyWord] = $this->translator->trans('mail.' . $keyWord);
        }
        return $return;
    }

    /**
     * Retourne une liste de user paginé
     * @param int $page
     * @param int $limit
     * @return Paginator
     */
    public function getAllPaginate(int $page, int $limit): Paginator
    {
        $repo = $this->getRepository(Mail::class);
        return $repo->getAllPaginate($page, $limit);
    }

    /**
     * Construit le tableau de donnée à envoyer au tableau GRID
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getAllFormatToGrid(int $page, int $limit): array
    {
        $column = [
            $this->translator->trans('mail.grid.id', domain: 'mail'),
            $this->translator->trans('mail.grid.title', domain: 'mail'),
            $this->translator->trans('mail.grid.description', domain: 'mail'),
            $this->translator->trans('mail.grid.created_at', domain: 'mail'),
            $this->translator->trans('mail.grid.update_at', domain: 'mail'),
            GridService::KEY_ACTION,
        ];

        $dataPaginate = $this->getAllPaginate($page, $limit);

        $nb = $dataPaginate->count();
        $data = [];
        foreach ($dataPaginate as $mail) {
            /* @var Mail $mail */

            $actions = $this->generateTabAction($mail);
            $data[] = [
                $this->translator->trans('mail.grid.id', domain: 'mail') => $mail->getId(),
                $this->translator->trans('mail.grid.title', domain: 'mail') => $this->translator->trans($mail->getTitle()),
                $this->translator->trans('mail.grid.description', domain: 'mail') => $this->translator->trans($mail->getDescription()),
                $this->translator->trans('mail.grid.created_at', domain: 'mail') => $mail->getCreatedAt()->
                format('d/m/y H:i'),
                $this->translator->trans('mail.grid.update_at', domain: 'mail') => $mail->getUpdateAt()->
                format('d/m/y H:i'),
                GridService::KEY_ACTION => json_encode($actions)
            ];
        }

        $tabReturn = [
            GridService::KEY_NB => $nb,
            GridService::KEY_DATA => $data,
            GridService::KEY_COLUMN => $column,
        ];
        return $this->gridService->addAllDataRequiredGrid($tabReturn);

    }

    /**
     * Génère le tableau d'action pour le Grid des mails
     * @param Mail $mail
     * @return array[]|string[]
     */
    private function generateTabAction(Mail $mail): array
    {

        $actions = [];

        // Bouton test email
        $actions[] = [
            'label' => '<i class="bi bi-send-check"></i>',
            'url' => $this->router->generate('admin_mail_edit', ['id' => $mail->getId()]),
            'ajax' => true,
            'confirm' => false,
        ];

        // Bouton edit
        $actions[] = ['label' => '<i class="bi bi-pencil"></i>',
            'id' => $mail->getId(),
            'url' => $this->router->generate('admin_mail_edit', ['id' => $mail->getId()]),
            'ajax' => false];
        return $actions;

    }


}
