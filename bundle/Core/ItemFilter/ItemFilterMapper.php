<?php

declare(strict_types=1);

namespace Netgen\Bundle\eZPlatformAdvancedSearchBundle\Core\ItemFilter;

use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult;
use Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterRequest;
use Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterResponse;

/**
 * ItemFilterMapper maps ItemFilterRequest and Pagerfanta to Query and ItemFilterResponse.
 *
 * @see \Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterRequest
 * @see \Ibexa\Contracts\Core\Repository\Values\Content\Query
 * @see \Pagerfanta\Pagerfanta
 * @see \Netgen\EzPlatformSiteApi\Core\Site\Pagination\Pagerfanta\FindAdapter
 * @see \Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterResponse
 */
abstract class ItemFilterMapper
{
    /**
     * Map given $request to Query instance.
     *
     * @param \Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterRequest $filterRequest
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Query
     */
    abstract public function mapQuery(ItemFilterRequest $filterRequest): Query;

    /**
     * Map given $pager to ItemFilterResponse instance.
     *
     * @param \Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterRequest $filterRequest
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query $query
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult $searchResult
     *
     * @return \Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterResponse
     */
    abstract public function mapResponse(ItemFilterRequest $filterRequest, Query $query, SearchResult $searchResult): ItemFilterResponse;
}
