<?php

declare(strict_types=1);

namespace Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterResponse;

use JsonSerializable;
use Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\ValueObject;

/**
 * Represents a facet item in the filtering response.
 */
final class FacetItem extends ValueObject implements JsonSerializable
{
    public const TYPE_TAG = 'tag';
    public const TYPE_NUMBER = 'number';
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $label;

    /**
     * @var int
     */
    public $count;

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}