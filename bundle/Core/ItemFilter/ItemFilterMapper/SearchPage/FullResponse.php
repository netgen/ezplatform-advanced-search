<?php

declare(strict_types=1);

namespace Netgen\Bundle\eZPlatformAdvancedSearchBundle\Core\ItemFilter\ItemFilterMapper\SearchPage;

/**
 * Represents full filter response.
 */
class FullResponse extends PagerResponse
{
    /**
     * @var \Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterResponse\Facet[]
     */
    public $facets = [];

    /**
     * @var \Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterResponse\SelectedFacet[]
     */
    public $selectedFacets = [];

    /**
     * @var string|null
     */
    public $searchTextSuggestion;
}
