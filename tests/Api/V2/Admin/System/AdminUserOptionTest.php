<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests de la ressource AdminUserOption : options de l'utilisateur de la session
 */

namespace App\Tests\Api\V2\Admin\System;

use App\Api\V2\Resource\Admin\System\AdminUserOption;
use App\Entity\Admin\System\ActionLog;
use App\Entity\Admin\System\OptionUser;
use App\Entity\Admin\System\User;
use App\Enum\Admin\AdminTemplate;
use App\Enum\Admin\System\Options\OptionUser as OptionUserKey;
use App\Tests\Api\V2\AppApiV2TestCase;

class AdminUserOptionTest extends AppApiV2TestCase
{
    /**
     * Adresse de l'option du thème du back-office
     * @var string
     */
    private const string THEME_PATH = '/admin/user_options/ou_admin_theme';

    /**
     * Refus sans session, avec un token seul et avec une session ouverte par un token public
     * @return void
     */
    public function testAccess(): void
    {
        $user = $this->createUser(['disabled' => false, 'anonymous' => false]);
        $publicSession = $this->sessionHeaders($user, 'ROLE_ADMIN_API');

        $this->assertProblem($this->requestV2('GET', '/admin/user_options', self::HEADER_NONE), 401);
        $this->assertProblem(
            $this->requestV2('GET', '/admin/user_options', headers: $this->backOfficeTokenHeaders()),
            403,
        );
        $this->assertProblem($this->requestV2('GET', '/admin/user_options', headers: $publicSession), 403);
        $this->assertProblem(
            $this->requestV2('PATCH', self::THEME_PATH, body: ['value' => 'natheo_atelier'], headers: $publicSession),
            403,
        );
    }

    /**
     * Un simple membre lit ses options dans l'ordre de la configuration, valeur par défaut sans ligne en base
     * @return void
     */
    public function testCollection(): void
    {
        $response = $this->requestV2('GET', '/admin/user_options', headers: $this->userHeaders($user));
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertMatchesResourceCollectionJsonSchema(AdminUserOption::class);

        $members = array_column($this->decode($response)['member'], null, 'id');
        $this->assertSame(
            ['ou_default_language', 'ou_nb_element', 'ou_admin_theme', 'ou_default_personal_data_render'],
            array_keys($members),
        );
        $theme = $members['ou_admin_theme'];
        $this->assertSame(AdminTemplate::default()->value, $theme['value']);
        $this->assertSame(
            [
                ['value' => 'natheo_studio', 'label' => 'Natheo-Studio'],
                ['value' => 'natheo_atelier', 'label' => 'Natheo-Atelier'],
            ],
            array_map(
                fn(array $choice) => array_intersect_key($choice, ['value' => 1, 'label' => 1]),
                $theme['choices'],
            ),
        );
        $this->assertTrue($theme['permissions']['edit']);
    }

    /**
     * Lecture d'une option, 404 pour une clé inconnue
     * @return void
     */
    public function testGet(): void
    {
        $headers = $this->userHeaders($user);
        $response = $this->requestV2('GET', self::THEME_PATH, headers: $headers);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertMatchesResourceItemJsonSchema(AdminUserOption::class);
        $this->assertSame(
            $this->translator->trans('options_user.admin_theme.label'),
            $this->decode($response)['label'],
        );

        $content = $this->assertProblem(
            $this->requestV2('GET', '/admin/user_options/ou_inconnue', headers: $headers),
            404,
        );
        $this->assertSame($this->translator->trans('user.option.error.not_found', domain: 'user'), $content['detail']);
    }

    /**
     * Modification enregistrée pour l'utilisateur seul et tracée, 422 pour une valeur hors liste
     * @return void
     */
    public function testPatch(): void
    {
        $headers = $this->userHeaders($user);
        $other = $this->createUser(['disabled' => false, 'anonymous' => false]);

        $response = $this->requestV2('PATCH', self::THEME_PATH, body: ['value' => 'natheo_atelier'], headers: $headers);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertMatchesResourceItemJsonSchema(AdminUserOption::class);
        $this->assertSame('natheo_atelier', $this->decode($response)['value']);
        $this->assertSame('natheo_atelier', $this->storedValue($user));
        $this->assertNull($this->storedValue($other));

        $log = $this->em->getRepository(ActionLog::class)->findOneBy(['module' => 'my_account']);
        $this->assertInstanceOf(ActionLog::class, $log);
        $this->assertSame(
            ['update', 'ou_admin_theme', 'natheo_atelier'],
            [$log->getAction(), $log->getResourceId(), $log->getLabel()],
        );

        foreach ([['value' => 'inconnu'], ['value' => null], []] as $body) {
            $content = $this->assertProblem(
                $this->requestV2('PATCH', self::THEME_PATH, body: $body, headers: $headers),
                422,
            );
            $this->assertSame('value', $content['violations'][0]['propertyPath']);
        }
        $this->assertProblem(
            $this->requestV2('PATCH', '/admin/user_options/ou_inconnue', body: ['value' => 'x'], headers: $headers),
            404,
        );
        $this->assertSame('natheo_atelier', $this->storedValue($user));
    }

    /**
     * Un If-Match périmé donne 412
     * @return void
     */
    public function testConcurrency(): void
    {
        $headers = $this->userHeaders();
        $etag = $this->requestV2('GET', self::THEME_PATH, headers: $headers)->headers->get('ETag');
        $this->assertNotNull($etag);

        $response = $this->requestV2(
            'PATCH',
            self::THEME_PATH,
            body: ['value' => 'natheo_atelier'],
            headers: ['HTTP_IF_MATCH' => $etag, ...$headers],
        );
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertProblem(
            $this->requestV2(
                'PATCH',
                self::THEME_PATH,
                body: ['value' => 'natheo_studio'],
                headers: ['HTTP_IF_MATCH' => $etag, ...$headers],
            ),
            412,
        );
    }

    /**
     * En-têtes d'une session back-office d'un nouveau membre
     * @param User|null $user rempli avec le membre créé
     * @return array<string, string>
     */
    private function userHeaders(?User &$user = null): array
    {
        $user = $this->createUser(['disabled' => false, 'anonymous' => false]);
        return $this->adminSessionHeaders($user);
    }

    /**
     * Valeur enregistrée en base du thème du back-office d'un utilisateur, null sans ligne
     * @param User $user
     * @return string|null
     */
    private function storedValue(User $user): ?string
    {
        $this->em->clear();
        $option = $this->em
            ->getRepository(OptionUser::class)
            ->findOneBy(['user' => $user->getId(), 'key' => OptionUserKey::OU_ADMIN_THEME->value]);
        return $option?->getValue();
    }
}
