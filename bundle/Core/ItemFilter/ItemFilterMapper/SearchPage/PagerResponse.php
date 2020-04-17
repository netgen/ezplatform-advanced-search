<?php

declare(strict_types=1);

namespace Netgen\Bundle\eZPlatformAdvancedSearchBundle\Core\ItemFilter\ItemFilterMapper\SearchPage;

use Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterResponse;

/**
 * Represents filter response for pagination use only.
 */
class PagerResponse extends ItemFilterResponse
{
    /**
     * @var string
     */
    public $nbResultsText;

    /**
     * @var string
     */
    public $noResultsText;

    /**
     * @var int
     */
    public $maxPerPage;

    /**
     * @var string
     */
    public $maxPerPagePrefix;

    /**
     * @var string
     */
    public $maxPerPageSuffix;

    /**
     * @var int
     */
    public $currentPage;

    /**
     * @var int
     */
    public $nbPages;

    /**
     * @var array
     */
    public $items = [];
}
