<?php

declare(strict_types=1);

namespace Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterResponse;

use Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\ValueObject;

class AutocompleteItem extends ValueObject implements \JsonSerializable
{
    /**
     * @var string
     */
    public $term;

    /**
     * @var int
     */
    public $count;

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
