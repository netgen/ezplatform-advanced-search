<?php

declare(strict_types=1);

namespace Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterResponse;

use JsonSerializable;
use Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\ValueObject;

/**
 * Represents a sort option in the filtering response.
 */
final class SortItem extends ValueObject implements JsonSerializable
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $label;

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
