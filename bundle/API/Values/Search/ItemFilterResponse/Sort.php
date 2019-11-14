<?php

declare(strict_types=1);

namespace Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterResponse;

use JsonSerializable;
use Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\ValueObject;

/**
 * Represents sort definition in the filtering response.
 */
final class Sort extends ValueObject implements JsonSerializable
{
    /**
     * @var string
     */
    public $label;

    /**
     * @var string
     */
    public $parameterName;

    /**
     * @var string
     */
    public $activeItemId;

    /**
     * @var \Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterResponse\SortItem[]
     */
    public $items = [];

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
