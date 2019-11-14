<?php

declare(strict_types=1);

namespace  Netgen\Bundle\eZPlatformAdvancedSearchBundle\Core\Search;

use Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Search\ItemFilterService as ItemFilterServiceInterface;
use Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterRequest;
use Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterResponse;
use Netgen\Bundle\eZPlatformAdvancedSearchBundle\Core\ItemFilter\ItemFilterMapper;
use Netgen\Bundle\eZPlatformAdvancedSearchBundle\Core\ItemFilter\ItemFilterMapperRegistry;
use Netgen\EzPlatformSiteApi\API\FindService;
use Netgen\EzPlatformSiteApi\Core\Traits\SiteAwareTrait;

class ItemFilterService implements ItemFilterServiceInterface
{
    use SiteAwareTrait;

    /**
     * @var ItemFilterMapperRegistry
     */
    private $itemFilterMapperRegistry;

    /**
     * @var \Netgen\EzPlatformSiteApi\API\FindService
     */
    private $findService;

    /**
     * @param ItemFilterMapperRegistry $itemFilterMapperRegistry
     * @param \Netgen\EzPlatformSiteApi\API\FindService $findService
     */
    public function __construct(ItemFilterMapperRegistry $itemFilterMapperRegistry, FindService $findService)
    {
        $this->itemFilterMapperRegistry = $itemFilterMapperRegistry;
        $this->findService = $findService;
    }

    /**
     * @param ItemFilterRequest $request
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @return ItemFilterResponse
     */
    public function filter(ItemFilterRequest $request): ItemFilterResponse
    {
        /** @var ItemFilterMapper $mapper */
        $mapper = $this->itemFilterMapperRegistry->get($request->mapperIdentifier);
        $query = $mapper->mapQuery($request);

        $searchResult = $this->findService->findContent($query);

        return $mapper->mapResponse($request, $query, $searchResult);
    }
}
