<?php

declare(strict_types=1);

namespace Netgen\Bundle\eZPlatformAdvancedSearchBundle\Core\ItemFilter\ItemFilterMapper\SearchPage;

use Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterResponse;

/**
 * Represents full filter response.
 */
final class FullResponse extends ItemFilterResponse
{
    /**
     * @var \Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterResponse\Facet[]
     */
    public $facets = [];

    /**
     * @var \Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterResponse\SelectedFacet[]
     */
    public $selectedFacets = [];
}
