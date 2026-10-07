<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.4
 * Service lier à l'objet mail
 */

namespace App\Service\Admin\System;

use App\Entity\Admin\System\Mail;
use App\Entity\Admin\System\User;
use App\Enum\Admin\System\Options\OptionSystem;
use App\Service\Admin\AppAdminService;
use App\Service\Admin\GridService;
use App\Utils\Markdown;
use App\Utils\System\Mail\KeyWord;
use App\Utils\System\Mail\MailKey;
use App\Utils\System\Mail\MailTemplate;
use Doctrine\ORM\Tools\Pagination\Paginator;
use League\CommonMark\Exception\CommonMarkException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use App\Enum\Admin\Global\SvgIcon;

class MailService extends AppAdminService
{
    /**
     * Identifiant du jeton CSRF pour l'envoi d'un email de démo
     */
    public const string CSRF_TOKEN_SEND_DEMO = 'mail_send_demo';

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
     * Clé REPLY_TO
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
     * Clé LOCALE, langue des liens internes du contenu
     * @const string
     */
    public const LOCALE = 'locale';

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

        $mailTranslation = $mail->getMailTranslationByLocale($locale);
        return [
            'id' => $mail->getId(),
            // Change avec la langue pour forcer le re-rendu de l'éditeur markdown
            'key' => $mail->getId() . '-' . $locale,
            'title' => $translator->trans($mail->getTitle()),
            'description' => $translator->trans($mail->getDescription()),
            'keyWords' => $this->formatKeyWord($mail->getKeyWords()),
            'titleTrans' => $mailTranslation?->getTitle() ?? '',
            'contentTrans' => $mailTranslation?->getContent() ?? '',
        ];
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
     * Retourne une liste de mail paginé
     * @param int $page
     * @param int $limit
     * @param array $queryParams
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

        $labelId = $translator->trans('mail.grid.id', domain: 'mail');
        $labelTitle = $translator->trans('mail.grid.title', domain: 'mail');
        $labelDescription = $translator->trans('mail.grid.description', domain: 'mail');
        $labelCreatedAt = $translator->trans('mail.grid.created_at', domain: 'mail');
        $labelUpdateAt = $translator->trans('mail.grid.update_at', domain: 'mail');

        $column = [$labelId, $labelTitle, $labelDescription, $labelCreatedAt, $labelUpdateAt, GridService::KEY_ACTION];

        $dataPaginate = $this->getAllPaginate($page, $limit, $queryParams);
        $csrfToken = $this->getCsrfTokenManager()->getToken(self::CSRF_TOKEN_SEND_DEMO)->getValue();

        $nb = $dataPaginate->count();
        $data = [];
        foreach ($dataPaginate as $mail) {
            /** @var Mail $mail */
            $data[] = [
                $labelId => $mail->getId(),
                $labelTitle => $translator->trans($mail->getTitle()),
                $labelDescription => $translator->trans($mail->getDescription()),
                $labelCreatedAt => $mail->getCreatedAt()->format('d/m/y H:i'),
                $labelUpdateAt => $mail->getUpdateAt()->format('d/m/y H:i'),
                GridService::KEY_ACTION => $this->generateTabAction($mail, $csrfToken),
            ];
        }

