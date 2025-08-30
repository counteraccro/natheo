<?php
/**
 * Class pour la génération des traductions pour les scripts vue du front
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Utils\Translate\Front;

use App\Utils\Translate\AppTranslate;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class FrontTranslate extends AppTranslate
{

    public function __construct(#[AutowireLocator([
        'translator' => TranslatorInterface::class,
        'parameterBag' => ContainerBagInterface::class,
    ])] private readonly ContainerInterface $handlers)
    {
        parent::__construct($handlers);
    }

    /**
     * Traduction front
     * @return array[]
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getTranslate(): array
    {
        return [
            'errorApi' => $this->getTranslateErrorApi(),
            'footer' => $this->getTranslateFooter(),
            'header' => $this->getTranslateHeader(),
            'main' => [
                'articleFooter' => $this->getTranslateArticleFooter(),
            ]
        ];
    }

    public function getTranslateErrorApi(): array
    {
        return [
            '401' => $this->translator->trans('front.error.api.modal.error.401', domain: 'front'),
            '403' => $this->translator->trans('front.error.api.modal.error.403', domain: 'front'),
        ];
    }

    /**
     * Traduction footer
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getTranslateFooter(): array
    {
        /** @var ContainerBagInterface $parameterBag */
        $parameterBag = $this->handlers->get('parameterBag');

        return [
            'credit' => $this->translator->trans('front.footer.credit', ['year' => date('Y'), 'version' => $parameterBag->get('app.version')], 'front'),
            'templateVersion' => $this->translator->trans('front.footer.template.version', ['version' => $parameterBag->get('app.natheo_horizon.version')], 'front'),
            'adminLink' => $this->translator->trans('front.footer.admin.link', domain: 'front'),
            'githubLink' => $this->translator->trans('front.footer.github.link', domain: 'front'),
            'sitemapLink' => $this->translator->trans('front.footer.sitemap.link', domain: 'front'),
            'frLink' => $this->translator->trans('front.footer.fr.link', domain: 'front'),
            'esLink' => $this->translator->trans('front.footer.es.link', domain: 'front'),
            'enLink' => $this->translator->trans('front.footer.en.link', domain: 'front'),
        ];
    }

    /**
     * Traduction header
     * @return array
     */
    private function getTranslateHeader(): array
    {
        return [
            'login' => $this->translator->trans('front.header.login', domain: 'front'),
            'logout' => $this->translator->trans('front.header.logout', domain: 'front'),
        ];

    }

    private function getTranslateArticleFooter(): array
    {
        return [
            'published' => $this->translator->trans('front.article.footer.published', domain: 'front'),
            'edit' => $this->translator->trans('front.article.footer.edit', domain: 'front'),
            'statPublication' => $this->translator->trans('front.article.footer.statPublication', domain: 'front'),
            'infoDraft' => $this->translator->trans('front.article.footer.info.draft', domain: 'front'),
            'comment' => $this->getTranslateComment()
        ];
    }

    private function getTranslateComment(): array
    {
        return [
            'title' => $this->translator->trans('front.comment.title', domain: 'front'),
            'nbComments' => $this->translator->trans('front.comment.nbComments', domain: 'front'),
            'timeAgo' => $this->translator->trans('front.comment.timeAgo', domain: 'front'),
        ];
    }
}