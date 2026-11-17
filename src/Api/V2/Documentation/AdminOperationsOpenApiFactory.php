<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Retire les opérations d'administration, et les schémas qu'elles seules utilisent, de la documentation publique
 */

namespace App\Api\V2\Documentation;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model\Paths;
use ApiPlatform\OpenApi\Model\Tag;
use ApiPlatform\OpenApi\OpenApi;
use App\Api\V2\Metadata\OperationSecurityMetadataFactory;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;

#[AsDecorator(decorates: 'api_platform.openapi.factory')]
final readonly class AdminOperationsOpenApiFactory implements OpenApiFactoryInterface
{
    /**
     * Clé de contexte qui demande la documentation complète, administration comprise
     * @var string
     */
    public const string WITH_ADMIN = 'natheo_with_admin';

    /**
     * Préfixe des références vers les schémas
     * @var string
     */
    private const string SCHEMA_REF = '#/components/schemas/';

    public function __construct(
        #[AutowireDecorated] private OpenApiFactoryInterface $decorated,
        #[Autowire('%app.api_v2.prefix%')] private string $apiPrefix,
    ) {}

    /**
     * @param array $context
     * @return OpenApi
     */
    public function __invoke(array $context = []): OpenApi
    {
        $openApi = ($this->decorated)($context);
        if (($context[self::WITH_ADMIN] ?? false) === true) {
            return $openApi;
        }

        $adminPrefix = rtrim($this->apiPrefix, '/') . OperationSecurityMetadataFactory::ADMIN_PREFIX;
        $public = new Paths();
        $publicRefs = [];
        $adminRefs = [];
        $tags = [];
        foreach ($openApi->getPaths()->getPaths() as $path => $pathItem) {
            if (str_starts_with($path, $adminPrefix)) {
                $this->collectRefs($pathItem, $adminRefs);
                continue;
            }
            $public->addPath($path, $pathItem);
            $this->collectRefs($pathItem, $publicRefs);
            foreach (['Get', 'Put', 'Post', 'Delete', 'Patch'] as $method) {
                array_push($tags, ...$pathItem->{'get' . $method}()?->getTags() ?? []);
            }
        }

        $schemas = $openApi->getComponents()->getSchemas() ?? new \ArrayObject();
        $adminOnly = array_diff($this->closure($adminRefs, $schemas), $this->closure($publicRefs, $schemas));
        $publicSchemas = new \ArrayObject(array_diff_key($schemas->getArrayCopy(), array_flip($adminOnly)));

        return $openApi
            ->withPaths($public)
            ->withComponents($openApi->getComponents()->withSchemas($publicSchemas))
            ->withTags(
                array_values(array_filter($openApi->getTags(), fn(Tag $tag) => in_array($tag->getName(), $tags, true))),
            );
    }

    /**
     * Noms des schémas référencés, directement ou par d'autres schémas
     * @param string[] $refs
     * @param \ArrayObject $schemas
     * @return string[]
     */
    private function closure(array $refs, \ArrayObject $schemas): array
    {
        $names = [];
        while ($refs !== []) {
            $name = substr((string) array_pop($refs), strlen(self::SCHEMA_REF));
            if (isset($names[$name]) || !isset($schemas[$name])) {
                continue;
            }
            $names[$name] = true;
            $this->collectRefs($schemas[$name], $refs);
        }
        return array_keys($names);
    }

    /**
     * Références vers les schémas contenues dans un modèle OpenAPI (objets lus par leurs propriétés)
     * @param mixed $value
     * @param string[] $refs
     * @return void
     */
    private function collectRefs(mixed $value, array &$refs): void
    {
        if ($value instanceof \ArrayObject) {
            $value = $value->getArrayCopy();
        } elseif (is_object($value)) {
            $value = (array) $value;
        }
        if (!is_array($value)) {
            return;
        }
        foreach ($value as $key => $item) {
            if ($key === '$ref' && is_string($item) && str_starts_with($item, self::SCHEMA_REF)) {
                $refs[] = $item;
            } else {
                $this->collectRefs($item, $refs);
            }
        }
    }
}