        $tabReturn = [
            GridService::KEY_NB => $nb,
            GridService::KEY_DATA => $data,
            GridService::KEY_COLUMN => $column,
            GridService::KEY_RAW_SQL => $gridService->getFormatedSQLQuery($dataPaginate),
            GridService::KEY_LIST_ORDER_FIELD => [
                'id' => $labelId,
                'title' => $labelTitle,
                'description' => $labelDescription,
                'createdAt' => $labelCreatedAt,
                'updateAt' => $labelUpdateAt,
            ],
        ];
        return $gridService->addAllDataRequiredGrid($tabReturn);
    }

    /**
     * Génère le tableau d'action pour le Grid des mails
     * @param Mail $mail
     * @param string $csrfToken jeton CSRF pour l'envoi de l'email de démo
     * @return array[]
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function generateTabAction(Mail $mail, string $csrfToken): array
    {
        $router = $this->getRouter();

        return [
            [
                'label' => [SvgIcon::MAIL->value],
                'color' => 'success',
                'type' => 'post',
                'url' => $router->generate('admin_mail_send_demo_mail', ['id' => $mail->getId()]),
                'ajax' => true,
                'confirm' => false,
                'csrf' => $csrfToken,
            ],
            [
                'label' => [SvgIcon::PEN->value],
                'color' => 'primary',
                'type' => 'get',
                'id' => $mail->getId(),
                'url' => $router->generate('admin_mail_edit', ['id' => $mail->getId()]),
                'ajax' => false,
            ],
        ];
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
     *  reply_to => string || array - optionnel - Si non défini alors la valeur de OS_MAIL_REPLY_TO sera utilisée<br/>
     *  template => string <br />
     *  locale => string - optionnel - langue des liens internes, langue courante si non défini <br />
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
        $signature = $optionSystemService->getValueByKey(OptionSystem::OS_MAIL_SIGNATURE->value);

        // Liens internes résolus et urls absolues : un email est lu hors du site
        $content = $this->getMarkdownEditorService()->parseMarkdown(
            (string) $content,
            $params[self::LOCALE] ?? null,
            true,
        );
        $markdown = new Markdown();
        $content = $markdown->convertMarkdownToHtml($content);
        $content = $content . $signature;

        $body = array_merge((array) $body, ['content' => $content]);

        $email = (new TemplatedEmail())
            ->from(...$this->toAddresses($from))
            ->to(...$this->toAddresses($to))
            ->replyTo(...$this->toAddresses($replyTo))
            ->subject((string) $title)
            ->htmlTemplate($template)
            ->context($body);

        $cc = $this->getParamsValue($params, self::CC);
        if ($cc !== null) {
            $email->cc(...$this->toAddresses($cc));
        }

        $bcc = $this->getParamsValue($params, self::BCC);
        if ($bcc !== null) {
            $email->bcc(...$this->toAddresses($bcc));
        }

        $mailer->send($email);
    }

    /**
     * Convertit une ou plusieurs adresses en liste utilisable par les méthodes variadiques de TemplatedEmail
     * @param string|array|null $addresses
     * @return array
     */
    private function toAddresses(string|array|null $addresses): array
    {
        return array_values((array) $addresses);
    }

    /**
     * Permet de renvoyer la valeur d'un paramètre d'envoi en fonction de sa clé,
     * FROM et REPLY_TO prennent la valeur de l'option système si non définis
     * @param array $params
     * @param string $key
     * @return string|array|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getParamsValue(array $params, string $key): string|array|null
    {
        $value = $params[$key] ?? null;
        if ($value !== null && $value !== '' && $value !== []) {
            return $value;
        }

        $optionSystemService = $this->getOptionSystemService();
        return match ($key) {
            self::FROM => $optionSystemService->getValueByKey(OptionSystem::OS_MAIL_FROM->value),
            self::REPLY_TO => $optionSystemService->getValueByKey(OptionSystem::OS_MAIL_REPLY_TO->value),
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
     * en fonction de la clé d'un email et de la langue demandée (langue du système par défaut)
     * @param Mail $mail
     * @param array $tabKeyWord
     * @param string|null $locale si null, la langue par défaut du système est utilisée
     * @return array <br />[<br />
     * MailService::TITLE => titre du mail en fonction de la langue, <br />
     * MailService::CONTENT => contenu du mail avec le tableau de keyword, <br />
     * MailService::TO => '', <br />
     * MailService::TEMPLATE => MailTemplate::EMAIL_SIMPLE_TEMPLATE, <br />
     * MailService::LOCALE => langue de la traduction utilisée <br />
     * ]
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getDefaultParams(Mail $mail, array $tabKeyWord, ?string $locale = null): array
    {
        $locale ??= $this->getOptionSystemService()->getValueByKey(OptionSystem::OS_DEFAULT_LANGUAGE->value);

        $mailTranslation = $mail->getMailTranslationByLocale($locale) ?? $mail->getMailTranslations()->first();
        if ($mailTranslation === false) {
            throw new \LogicException(sprintf('Aucune traduction pour l\'email "%s"', $mail->getKey()));
        }

        return [
            MailService::TITLE => $mailTranslation->getTitle(),
            MailService::CONTENT => $this->replaceKeyWords($mailTranslation->getContent(), $tabKeyWord),
            MailService::TO => '',
            MailService::TEMPLATE => MailTemplate::EMAIL_SIMPLE_TEMPLATE,
            MailService::LOCALE => $mailTranslation->getLocale(),
        ];
    }

    /**
     * Remplace les mots clés [[...]] d'un contenu par leurs valeurs
     * @param string $content
     * @param array $tabKeyWord tableau issu de KeyWord (clés KeyWord::KEY_SEARCH et KeyWord::KEY_REPLACE)
     * @return string
     */
    public function replaceKeyWords(string $content, array $tabKeyWord): string
    {
        return str_replace($tabKeyWord[KeyWord::KEY_SEARCH], $tabKeyWord[KeyWord::KEY_REPLACE], $content);
    }

    /**
     * Retourne le tableau de mots clés d'un email de démo, rempli avec les données de l'utilisateur courant
     * @param Mail $mail
     * @param User $user utilisateur qui reçoit l'email de démo (sert aussi d'administrateur fictif)
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getDemoKeyWords(Mail $mail, User $user): array
    {
        $optionSystemService = $this->getOptionSystemService();
        $url = $this->getRouter()->generate('front_no_local');
        $keyWord = new KeyWord($mail->getKey());

        return match ($mail->getKey()) {
            MailKey::MAIL_CHANGE_PASSWORD => $keyWord->getMailChangePassword($user, $url, $optionSystemService),
            MailKey::MAIL_ACCOUNT_ADM_DISABLE => $keyWord->getTabMailAccountAdmDisabled(
                $user,
                $user,
                $optionSystemService,
            ),
            MailKey::MAIL_ACCOUNT_ADM_ENABLE => $keyWord->getTabMailAccountAdmEnabled(
                $user,
                $user,
                $optionSystemService,
            ),
            MailKey::MAIL_CREATE_ACCOUNT_ADM => $keyWord->getTabMailCreateAccountAdm(
                $user,
                $user,
                $url,
                $optionSystemService,
            ),
            MailKey::MAIL_SELF_DISABLED_ACCOUNT => $keyWord->getTabMailSelfDisabled($user, $optionSystemService),
            MailKey::MAIL_SELF_DELETE_ACCOUNT => $keyWord->getTabMailSelfDelete($user, $optionSystemService),
            MailKey::MAIL_SELF_ANONYMOUS_ACCOUNT => $keyWord->getTabMailSelfAnonymous($user, $optionSystemService),
            MailKey::MAIL_RESET_PASSWORD => $keyWord->getTabMailResetPassword($user, $user, $url, $optionSystemService),
            default => [
                KeyWord::KEY_SEARCH => [],
                KeyWord::KEY_REPLACE => [],
            ],
        };
    }

    /**
     * Envoi un email de démo à l'utilisateur.
     * Si $title et $content sont renseignés, ils remplacent le contenu sauvegardé (test avant sauvegarde)
     * @param Mail $mail
     * @param User $user
     * @param string|null $locale langue de l'email, langue par défaut du système si null
     * @param string|null $title
     * @param string|null $content
     * @return void
     * @throws CommonMarkException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function sendDemoMail(
        Mail $mail,
        User $user,
        ?string $locale = null,
        ?string $title = null,
        ?string $content = null,
    ): void {
        $tabKeyWord = $this->getDemoKeyWords($mail, $user);
        $params = $this->getDefaultParams($mail, $tabKeyWord, $locale);

        if ($title !== null && $title !== '') {
            $params[self::TITLE] = $title;
        }
        if ($content !== null && $content !== '') {
            $params[self::CONTENT] = $this->replaceKeyWords($content, $tabKeyWord);
        }
        $params[self::TO] = $user->getEmail();

        $this->sendMail($params);
    }
}
