<?php

declare(strict_types=1);

namespace Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterResponse;

use JsonSerializable;
use Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\ValueObject;

/**
 * Represents a facet in filtering response.
 */
final class Facet extends ValueObject implements JsonSerializable
{
    public const FacetTypeList = 'list';

    /**
     * @var string
     */
    public $title;

    /**
     * One of the self::FacetType* constants.
     *
     * @var string
     */
    public $type;

    /**
     * Whether the initial state of the facet is closed.
     *
     * @var string
     */
    public $initialStateClosed;

    /**
     * @var array
     */
    public $selectedValue;

    /**
     * @var string
     */
    public $parameterName;

    /**
     * @var \Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterResponse\FacetItem[]
     */
    public $items = [];

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
