<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * AppService pour le front
 */

namespace App\Service\Front;

use App\Enum\Front\Template;
use App\Utils\System\Options\OptionSystemKey;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AppFrontService extends AppFrontHandlerService
{
    /**
     * Retourne le path du template en fonction de l'option system OS_THEME_FRONT_SITE
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getPathTemplate(): string
    {
        $translator = $this->getTranslator();
        $optionSystemService = $this->getOptionSystemService();
        $template = $optionSystemService->getValueByKey(OptionSystemKey::OS_THEME_FRONT_SITE);

        if (!in_array($template, Template::toArray())) {
            throw new HttpException(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $translator->trans('front.error.not.template', ['template' => $template], domain: 'front_error'),
            );
        }
        return 'front/' . $template;
    }

    /**
     * Retourne la clé de génération des fichiers CSS et JS en fonction du template
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getScriptTags(): string
    {
        $translator = $this->getTranslator();
        $optionSystemService = $this->getOptionSystemService();
        $template = $optionSystemService->getValueByKey(OptionSystemKey::OS_THEME_FRONT_SITE);

        if (!in_array($template, Template::toArray())) {
            throw new HttpException(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $translator->trans('front.error.not.scriptTag', ['template' => $template], domain: 'front_error'),
            );
        }
        return 'front_' . $template;
    }
}
