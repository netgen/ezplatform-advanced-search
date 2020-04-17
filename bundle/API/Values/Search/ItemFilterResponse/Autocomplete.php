<?php

declare(strict_types=1);

namespace Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterResponse;

use Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\ValueObject;

class Autocomplete extends ValueObject implements \JsonSerializable
{
    /**
     * @var \Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterResponse\AutocompleteItem[]
     */
    public $items;

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
