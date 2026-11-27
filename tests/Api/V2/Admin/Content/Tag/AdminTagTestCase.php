<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Base des tests de la ressource AdminTag : tags avec un libellé par langue du site
 */

namespace App\Tests\Api\V2\Admin\Content\Tag;

use App\Entity\Admin\Content\Tag\Tag;
use App\Entity\Admin\System\ActionLog;
use App\Entity\Admin\System\User;
use App\Enum\Admin\System\Options\OptionSystem;
use App\Service\Admin\System\OptionSystemService;
use App\Tests\Api\V2\AppApiV2TestCase;

abstract class AdminTagTestCase extends AppApiV2TestCase
{
    /**
     * Tag avec un libellé par langue du site, « <label> » en français et « <label> en|es » ailleurs
     * @param User|null $user
     * @param string $label
     * @param array $customData
     * @return Tag
     */
    protected function createLabelledTag(?User $user, string $label, array $customData = []): Tag
    {
        $tag = $this->createTag($user, array_merge(['color' => '#112233', 'disabled' => false], $customData));
        foreach (['fr', 'en', 'es'] as $locale) {
            $this->createTagTranslation($tag, [
                'locale' => $locale,
                'label' => $locale === 'fr' ? $label : $label . ' ' . $locale,
            ]);
        }
        return $tag;
    }

    /**
     * En-têtes d'une session back-office d'un nouveau contributeur
     * @param User|null $user rempli avec le contributeur créé
     * @return array<string, string>
     */
    protected function contributorHeaders(?User &$user = null): array
    {
        $user = $this->createUserContributeur(['disabled' => false, 'anonymous' => false]);
        return $this->adminSessionHeaders($user);
    }

    /**
     * En-têtes d'une session back-office d'un nouvel administrateur
     * @param User|null $user
     * @return array<string, string>
     */
    protected function adminHeaders(?User &$user = null): array
    {
        $user = $this->createUserAdmin(['disabled' => false, 'anonymous' => false]);
        return $this->adminSessionHeaders($user);
    }

    /**
     * @param bool $allow
     * @return void
     */
    protected function setAllowDelete(bool $allow): void
    {
        $this->container
            ->get(OptionSystemService::class)
            ->saveValueByKee(OptionSystem::OS_ALLOW_DELETE_DATA->value, $allow ? '1' : '0');
    }

    /**
     * Actions du journal, de la plus ancienne à la plus récente
     * @return array<int, array{0: string|null, 1: string|null, 2: string|null}>
     */
    protected function logs(): array
    {
        return array_map(
            fn(ActionLog $log) => [$log->getAction(), $log->getResourceId(), $log->getLabel()],
            $this->em->getRepository(ActionLog::class)->findBy(['module' => 'tag'], ['id' => 'ASC']),
        );
    }
}
