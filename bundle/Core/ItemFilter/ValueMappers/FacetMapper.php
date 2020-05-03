<?php

declare(strict_types=1);

namespace Netgen\Bundle\eZPlatformAdvancedSearchBundle\Core\ItemFilter\ValueMappers;

use eZ\Publish\API\Repository\ObjectStateService;
use eZ\Publish\API\Repository\Values\Content\Search\Facet as ApiFacet;
use Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterRequest;
use Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterResponse\Facet;
use Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterResponse\FacetItem;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Search\Facet\CustomFieldFacet;
use Netgen\EzPlatformSiteApi\Core\Site\LoadService;
use Netgen\TagsBundle\API\Repository\TagsService;
use RuntimeException;

/**
 * FacetMapper provides mapping of eZ Platform search API facet to item filter response facets.
 */
class FacetMapper
{
    /**
     * @var \Netgen\Bundle\eZPlatformAdvancedSearchBundle\Core\ItemFilter\ValueMappers\FacetTitleMapper
     */
    private $facetTitleMapper;

    /**
     * @var TagsService
     */
    private $tagsService;

    /**
     * @var LoadService
     */
    private $loadService;

    /**
     * @var ObjectStateService
     */
    private $objectStateService;

    /**
     * @var array State IDs to be excluded from facets
     */
    private $excludedStates;

    /**
     * @param \Netgen\Bundle\eZPlatformAdvancedSearchBundle\Core\ItemFilter\ValueMappers\FacetTitleMapper $facetTitleMapper
     */
    public function __construct(
        FacetTitleMapper $facetTitleMapper,
        TagsService $tagsService,
        LoadService $loadService,
        ObjectStateService $objectStateService,
        array $excludedStates
    ) {
        $this->facetTitleMapper = $facetTitleMapper;
        $this->tagsService = $tagsService;
        $this->loadService = $loadService;
        $this->objectStateService = $objectStateService;
        $this->excludedStates = $excludedStates;
    }

    /**
     * @param \Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterRequest $itemFilterRequest
     * @param string[] $facetIdentifierMap
     * @param array $facetDefinitions
     * @param \eZ\Publish\API\Repository\Values\Content\Search\Facet[] $facets
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotImplementedException
     * @throws \Netgen\EzPlatformSiteApi\API\Exceptions\TranslationNotMatchedException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *
     * @return \Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterResponse\Facet[]
     */
    public function mapFacets(
        ItemFilterRequest $itemFilterRequest,
        array $facetIdentifierMap,
        array $facetDefinitions,
        array $facets
    ): array {
        $specialFacets = [];

        $regularFacets = $this->recursiveMapRegularFacets(
            $itemFilterRequest,
            $facetIdentifierMap,
            $facetDefinitions,
            $facets
        );

        return \array_merge($specialFacets, $regularFacets);
    }

    /**
     * @param \Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterRequest $itemFilterRequest
     * @param string[] $facetIdentifierMap
     * @param array $facetDefinitions
     * @param \eZ\Publish\API\Repository\Values\Content\Search\Facet[] $facets
     *
     * @throws \Netgen\EzPlatformSiteApi\API\Exceptions\TranslationNotMatchedException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotImplementedException
     *
     * @return \Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterResponse\Facet[]
     */
    private function recursiveMapRegularFacets(
        ItemFilterRequest $itemFilterRequest,
        array $facetIdentifierMap,
        array $facetDefinitions,
        array $facets
    ): array {
        $mappedFacets = [];

        foreach ($facetIdentifierMap as $identifier => $subFacetIdentifierMap) {
            if (!array_key_exists($identifier, $facetDefinitions)) {
                continue;
            }

            $facet = $this->getFacet($facets, $identifier);

            if ($facet === null) {
                /** @noinspection SlowArrayOperationsInLoopInspection */
                $mappedFacets = \array_merge(
                    $mappedFacets,
                    $this->recursiveMapRegularFacets(
                        $productFilterRequest,
                        $subFacetIdentifierMap,
                        $facetDefinitions,
                        $facets
                    )
                );

                continue;
            }

            $definition = $facetDefinitions[$identifier];
            $items = $this->mapItems($facet, $definition);

            if (\count($items) === 0) {
                continue;
            }
            $selectedFacet = $itemFilterRequest->request->query->get($identifier);

            if (null !== $selectedFacet) {
                $newItems = [];
                foreach ($items as $item) {
                    if (!in_array($item->id, $selectedFacet, false)) {
                        $newItems[] = $item;
                    }
                }
                $items = $newItems;
            }

            if (!empty($items)) {
                $mappedFacets[] = new Facet([
                    'title' => $this->facetTitleMapper->mapTitle($identifier),
                    'type' => $this->resolveType($identifier),
                    'initialStateClosed' => $this->getInitialStateClosedByFacetDefinition($definition),
                    'parameterName' => $identifier,
                    'items' => $items,
                ]);
            }
        }

        return $mappedFacets;
    }

