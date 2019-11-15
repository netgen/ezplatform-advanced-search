<?php

declare(strict_types=1);

namespace Netgen\Bundle\eZPlatformAdvancedSearchBundle\Core\ItemFilter\ValueMappers;

use eZ\Publish\API\Repository\Values\Content\Search\Facet as ApiFacet;
use Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterRequest;
use Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterResponse\Facet;
use Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterResponse\FacetItem;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Search\Facet\CustomFieldFacet;
use Netgen\TagsBundle\API\Repository\TagsService;
use RuntimeException;

/**
 * FacetMapper provides mapping of eZ Platform search API facet to item filter response facets.
 */
final class FacetMapper
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
     * @param \Netgen\Bundle\eZPlatformAdvancedSearchBundle\Core\ItemFilter\ValueMappers\FacetTitleMapper $facetTitleMapper
     */
    public function __construct(
        FacetTitleMapper $facetTitleMapper,
        TagsService $tagsService
    ) {
        $this->facetTitleMapper = $facetTitleMapper;
        $this->tagsService = $tagsService;
    }

    /**
     * @param \Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterRequest $itemFilterRequest
     * @param string[] $facetIdentifierMap
     * @param array $facetDefinitions
     * @param \eZ\Publish\API\Repository\Values\Content\Search\Facet[] $facets
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotImplementedException
     * @throws \Netgen\EzPlatformSiteApi\API\Exceptions\TranslationNotMatchedException
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
     * @throws \eZ\Publish\API\Repository\Exceptions\NotImplementedException
     * @throws \Netgen\EzPlatformSiteApi\API\Exceptions\TranslationNotMatchedException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
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

            // We only try to map sub-facets if their parent facet is not mapped
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

            $mappedFacets[] = new Facet([
                'title' => $this->facetTitleMapper->mapTitle($identifier),
                'type' => $this->resolveType($identifier),
                'initialStateClosed' => $this->getInitialStateClosedByFacetDefinition($definition),
                'parameterName' => $identifier,
                'items' => $items,
            ]);
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
                    $items[] = new FacetItem([
                        'id' => $entry,
                        'label' => $label,
                        'count' => $count,
                    ]);
                }

                break;
            case FacetItem::TYPE_NUMBER:
                foreach ($facet->entries as $entry => $count) {
                    $items[] = new FacetItem([
                        'id' => $entry,
                        'label' => $entry,
                        'count' => $count,
                    ]);
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
