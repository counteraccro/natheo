<?php

namespace App\Service\Admin;

use App\Entity\Admin\Mail;

class MailService extends AppAdminService
{

    /**
     * Retourne une liste de mail en fonction de la langue
     * @param string $locale
     * @return Mail[]
     */
    public function getAllMailByLocale(string $locale): array
    {
        $mailRepository = $this->getRepository(Mail::class);
        return $mailRepository->getAllMailByLocale($locale);
    }

    /**
     * Retourne une liste de mail formaté pour vueJs et automatiquement traduit en fonction de langue par défaut
     * @param string $locale
     * @return array
     */
    public function getAllMailByLocaleFormat(string $locale): array
    {
        $mails = $this->getAllMailByLocale($locale);

        $return = [];
        foreach ($mails as $mail) {
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
        }
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


}