    private function getInitialStateClosedByFacetDefinition(array $definition)
    {
        if (array_key_exists('initialStateClosed', $definition)) {
            return $definition['initialStateClosed'];
        }

        return true;
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Search\Facet $facet
     * @param array $definition
     *
     * @return \Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterResponse\FacetItem[]
     */
    private function mapItems(ApiFacet $facet, array $definition): array
    {
        if ($facet instanceof CustomFieldFacet) {
            return $this->mapCustomFieldFacet($facet, $definition);
        }

        $facetClass = \get_class($facet);

        throw new RuntimeException("Facet of type '{$facetClass}' is not handled");
    }

    /**
     * @param \Netgen\EzPlatformSearchExtra\API\Values\Content\Search\Facet\CustomFieldFacet $facet
     * @param array $definition
     *
     * @return \Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterResponse\FacetItem[]
     */
    private function mapCustomFieldFacet(CustomFieldFacet $facet, array $definition): array
    {
        $items = [];

        switch ($definition['type']) {
            case FacetItem::TYPE_TAG:
                $tags = $this->tagsService->loadTagList(array_keys($facet->entries));
                foreach ($facet->entries as $entry => $count) {
                    $label = $tags[$entry]->getKeyword();
                    if ($definition['showCount']) {
                        $label = $label . ' (' . $count . ')';
                    }
                    $items[] = new FacetItem([
                        'id' => $entry,
                        'label' => $label,
                        'count' => $count,
                    ]);
                }

                break;
            case FacetItem::TYPE_NUMBER:
                foreach ($facet->entries as $entry => $count) {
                    $label = $entry;
                    if ($definition['showCount']) {
                        $label = $label . ' (' . $count . ')';
                    }
                    $items[] = new FacetItem([
                        'id' => $entry,
                        'label' => $label,
                        'count' => $count,
                    ]);
                }

                break;
            case FacetItem::TYPE_CONTENT:
                foreach ($facet->entries as $entry => $count) {
                    $content = $this->loadService->loadContent($entry);
                    $label = $entry;
                    if ($content->hasField('title')) {
                        $label = $content->getFieldValue('title')->text;
                    } elseif ($content->hasField('name')) {
                        $label = $content->getFieldValue('name')->text;
                    }
                    if ($definition['showCount']) {
                        $label = $label . ' (' . $count . ')';
                    }
                    $items[] = new FacetItem([
                        'id' => $entry,
                        'label' => $label,
                        'count' => $count,
                    ]);
                }

                break;
            case FacetItem::TYPE_LOCATION:
                foreach ($facet->entries as $entry => $count) {
                    $content = $this->loadService->loadLocation($entry)->content;
                    $label = $entry;
                    if ($content->hasField('title')) {
                        $label = $content->getFieldValue('title')->text;
                    } elseif ($content->hasField('name')) {
                        $label = $content->getFieldValue('name')->text;
                    }
                    if ($definition['showCount']) {
                        $label = $label . ' (' . $count . ')';
                    }
                    $items[] = new FacetItem([
                        'id' => $entry,
                        'label' => $label,
                        'count' => $count,
                    ]);
                }

                break;
            case FacetItem::TYPE_STATE:
                foreach ($facet->entries as $entry => $count) {
                    if (!in_array($entry, $this->excludedStates, true)) {
                        $state = $this->objectStateService->loadObjectState($entry);
                        $label = $state->getName();
                        if ($definition['showCount']) {
                            $label = $label . ' (' . $count . ')';
                        }
                        $items[] = new FacetItem([
                            'id' => $entry,
                            'label' => $label,
                            'count' => $count,
                        ]);
                    }
                }

                break;
        }

        return $items;
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Search\Facet[] $facets
     * @param string $name
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\Facet
     */
    private function getFacet(array $facets, string $name): ?ApiFacet
    {
        foreach ($facets as $facet) {
            if ($facet->name === $name) {
                return $facet;
            }
        }

        return null;
    }

    private function resolveType(string $identifier): string
    {
        return 'list';
    }
}
