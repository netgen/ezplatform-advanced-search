<?php

declare(strict_types=1);

namespace Netgen\Bundle\eZPlatformAdvancedSearchBundle\Core\Pagination\Pagerfanta;

use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use Netgen\EzPlatformSiteApi\API\FindService;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * Class AdvancedFindAdapter.
 */
final class AdvancedFindAdapter implements AdapterInterface
{
    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Query
     */
    private $query;

    /**
     * @var \Netgen\EzPlatformSiteApi\API\FindService
     */
    private $findService;

    /**
     * @var int
     */
    private $nbResults;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Search\Facet[]
     */
    private $facets;

    /**
     * @var float
     */
    private $maxScore;

    /**
     * @var int
     */
    private $time;

    /**
     * @var bool
     */
    private $isExtraInfoInitialized = false;

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Query $query
     * @param \Netgen\EzPlatformSiteApi\API\FindService $findService
     */
    public function __construct(Query $query, FindService $findService)
    {
        $this->query = $query;
        $this->findService = $findService;
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @return int
     */
    public function getNbResults(): int
    {
        $this->initializeExtraInfo();

        return $this->nbResults;
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @return array
     */
    public function getFacets(): array
    {
        $this->initializeExtraInfo();

        return $this->facets;
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @return float
     */
    public function getMaxScore(): float
    {
        $this->initializeExtraInfo();

        return $this->maxScore;
    }

    /**
     * @return int
     */
    public function getTime(): int
    {
        return $this->time;
    }

    /**
     * @param int $offset
     * @param int $length
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @return array|\eZ\Publish\API\Repository\Values\Content\Search\SearchHit[]|\Traversable
     */
    public function getSlice($offset, $length)
    {
        $query = clone $this->query;
        $query->offset = $offset;
        $query->limit = $length;
        $query->performCount = false;

        $searchResult = $this->executeQuery($query);

        $this->time = $searchResult->time;

        if (!$this->isExtraInfoInitialized && $searchResult->totalCount !== null) {
            $this->setExtraInfo($searchResult);
        }

        return $searchResult->searchHits;
    }

    /**
     * Execute the given $query and return SearchResult instance.
     *
     * @param Query $query
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @return SearchResult
     */
    private function executeQuery(Query $query): SearchResult
    {
        if ($query instanceof LocationQuery) {
            return $this->findService->findLocations($query);
        }

        return $this->findService->findContent($query);
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    private function initializeExtraInfo(): void
    {
        if ($this->isExtraInfoInitialized) {
            return;
        }

        $query = clone $this->query;
        $query->limit = 0;
        $searchResult = $this->executeQuery($query);

        $this->setExtraInfo($searchResult);
    }

    /**
     * @param SearchResult $searchResult
     */
    private function setExtraInfo(SearchResult $searchResult): void
    {
        $this->facets = $searchResult->facets;
        $this->maxScore = $searchResult->maxScore;
        $this->nbResults = $searchResult->totalCount;

        $this->isExtraInfoInitialized = true;
    }
}
