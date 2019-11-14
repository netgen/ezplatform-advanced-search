<?php

declare(strict_types=1);

namespace Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\ValueMappers;

use Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\ValueObject;
use Netgen\EzPlatformSiteApi\API\Values\Content;

abstract class ItemMapper
{
    /**
     * Map given $content to search response item instance.
     *
     * @param \iterable $contentItems
     *
     * @return ValueObject[]
     */
    public function mapItems(
        iterable $contentItems
    ): array {
        $items = [];

        foreach ($contentItems as $content) {
            $item = $this->mapItem($content);

            if ($item === null) {
                continue;
            }

            $items[] = $item;
        }

        return $items;
    }

    abstract public function mapItem(
        Content $content
    ): ?ValueObject;
}
