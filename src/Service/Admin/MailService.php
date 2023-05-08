<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Service lier à l'objet mail
 */

namespace App\Service\Admin;

use App\Entity\Admin\Mail;
use App\Entity\Admin\User;
use App\Utils\Mail\KeyWord;
use App\Utils\Mail\MailKey;
use App\Utils\Mail\MailTemplate;
use App\Utils\Markdown;
use App\Utils\Options\OptionSystemKey;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use League\CommonMark\Exception\CommonMarkException;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

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
     * @var GridService
     */
    private GridService $gridService;

    /**
     * @var MailerInterface
     */
    private MailerInterface $mailer;

    /**
     * @var OptionSystemService
     */
    private OptionSystemService $optionSystemService;

    /**
     * @param EntityManagerInterface $entityManager
     * @param ContainerBagInterface $containerBag
     * @param TranslatorInterface $translator
     * @param UrlGeneratorInterface $router
     * @param Security $security
     * @param RequestStack $requestStack
     * @param GridService $gridService
     * @param MailerInterface $mailer
     * @param OptionSystemService $optionSystemService
     */
    public function __construct(EntityManagerInterface $entityManager, ContainerBagInterface $containerBag,
                                TranslatorInterface    $translator, UrlGeneratorInterface $router,
                                Security               $security, RequestStack $requestStack, GridService $gridService,
                                MailerInterface        $mailer, OptionSystemService $optionSystemService
    )
    {
        $this->gridService = $gridService;
        $this->mailer = $mailer;
        $this->optionSystemService = $optionSystemService;

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
                $this->translator->trans('mail.grid.title', domain: 'mail') =>
                    $this->translator->trans($mail->getTitle()),
                $this->translator->trans('mail.grid.description', domain: 'mail') =>
                    $this->translator->trans($mail->getDescription()),
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
            'url' => $this->router->generate('admin_mail_send_demo_mail', ['id' => $mail->getId()]),
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
     * @throws TransportExceptionInterface
     * @throws CommonMarkException
     */
    public function sendMail(array $params): void
    {

        $from = $this->getParamsValue($params, self::FROM);
        $replyTo = $this->getParamsValue($params, self::REPLY_TO);
        $template = $this->getParamsValue($params, self::TEMPLATE);
        $content = $this->getParamsValue($params, self::CONTENT);
        $title = $this->getParamsValue($params, self::TITLE);
        $body = $this->getParamsValue($params, self::BODY);
        $signature = $this->optionSystemService->getValueByKey(OptionSystemKey::OS_MAIL_SIGNATURE);

        $markdown = new Markdown();
        $content = $markdown->convertMarkdownToHtml($content);
        $content = $content . $signature;

        $body = array_merge((array)$body, ['content' => $content]);

        $email = (new TemplatedEmail())
            ->from($from)
            ->to($from)
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

        $this->mailer->send($email);
    }

    /**
     * Permet de renvoyer la valeur d'une option en fonction de sa clé
     * @param $params
     * @param $key
     * @return string|null
     */
    private function getParamsValue($params, $key): ?string
    {
        if (isset($params[$key]) && ($params[$key] !== "" || $params !== null)) {
            return $params[$key];
        }

        return match ($key) {
            self::FROM => $this->optionSystemService->getValueByKey(OptionSystemKey::OS_MAIL_FROM),
            self::REPLY_TO => $this->optionSystemService->getValueByKey(OptionSystemKey::OS_MAIL_REPLY_TO),
            default => null,
        };
    }

    /**
     * Retourne une entité Mail en fonction de sa clé
     * @param String $key
     * @return Mail
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
     */
    public function getDefaultParams(Mail $mail, array $tabKeyWord): array
    {
        $mailTranslate = $mail->geMailTranslationByLocale($this->optionSystemService
            ->getValueByKey(OptionSystemKey::OS_DEFAULT_LANGUAGE));
        $content = str_replace(
            $tabKeyWord[KeyWord::KEY_SEARCH],
            $tabKeyWord[KeyWord::KEY_REPLACE],
            $mailTranslate->getContent()
        );

        return [
            MailService::TITLE => $mailTranslate->getTitle(),
            MailService::CONTENT => $content,
            MailService::TO => '',
            MailService::TEMPLATE => MailTemplate::EMAIL_SIMPLE_TEMPLATE
        ];
    }
}
