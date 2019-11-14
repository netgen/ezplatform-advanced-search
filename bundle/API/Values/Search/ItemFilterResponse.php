<?php

declare(strict_types=1);

namespace Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search;

use JsonSerializable;
use Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\ValueObject;

/**
 * Represents product filtering response.
 */
abstract class ItemFilterResponse extends ValueObject implements JsonSerializable
{
    /**
     * @var int
     */
    public $nbResults;

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
     * @var \Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterResponse\Sort
     */
    public $sort;

    /**
     * An array of items.
     *
     * @var \Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterResponse|array
     */
    public $items;

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
