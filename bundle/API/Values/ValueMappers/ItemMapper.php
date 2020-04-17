<?php

declare(strict_types=1);

namespace Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\ValueMappers;

use Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\ValueObject;
use Netgen\EzPlatformSiteApi\API\Values\Content;

abstract class ItemMapper
{
    /**
     * Map given $contentItems to search response item instances.
     *
     * @param \Netgen\EzPlatformSiteApi\API\Values\Content[] $contentItems
     *
     * @return \Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\ValueObject[]
     */
    public function mapItems(array $contentItems): array
    {
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

    abstract public function mapItem(Content $content): ?ValueObject;
}
