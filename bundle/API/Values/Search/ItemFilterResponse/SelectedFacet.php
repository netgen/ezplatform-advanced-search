<?php

declare(strict_types=1);

namespace Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterResponse;

use JsonSerializable;
use Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\ValueObject;

/**
 * Represents a selected facet in the filtering response.
 */
final class SelectedFacet extends ValueObject implements JsonSerializable
{
    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $parameterName;

    /**
     * @var SelectedFacetItem[]
     */
    public $items;

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
