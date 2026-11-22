<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test du validateur de la valeur d'une option de l'utilisateur
 */

namespace App\Tests\Api\V2\Validator\Admin\System;

use App\Api\V2\Input\Admin\System\UpdateUserOptionInput;
use App\Api\V2\Resource\Admin\System\AdminUserOption;
use App\Api\V2\Validator\Admin\System\ValidUserOptionInput;
use App\Api\V2\Validator\Admin\System\ValidUserOptionInputValidator;
use App\Service\Api\Admin\System\ApiAdminUserOptionService;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\ConstraintValidatorFactory;
use Symfony\Component\Validator\Validation;

class ValidUserOptionInputValidatorTest extends KernelTestCase
{
    /**
     * Configurations et valeurs, avec le nombre de violations attendu
     * @return array<string, array{0: array, 1: string|null, 2: int}>
     */
    public static function provideValues(): array
    {
        $select = ['type' => 'select', 'list_value' => 'a:label.a|b:label.b'];
        return [
            'valeur de la liste' => [$select, 'b', 0],
            'valeur hors liste' => [$select, 'c', 1],
            'valeur absente' => [$select, null, 1],
            'option désactivée' => [$select + ['disabled' => true], 'a', 1],
        ];
    }

    /**
     * Refuse une valeur hors liste, absente ou destinée à une option désactivée
     * @param array $config
     * @param string|null $value
     * @param int $violations
     * @return void
     */
    #[DataProvider('provideValues')]
    public function testValidate(array $config, ?string $value, int $violations): void
    {
        self::bootKernel();
        /** @var ApiAdminUserOptionService $service */
        $service = static::getContainer()->get(ApiAdminUserOptionService::class);
        $stub = $this->createStub(ApiAdminUserOptionService::class);
        $stub->method('findConfig')->willReturn(['OU_TEST', $config]);
        $stub->method('isValidValue')->willReturnCallback($service->isValidValue(...));

        $previous = new AdminUserOption();
        $previous->id = 'ou_test';
        $requestStack = new RequestStack();
        $requestStack->push(new Request(attributes: ['previous_data' => $previous]));

        $validator = Validation::createValidatorBuilder()
            ->setConstraintValidatorFactory(
                new ConstraintValidatorFactory([
                    ValidUserOptionInputValidator::class => new ValidUserOptionInputValidator($stub, $requestStack),
                ]),
            )
            ->getValidator();
        $input = new UpdateUserOptionInput();
        $input->value = $value;

        $this->assertCount($violations, $validator->validate($input, new ValidUserOptionInput()));
    }
}
