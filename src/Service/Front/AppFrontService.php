<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * AppService pour le front
 */

namespace App\Service\Front;

use App\Enum\Admin\System\Options\OptionSystem;
use App\Enum\Front\Template;
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
        return 'front/' . $this->resolveTemplate('front_error.index.no_template')->value;
    }

    /**
     * Retourne la clé de l'entrée Vite du thème actif (front_<thème>), pour ses scripts et ses styles
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getScriptTags(): string
    {
        return 'front_' . $this->resolveTemplate('front_error.index.no_script_tag')->value;
    }

    /**
     * Thème de l'option system OS_THEME_FRONT_SITE, erreur 500 traduite par $errorKey s'il est inconnu
     * @param string $errorKey
     * @return Template
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function resolveTemplate(string $errorKey): Template
    {
        $value = (string) $this->getOptionSystemService()->getValueByKey(OptionSystem::OS_THEME_FRONT_SITE->value);
        $template = Template::tryFrom($value);
        if ($template === null) {
            throw new HttpException(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $this->getTranslator()->trans($errorKey, ['template' => $value], domain: 'front_error'),
            );
        }
        return $template;
    }
}
