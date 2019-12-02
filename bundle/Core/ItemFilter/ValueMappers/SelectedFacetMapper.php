<?php

declare(strict_types=1);

namespace Netgen\Bundle\eZPlatformAdvancedSearchBundle\Core\ItemFilter\ValueMappers;

use Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterRequest;
use Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterResponse\FacetItem;
use Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterResponse\SelectedFacet;
use Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterResponse\SelectedFacetItem;
use Netgen\EzPlatformSiteApi\API\LoadService;
use Netgen\TagsBundle\API\Repository\TagsService;

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
     * @var TagsService
     */
    private $tagsService;

    /**
     * @var LoadService
     */
    private $loadService;

    /**
     * @param \Netgen\Bundle\eZPlatformAdvancedSearchBundle\Core\ItemFilter\ValueMappers\FacetTitleMapper $facetTitleMapper
     */
    public function __construct(FacetTitleMapper $facetTitleMapper, TagsService $tagsService, LoadService $loadService)
    {
        $this->facetTitleMapper = $facetTitleMapper;
        $this->tagsService = $tagsService;
        $this->loadService = $loadService;
    }

    /**
     * @param \Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterRequest $filterRequest
     * @param string[] $selectedFacetsSet
     * @param array $facetDefinitions
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *
     * @return \Netgen\Bundle\eZPlatformAdvancedSearchBundle\API\Values\Search\ItemFilterResponse\SelectedFacet[]
     */
    public function mapSelectedFacets(
        ItemFilterRequest $filterRequest,
        array $selectedFacetsSet,
        array $facetDefinitions
    ): array {
        $selectedFacets = [];
        foreach ($selectedFacetsSet as $identifier => $selectedIds) {
            if (!array_key_exists($identifier, $facetDefinitions)) {
                continue;
            }

            $definition = $facetDefinitions[$identifier];
            $parameterName = $definition['parameterName'];
            $type = $definition['type'];
            $facetId = $filterRequest->request->query->get($parameterName);

            $selectedFacets[] = new SelectedFacet([
                'title' => $this->facetTitleMapper->mapTitle($identifier),
                'items' => $this->mapItems($selectedIds, $facetDefinitions[$identifier]),
                'parameterName' => $parameterName,
            ]);
        }

        return $selectedFacets;
    }

    private function mapItems($ids, $definition)
    {
        $items = [];
        switch ($definition['type']) {
            case FacetItem::TYPE_TAG:
                $tags = $this->tagsService->loadTagList($ids);
                foreach ($ids as $id) {
                    $label = $tags[$id]->getKeyword();
                    $items[] = new SelectedFacetItem([
                        'id' => $id,
                        'label' => $label,
                    ]);
                }

                break;
            case FacetItem::TYPE_NUMBER:
                foreach ($ids as $id) {
                    $label = $id;
                    $items[] = new SelectedFacetItem([
                        'id' => $id,
                        'label' => $label,
                    ]);
                }

                break;
            case FacetItem::TYPE_CONTENT:
                foreach ($ids as $id) {
                    $content = $this->loadService->loadLocation($id)->content;
                    $label = $id;
                    if ($content->hasField('title')) {
                        $label = $content->getFieldValue('title')->text;
                    } elseif ($content->hasField('name')) {
                        $label = $content->getFieldValue('name')->text;
                    }
                    $items[] = new SelectedFacetItem([
                        'id' => $id,
                        'label' => $label,
                    ]);
                }

                break;
        }

        return $items;
    }
}
