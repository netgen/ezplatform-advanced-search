<?php

declare(strict_types=1);

namespace Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\ValueMappers;

use Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterRequest;
use Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterResponse\Sort;

/**
 * SortMapper provides mapping of filter request to sort response and search query sort clause.
 */
abstract class SortMapper
{
    /**
     * @param \Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterRequest $request
     *
     * @return \Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterResponse\Sort
     */
    abstract public function mapSortResponse(ItemFilterRequest $request): Sort;

    /**
     * @param \Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterRequest $filterRequest
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause[]
     */
    abstract public function mapSortClauses(ItemFilterRequest $filterRequest): array;
}
