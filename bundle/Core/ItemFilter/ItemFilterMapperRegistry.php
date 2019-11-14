<?php

declare(strict_types=1);

namespace Netgen\Bundle\eZPlatformAdvancedSearchBundle\Core\ItemFilter;

use OutOfBoundsException;

/**
 * Registry for ItemFilterMappers.
 */
final class ItemFilterMapperRegistry
{
    /**
     * @var \Netgen\Bundle\eZPlatformAdvancedSearchBundle\Core\ItemFilter\ItemFilterMapper[]
     */
    private $mappersByIdentifier = [];

    /**
     * @var \Netgen\Bundle\eZPlatformAdvancedSearchBundle\Core\ItemFilter\ItemFilterMapper[]
     */
    public function __construct(array $mappersByIdentifier = [])
    {
        foreach ($mappersByIdentifier as $identifier => $mapper) {
            $this->register($mapper, $identifier);
        }
    }

    /**
     * Add $mapper to the internal collection with $identifier.
     *
     * @param \Netgen\Bundle\eZPlatformAdvancedSearchBundle\Core\ItemFilter\ItemFilterMapper $mapper
     * @param string $identifier
     */
    public function register(ItemFilterMapper $mapper, string $identifier): void
    {
        $this->mappersByIdentifier[$identifier] = $mapper;
    }

    /**
     * Return mapper with the given $identifier.
     *
     * @param string $identifier
     *
     * @return \Netgen\Bundle\eZPlatformAdvancedSearchBundle\Core\ItemFilter\ItemFilterMapper
     */
    public function get(string $identifier): ItemFilterMapper
    {
        if (!array_key_exists($identifier, $this->mappersByIdentifier)) {
            throw new OutOfBoundsException(
                "Mapper with the given identifier '{$identifier}' was not found"
            );
        }

        return $this->mappersByIdentifier[$identifier];
    }
}
