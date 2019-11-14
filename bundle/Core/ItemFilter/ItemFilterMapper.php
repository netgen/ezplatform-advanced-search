<?php

declare(strict_types=1);

namespace Netgen\Bundle\eZPlatformAdvancedSearchBundle\Core\ItemFilter;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterRequest;
use Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterResponse;

/**
 * ItemFilterMapper maps ItemFilterRequest and Pagerfanta to Query and ItemFilterResponse.
 *
 * @see \Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterRequest
 * @see \eZ\Publish\API\Repository\Values\Content\Query
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
     * @return \eZ\Publish\API\Repository\Values\Content\Query
     */
    abstract public function mapQuery(ItemFilterRequest $filterRequest): Query;

    /**
     * Map given $pager to ItemFilterResponse instance.
     *
     * @param \Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterRequest $filterRequest
     * @param \eZ\Publish\API\Repository\Values\Content\Query $query
     * @param \eZ\Publish\API\Repository\Values\Content\Search\SearchResult $searchResult
     *
     * @return \Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterResponse
     */
    abstract public function mapResponse(ItemFilterRequest $filterRequest, Query $query, SearchResult $searchResult): ItemFilterResponse;
}
