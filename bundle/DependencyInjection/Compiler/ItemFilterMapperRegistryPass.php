<?php

declare(strict_types=1);

namespace Netgen\Bundle\eZPlatformAdvancedSearchBundle\DependencyInjection\Compiler;

use LogicException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This compiler pass will register item filter mappers.
 *
 * @see \Netgen\Bundle\eZPlatformAdvancedSearchBundle\Core\ItemFilter\ItemFilterMapper
 */
final class ItemFilterMapperRegistryPass implements CompilerPassInterface
{
    /**
     * Service ID of the item filter mapper registry.
     *
     * @see \Netgen\Bundle\eZPlatformAdvancedSearchBundle\Core\ItemFilter\ItemFilterMapperRegistry
     *
     * @var string
     */
    private static $mapperRegistryId = 'netgen_ez_platform_advanced_search.item_filter.item_filter_mapper.registry';

    /**
     * Service tag used for item filter mappers.
     *
     * @see \Netgen\Bundle\eZPlatformAdvancedSearchBundle\Core\ItemFilter\ItemFilterMapper
     *
     * @var string
     */
    private static $mapperTag = 'netgen_ez_platform_advanced_search.item_filter_mapper';

    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(static::$mapperRegistryId)) {
            return;
        }

        $mapperRegistryDefinition = $container->getDefinition(static::$mapperRegistryId);
        $mappers = $container->findTaggedServiceIds(static::$mapperTag);

        foreach ($mappers as $id => $attributes) {
            $this->registerResolver($mapperRegistryDefinition, $id, $attributes);
        }
    }

    /**
     * Add method call to register mapper with given $id with mapper registry.
     *
     * @param \Symfony\Component\DependencyInjection\Definition $mapperRegistryDefinition
     * @param string $id
     * @param array $attributes
     *
     * @throws \LogicException
     */
    private function registerResolver(Definition $mapperRegistryDefinition, string $id, array $attributes): void
    {
        foreach ($attributes as $attribute) {
            if (!isset($attribute['identifier'])) {
                $mapperTag = static::$mapperTag;

                throw new LogicException(
                    "Service tag '{$mapperTag}' needs an 'identifier' attribute to identify the mapper"
                );
            }

            $mapperRegistryDefinition->addMethodCall(
                'register',
                [
                    new Reference($id),
                    $attribute['identifier'],
                ]
            );
        }
    }
}
