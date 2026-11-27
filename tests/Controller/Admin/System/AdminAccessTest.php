<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Pages système ouvertes aux administrateurs et fermées aux contributeurs
 */

namespace App\Tests\Controller\Admin\System;

use App\Tests\AppWebTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class AdminAccessTest extends AppWebTestCase
{
    /**
     * Pages des modules réservés aux administrateurs
     * @return array<string, array{0: string}>
     */
    public static function adminRoutes(): array
    {
        return [
            'options système' => ['admin_option-system_change'],
            'tokens API' => ['admin_api_token_index'],
            'nouveau token' => ['admin_api_token_add'],
            'documentation de l\'API' => ['admin_api_doc_index'],
            'emails' => ['admin_mail_index'],
            'traductions' => ['admin_translation_index'],
        ];
    }

    /**
     * Un contributeur est refusé, un administrateur accède à la page
     * @param string $route
     * @return void
     */
    #[DataProvider('adminRoutes')]
    public function testAdminAccess(string $route): void
    {
        $this->checkNoAccess($route, user: $this->createUserContributeur());

        $admin = $this->createUserAdmin();
        $this->generateDefaultOptionUser($admin);
        $this->client->loginUser($admin, 'admin');
        $this->client->request('GET', $this->router->generate($route));
        $this->assertResponseIsSuccessful();
    }
}
