<?php

declare(strict_types=1);

namespace Netgen\Bundle\eZPlatformAdvancedSearchBundle\Core\ItemFilter\ValueMappers;

use Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterRequest;
use Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterResponse\SelectedFacet;

/**
 * SelectedFacetMapper maps selected facets.
 */
final class SelectedFacetMapper
{
    /**
     * @var \Netgen\Bundle\eZPlatformAdvancedSearchBundle\Core\ItemFilter\ValueMappers\FacetTitleMapper
     */
    private $facetTitleMapper;

    /**
     * @param \Netgen\Bundle\eZPlatformAdvancedSearchBundle\Core\ItemFilter\ValueMappers\FacetTitleMapper $facetTitleMapper
     */
    public function __construct(FacetTitleMapper $facetTitleMapper)
    {
        $this->facetTitleMapper = $facetTitleMapper;
    }

    /**
     * @param \Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterRequest $filterRequest
     * @param string[] $selectedFacetsSet
     * @param array $facetDefinitions
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     *
     * @return \Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterResponse\SelectedFacet[]
     */
    public function mapSelectedFacets(
        ItemFilterRequest $filterRequest,
        array $selectedFacetsSet,
        array $facetDefinitions
    ): array {
        $selectedFacets = [];

        foreach (array_keys($selectedFacetsSet) as $identifier) {
            if (!array_key_exists($identifier, $facetDefinitions)) {
                continue;
            }

            $definition = $facetDefinitions[$identifier];
            $parameterName = $definition['parameterName'];
            $type = $definition['type'];
            $facetId = $filterRequest->request->query->get($parameterName);

            $selectedFacets[] = new SelectedFacet([
                'id' => $facetId,
                'label' => $this->facetTitleMapper->mapSelectedTitle($identifier, $type, $facetId),
                'parameterName' => $parameterName,
            ]);
        }

        return $selectedFacets;
    }
}
