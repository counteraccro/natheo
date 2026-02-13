<?php
/**
 * @author Gourdon Aymeric
 * @version 1.2
 * Service lier à l'objet mail
 */

namespace App\Service\Admin\System;

use App\Entity\Admin\System\Mail;
use App\Service\Admin\AppAdminService;
use App\Service\Admin\GridService;
use App\Utils\Markdown;
use App\Utils\System\Mail\KeyWord;
use App\Utils\System\Mail\MailTemplate;
use App\Utils\System\Options\OptionSystemKey;
use Doctrine\ORM\Tools\Pagination\Paginator;
use League\CommonMark\Exception\CommonMarkException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class MailService extends AppAdminService
{
    /**
     * Clé FROM
     * @const string
     */
    public const FROM = 'from';

    /**
     * Clé TO
     * @const string
     */
    public const TO = 'to';

    /**
     * Clé CC
     * @const string
     */
    public const CC = 'cc';

    /**
     * Clé BCC
     * @const string
     */
    public const BCC = 'bcc';

    /**
     * Clé REPLAY_TO
     * @const string
     */
    public const REPLY_TO = 'reply_to';

    /**
     * Clé TEMPLATE
     * @const string
     */
    public const TEMPLATE = 'template';

    /**
     * Clé CONTENT
     * @const string
     */
    public const CONTENT = 'content';

    /**
     * Clé TITLE
     * @const string
     */
    public const TITLE = 'title';

    /**
     * Clé BODY
     * @const string
     */
    public const BODY = 'body';

    /**
     * Retourne une liste de mail formaté pour vueJs et automatiquement traduit en fonction de langue par défaut
     * @param string $locale
     * @param Mail $mail
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getMailFormat(string $locale, Mail $mail): array
    {
        $translator = $this->getTranslator();

        $return = [];
        $mailTranslation = $mail->geMailTranslationByLocale($locale);
        $return[$mail->getId()] = [
            'id' => $mail->getId(),
            'key' => rand(1, 9) . rand(1, 9) . rand(1, 9) . rand(1, 9),
            'title' => $translator->trans($mail->getTitle()),
            'description' => $translator->trans($mail->getDescription()),
            'keyWords' => $this->formatKeyWord($mail->getKeyWords()),
            'titleTrans' => $mailTranslation->getTitle(),
            'contentTrans' => $mailTranslation->getContent(),
        ];
        return $return;
    }

    /**
     * Format la string keyWord en tableau avec traduction
     * @param string $keyWord
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function formatKeyWord(string $keyWord): array
    {
        $translator = $this->getTranslator();

        $tab = explode('|', $keyWord);

        $return = [];
        foreach ($tab as $keyWord) {
            $return[$keyWord] = $translator->trans('mail.' . $keyWord);
        }
        return $return;
    }

    /**
     * Retourne une liste de user paginé
     * @param int $page
     * @param int $limit
     * @return Paginator
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAllPaginate(int $page, int $limit, array $queryParams): Paginator
    {
        $repo = $this->getRepository(Mail::class);
        return $repo->getAllPaginate($page, $limit, $queryParams);
    }

    /**
     * Construit le tableau de donnée à envoyer au tableau GRID
     * @param int $page
     * @param int $limit
     * @param array $queryParams
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAllFormatToGrid(int $page, int $limit, array $queryParams): array
    {
        $translator = $this->getTranslator();
        $gridService = $this->getGridService();

        $column = [
            $translator->trans('mail.grid.id', domain: 'mail'),
            $translator->trans('mail.grid.title', domain: 'mail'),
            $translator->trans('mail.grid.description', domain: 'mail'),
            $translator->trans('mail.grid.created_at', domain: 'mail'),
            $translator->trans('mail.grid.update_at', domain: 'mail'),
            GridService::KEY_ACTION,
        ];

        $dataPaginate = $this->getAllPaginate($page, $limit, $queryParams);

        $nb = $dataPaginate->count();
        $data = [];
        foreach ($dataPaginate as $mail) {
            /* @var Mail $mail */

            $actions = $this->generateTabAction($mail);
            $data[] = [
                $translator->trans('mail.grid.id', domain: 'mail') => $mail->getId(),
                $translator->trans('mail.grid.title', domain: 'mail') => $translator->trans($mail->getTitle()),
                $translator->trans('mail.grid.description', domain: 'mail') => $translator->trans(
                    $mail->getDescription(),
                ),
                $translator->trans('mail.grid.created_at', domain: 'mail') => $mail
                    ->getCreatedAt()
                    ->format('d/m/y H:i'),
                $translator->trans('mail.grid.update_at', domain: 'mail') => $mail->getUpdateAt()->format('d/m/y H:i'),
                GridService::KEY_ACTION => $actions,
            ];
        }

        $tabReturn = [
            GridService::KEY_NB => $nb,
            GridService::KEY_DATA => $data,
            GridService::KEY_COLUMN => $column,
            GridService::KEY_RAW_SQL => $gridService->getFormatedSQLQuery($dataPaginate),
            GridService::KEY_LIST_ORDER_FIELD => [
                'id' => $translator->trans('mail.grid.id', domain: 'mail'),
                'title' => $translator->trans('mail.grid.title', domain: 'mail'),
                'description' => $translator->trans('mail.grid.description', domain: 'mail'),
                'createdAt' => $translator->trans('mail.grid.created_at', domain: 'mail'),
                'updateAt' => $translator->trans('mail.grid.update_at', domain: 'mail'),
            ],
        ];
        return $gridService->addAllDataRequiredGrid($tabReturn);
    }

    /**
     * Génère le tableau d'action pour le Grid des mails
     * @param Mail $mail
     * @return array[]|string[]
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function generateTabAction(Mail $mail): array
    {
        $router = $this->getRouter();

        $actions = [];

        // Bouton test email
        $actions[] = [
            'label' => [
                'm3.5 5.5 7.893 6.036a1 1 0 0 0 1.214 0L20.5 5.5M4 19h16a1 1 0 0 0 1-1V6a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1Z',
            ],
            'color' => 'success',
            'type' => 'get',
            'url' => $router->generate('admin_mail_send_demo_mail', ['id' => $mail->getId()]),
            'ajax' => true,
            'confirm' => false,
        ];

        // Bouton edit
        $actions[] = [
            'label' => [
                'M10.779 17.779 4.36 19.918 6.5 13.5m4.279 4.279 8.364-8.643a3.027 3.027 0 0 0-2.14-5.165 3.03 3.03 0 0 0-2.14.886L6.5 13.5m4.279 4.279L6.499 13.5m2.14 2.14 6.213-6.504M12.75 7.04 17 11.28',
            ],
            'color' => 'primary',
            'type' => 'get',
            'id' => $mail->getId(),
            'url' => $router->generate('admin_mail_edit', ['id' => $mail->getId()]),
            'ajax' => false,
        ];
        return $actions;
    }

    /**
     * Permet d'envoyer un email avec le contenu présent dans Mail
     * @param array $params Tableau d'options contenant <br />
     *  title => string - titre de l'email <br />
     *  body => array - optionnel - Contenu à ajouter en plus de content directement dans le body du mail
     * (doit être présent dans le template) <br />
     *  content => string - Contenu de l'email <br />
     *  from => string || array  - optionnel - Si non défini alors la valeur de OS_MAIL_FROM sera utilisée<br/>
     *  to => string || array <br/>
     *  cc =>  string || array  - optionnel<br/>
     *  bcc => string || array - optionnel <br/>
     *  replayTo => string || array - si son défini alors la valeur de OS_MAIL_REPLAY_TO sera utilisée<br/>
     *  template => string <br />
     * @return void
     * @throws CommonMarkException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function sendMail(array $params): void
    {
        $mailer = $this->getMailer();
        $optionSystemService = $this->getOptionSystemService();

        $to = $this->getParamsValue($params, self::TO);
        $from = $this->getParamsValue($params, self::FROM);
        $replyTo = $this->getParamsValue($params, self::REPLY_TO);
        $template = $this->getParamsValue($params, self::TEMPLATE);
        $content = $this->getParamsValue($params, self::CONTENT);
        $title = $this->getParamsValue($params, self::TITLE);
        $body = $this->getParamsValue($params, self::BODY);
        $signature = $optionSystemService->getValueByKey(OptionSystemKey::OS_MAIL_SIGNATURE);

        $markdown = new Markdown();
        $content = $markdown->convertMarkdownToHtml($content);
        $content = $content . $signature;

        $body = array_merge((array) $body, ['content' => $content]);

        $email = (new TemplatedEmail())
            ->from($from)
            ->to($to)
            ->replyTo($replyTo)
            ->subject($title)
            ->htmlTemplate($template)
            ->context($body);

        $cc = $this->getParamsValue($params, self::CC);
        if ($cc !== null) {
            $email->cc($cc);
        }

        $bcc = $this->getParamsValue($params, self::BCC);
        if ($bcc !== null) {
            $email->cc($bcc);
        }

        $mailer->send($email);
    }

    /**
     * Permet de renvoyer la valeur d'une option en fonction de sa clé
     * @param $params
     * @param $key
     * @return string|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getParamsValue($params, $key): ?string
    {
        $optionSystemService = $this->getOptionSystemService();

        if (isset($params[$key]) && ($params[$key] !== '' || $params !== null)) {
            return $params[$key];
        }

        return match ($key) {
            self::FROM => $optionSystemService->getValueByKey(OptionSystemKey::OS_MAIL_FROM),
            self::REPLY_TO => $optionSystemService->getValueByKey(OptionSystemKey::OS_MAIL_REPLY_TO),
            default => null,
        };
    }

    /**
     * Retourne une entité Mail en fonction de sa clé
     * @param String $key
     * @return Mail
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getByKey(string $key): Mail
    {
        $repo = $this->getRepository(Mail::class);
        return $repo->findByKey($key);
    }

    /**
     * Retourne la liste des paramètres pour l'envoi d'un email sous la forme d'un tableau
     * en fonction de la clé d'un email et de la langue du système
     * @param Mail $mail
     * @param array $tabKeyWord
     * @return array <br />[<br />
     * MailService::TITLE => titre du mail en fonction de la langue du système, <br />
     * MailService::CONTENT => contenu du mail avec le tableau de keyword, <br />
     * MailService::TO => '', <br />
     * MailService::TEMPLATE => MailTemplate::EMAIL_SIMPLE_TEMPLATE <br />
     * ]
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getDefaultParams(Mail $mail, array $tabKeyWord): array
    {
        $optionSystemService = $this->getOptionSystemService();

        $mailTranslate = $mail->geMailTranslationByLocale(
            $optionSystemService->getValueByKey(OptionSystemKey::OS_DEFAULT_LANGUAGE),
        );
        $content = str_replace(
            $tabKeyWord[KeyWord::KEY_SEARCH],
            $tabKeyWord[KeyWord::KEY_REPLACE],
            $mailTranslate->getContent(),
        );

        return [
            MailService::TITLE => $mailTranslate->getTitle(),
            MailService::CONTENT => $content,
            MailService::TO => '',
            MailService::TEMPLATE => MailTemplate::EMAIL_SIMPLE_TEMPLATE,
        ];
    }
}
