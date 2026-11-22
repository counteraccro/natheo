<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Options de l'utilisateur lues et modifiées par l'API d'administration, d'après la configuration options_user.yaml
 */

namespace App\Service\Api\Admin\System;

use App\Entity\Admin\System\OptionUser;
use App\Entity\Admin\System\User;
use App\Service\Api\AppApiService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ApiAdminUserOptionService extends AppApiService
{
    /**
     * Configuration de chaque option, indexée par sa clé, dans l'ordre du fichier
     * @return array<string, array>
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getConfigs(): array
    {
        return $this->getOptionConfigService()->flatten($this->getOptionUserService()->getOptionsUserConfig());
    }

    /**
     * Libellés traduits d'une option : libellé, aide et valeurs d'une liste (« valeur:clé de traduction|… »)
     * @param string $key
     * @param array $config
     * @return array{label: string, help: string|null, choices: array<string, string>}
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getLabels(string $key, array $config): array
    {
        $translator = $this->getTranslator();
        $choices = [];
        if (($config['type'] ?? null) === 'select' && isset($config['list_value'])) {
            foreach (explode('|', (string) $config['list_value']) as $item) {
                [$value, $label] = array_pad(explode(':', $item, 2), 2, '');
                $choices[$value] = $translator->trans($label);
            }
        }
        return [
            'label' => $translator->trans((string) ($config['label'] ?? $key)),
            'help' => isset($config['help']) ? $translator->trans((string) $config['help']) : null,
            'choices' => $choices,
        ];
    }

    /**
     * Configuration d'une option désignée par le slug de sa clé (ou_admin_theme), null si inconnue
     * @param string $slug
     * @return array{0: string, 1: array}|null clé et configuration
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function findConfig(string $slug): ?array
    {
        foreach ($this->getConfigs() as $key => $config) {
            if (strtolower($key) === $slug) {
                return [$key, $config];
            }
        }
        return null;
    }

    /**
     * Valeurs enregistrées de l'utilisateur, indexées par clé ; une option sans ligne garde sa valeur par défaut
     * @param User $user
     * @return array<string, string|null>
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getValues(User $user): array
    {
        $rows = $this->getRepository(OptionUser::class)
            ->createQueryBuilder('o')
            ->select('o.key', 'o.value')
            ->where('o.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getArrayResult();
        return array_column($rows, 'value', 'key');
    }

    /**
     * Valeur d'une option de l'utilisateur, celle de la configuration par défaut sans ligne en base
     * @param array<string, string|null> $values
     * @param string $key
     * @param array $config
     * @return string
     */
    public function getValue(array $values, string $key, array $config): string
    {
        return (string) ($values[$key] ?? ($config['default'] ?? ''));
    }

    /**
     * Indique si la valeur est acceptée par une option modifiable
     * @param array $config
     * @param string $value
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function isValidValue(array $config, string $value): bool
    {
        $optionConfigService = $this->getOptionConfigService();
        return $optionConfigService->isEditable($config) && $optionConfigService->isValidValue($config, $value);
    }

    /**
     * Enregistre la valeur d'une option, la ligne est créée pour un utilisateur qui ne l'a pas encore
     * @param User $user
     * @param string $key
     * @param string $value
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function update(User $user, string $key, string $value): void
    {
        $this->getOptionUserService()->saveValueForUser($user, $key, $value);
    }
}
