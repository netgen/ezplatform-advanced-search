<?php

declare(strict_types=1);

namespace Netgen\Bundle\eZPlatformAdvancedSearchBundle\Core\ItemFilter\ValueMappers;

use Ibexa\Contracts\Core\Repository\ObjectStateService;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\Facet as ApiFacet;
use Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterRequest;
use Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterResponse\Facet;
use Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterResponse\FacetItem;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Search\Facet\CustomFieldFacet;
use Netgen\EzPlatformSearchExtra\Core\Search\Solr\API\Facet\RawFacet;
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
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Search\Facet[] $facets
     *
     * @throws \Netgen\EzPlatformSiteApi\API\Exceptions\TranslationNotMatchedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotImplementedException
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
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Search\Facet[] $facets
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotImplementedException
     * @throws \Netgen\EzPlatformSiteApi\API\Exceptions\TranslationNotMatchedException
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
                        $itemFilterRequest,
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
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Search\Facet $facet
     * @param array $definition
     *
     * @return \Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterResponse\FacetItem[]
     */
    private function mapItems(ApiFacet $facet, array $definition): array
    {
        if ($facet instanceof CustomFieldFacet) {
            return $this->mapCustomFieldFacet($facet, $definition);
        }

        if ($facet instanceof RawFacet) {
            return $this->mapRawFacetItems($facet, $definition);
        }

        $facetClass = \get_class($facet);

        throw new RuntimeException("Facet of type '{$facetClass}' is not handled");
    }

    private function mapRawFacetItems(RawFacet $facet, array $definition): array
    {
        $items = [];

        foreach ($facet->data->buckets as $bucket) {
            switch ($definition['type']) {
                case FacetItem::TYPE_CONTENT:
                    $content = $this->loadService->loadContent($bucket->val);
                    $label = '';
                    if ($content->hasField('title')) {
                        $label = $content->getFieldValue('title')->text;
                    } elseif ($content->hasField('name')) {
                        $label = $content->getFieldValue('name')->text;
                    }
                    if ($definition['showCount']) {
                        $label = $label . ' (' . $bucket->count . ')';
                    }
                    $items[] = new FacetItem([
                        'id' => $bucket->val,
                        'label' => $label,
                        'count' => $count,
                    ]);

                    break;
            }
        }

        return $items;
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
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Search\Facet[] $facets
     * @param string $name
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Search\Facet
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
