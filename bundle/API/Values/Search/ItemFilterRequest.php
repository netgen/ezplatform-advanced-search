<?php

declare(strict_types=1);

namespace Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search;

use Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\ValueObject;

/**
 * Used to perform product filter query.
 */
final class ItemFilterRequest extends ValueObject
{
    /**
     * @var string
     */
    public $mapperIdentifier;

    /**
     * @var int
     */
    public $currentPage;

    /**
     * @var int
     */
    public $maxPerPage;

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    public $request;

    /**
     * @todo
     * One of category|brand|premium_category|premium_brand|search
     *
     * @var string
     */
    public $pageType = 'search';

    /**
     * @var array
     */
    public $parameters = [];
}
