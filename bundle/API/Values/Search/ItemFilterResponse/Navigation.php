<?php

declare(strict_types=1);

namespace Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterResponse;

use JsonSerializable;
use Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\ValueObject;

/**
 * Represents selected categories in the filtering response.
 */
final class Navigation extends ValueObject implements JsonSerializable
{
    /**
     * Navigation item Location ID.
     *
     * @var int|string
     */
    public $id;

    /**
     * @var int
     */
    public $label;

    /**
     * @var \Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterResponse\Navigation[]
     */
    public $subItems = [];

    /**
     * Navigation parameters, with parameter name as key and parameter value as value.
     *
     * @var array
     */
    public $parameters = [];

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
