<?php

declare(strict_types=1);

namespace Netgen\Bundle\eZPlatformAdvancedSearchBundle\Controller;

use Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Search\ItemFilterService;
use Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterRequest;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides JSON API for item filter.
 */
class ItemFilterApiController extends Controller
{
    /**
     * @var \Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Search\ItemFilterService
     */
    private $itemFilterService;

    /**
     * @var int[]
     */
    private $maxPerPageChoices;

    /**
     * @var int
     */
    private $defaultMaxPerPage;

    /**
     * @param \Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Search\ItemFilterService $itemFilterService
     * @param int[] $maxPerPageChoices
     * @param int $defaultMaxPerPage
     */
    public function __construct(
        ItemFilterService $itemFilterService,
        array $maxPerPageChoices,
        int $defaultMaxPerPage
    ) {
        $this->itemFilterService = $itemFilterService;
        $this->maxPerPageChoices = $maxPerPageChoices;
        $this->defaultMaxPerPage = $defaultMaxPerPage;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $mapper identifier of mapper used for items
     *
     * @throws \Exception
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function fullResponse(Request $request, string $mapper): JsonResponse
    {
        $itemFilterRequest = $this->getItemFilterRequest($request);
        $itemFilterRequest->mapperIdentifier = $mapper;

        return $this->getResponse($itemFilterRequest);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $mapper identifier of mapper used for items
     *
     * @throws \Exception
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function pagerResponse(Request $request, string $mapper): JsonResponse
    {
        $itemFilterRequest = $this->getItemFilterRequest($request);
        $itemFilterRequest->mapperIdentifier = $mapper;
        $itemFilterRequest->parameters = [
            'pagerResponse' => true,
        ];

        return $this->getResponse($itemFilterRequest);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @throws \Exception
     *
     * @return \Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterRequest
     */
    private function getItemFilterRequest(Request $request): ItemFilterRequest
    {
        return new ItemFilterRequest([
            'currentPage' => $request->query->get('currentPage', 1),
            'maxPerPage' => $this->resolveMaxPerPage($request),
            'request' => $request,
            'pageType' => $this->getPageType($request),
        ]);
    }

    /**
     * @param \Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterRequest $itemFilterRequest
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    private function getResponse(ItemFilterRequest $itemFilterRequest): JsonResponse
    {
        $itemFilterResponse = $this->itemFilterService->filter(
            $itemFilterRequest
        );

        return new JsonResponse($itemFilterResponse);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return int
     */
    private function resolveMaxPerPage(Request $request): int
    {
        $maxPerPage = (int) $request->query->get('maxPerPage', $this->defaultMaxPerPage);

        if (\in_array($maxPerPage, $this->maxPerPageChoices, true)) {
            return $maxPerPage;
        }

        return $this->defaultMaxPerPage;
    }

    /**
     * Returns page type, possible values are: category|brand|premium_brand|search.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return string
     */
    private function getPageType(Request $request): string
    {
        return $request->query->get('pageType', 'search');
    }
}
