<?php

declare(strict_types=1);

namespace Netgen\Bundle\eZPlatformAdvancedSearchBundle\Core\Search;

use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult;
use Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterRequest;
use Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterResponse;
use Symfony\Component\Translation\TranslatorInterface;

class PaginationResolver
{
    /**
     * @var \Symfony\Component\Translation\TranslatorInterface
     */
    private $translator;

    /**
     * @var string
     */
    private $translationDomain;

    /**
     * @var int[]
     */
    private $maxPerPageChoices;

    /**
     * PaginationResolver constructor.
     *
     * @param \Symfony\Component\Translation\TranslatorInterface $translator
     * @param string $translationDomain
     * @param array $maxPerPageChoices
     */
    public function __construct(
        TranslatorInterface $translator,
        string $translationDomain,
        array $maxPerPageChoices
    ) {
        $this->translator = $translator;
        $this->translationDomain = $translationDomain;
        $this->maxPerPageChoices = $maxPerPageChoices;
    }

    /**
     * Sets limit and offset on query, based on current page
     * and max per page.
     *
     * @param \Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterRequest $filterRequest
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query $query
     *
     * @throws \Netgen\EzPlatformSiteApi\API\Exceptions\TranslationNotMatchedException
     * @throws \Ibexa\Core\Base\Exceptions\InvalidArgumentValue
     */
    public function resolveQueryPagination(ItemFilterRequest $filterRequest, Query $query): void
    {
        $page = (int) $filterRequest->currentPage;
        $limit = $filterRequest->maxPerPage;
        $offset = ($page - 1) * $limit;

        $query->limit = $limit;
        $query->offset = $offset;
    }

    /**
     * Sets all pagination parameters in response, based on
     * search results.
     *
     * @param \Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterRequest $filterRequest
     * @param \Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterResponse $response
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query $query
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult $searchResult
     *
     * @throws \Netgen\EzPlatformSiteApi\API\Exceptions\TranslationNotMatchedException
     * @throws \Ibexa\Core\Base\Exceptions\InvalidArgumentValue
     */
    public function resolveResponsePagination(
        ItemFilterRequest $filterRequest,
        ItemFilterResponse $response,
        Query $query,
        SearchResult $searchResult
    ): void {
        $totalCount = $searchResult->totalCount;
        $limit = $query->limit;
        $offset = $query->offset;
        $nbPages = (int) ceil($totalCount / $limit);
        $page = (int) ceil($offset / $limit) + 1;

        $response->nbResults = $totalCount;
        $response->nbResultsText = $this->translate('pager.nb_results', ['%count%' => $response->nbResults]);
        $response->noResultsText = $query->query ? $this->translate('no_results', ['%term%' => urldecode($query->query->value)]) : '';
        $response->nbPages = $nbPages;
        $response->currentPage = $page;
        $response->maxPerPage = $limit;
        $response->maxPerPagePrefix = $this->translate('pager.max_per_page.show');
        $response->maxPerPageSuffix = $this->translate('pager.max_per_page.per_page');
        $response->maxPerPageChoices = $this->maxPerPageChoices;
    }

    private function translate(string $identifier, array $parameters = []): string
    {
        return $this->translator->trans($identifier, $parameters, $this->translationDomain);
    }
}
