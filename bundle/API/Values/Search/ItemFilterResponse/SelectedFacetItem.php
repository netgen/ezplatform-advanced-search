<?php

declare(strict_types=1);

namespace Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterResponse;

use JsonSerializable;
use Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\ValueObject;

/**
 * Represents a selected facet item in the filtering response.
 */
final class SelectedFacetItem extends ValueObject implements JsonSerializable
{
    /**
     * @var int
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
