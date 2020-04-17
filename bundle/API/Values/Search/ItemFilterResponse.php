<?php

declare(strict_types=1);

namespace Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search;

use Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\ValueObject;

/**
 * Represents item filtering response.
 */
abstract class ItemFilterResponse extends ValueObject implements \JsonSerializable
{
    /**
     * @var int
     */
    public $nbResults;

    /**
     * @var \Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterResponse\Sort
     */
    public $sort;

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
