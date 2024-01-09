<?php

declare(strict_types=1);

namespace Netgen\Bundle\eZPlatformAdvancedSearchBundle\Controller;

use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\ContentTypeIdentifier;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\LogicalAnd;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Subtree;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Visibility;
use Netgen\Bundle\EzPlatformSiteApiBundle\Controller\Controller;
use Netgen\EzPlatformSiteApi\Core\Traits\PagerfantaTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdvancedSearchPageController extends Controller
{
    use PagerfantaTrait;

    /**
     * Action for displaying the results of full text search.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @throws \Netgen\EzPlatformSiteApi\API\Exceptions\TranslationNotMatchedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function __invoke(Request $request): Response
    {
        $configResolver = $this->getConfigResolver();

        $searchText = trim($request->get('searchText', ''));
        $contentTypes = $configResolver->getParameter('search.content_types', 'ngsite');

        if (empty($searchText)) {
            return $this->render(
                $configResolver->getParameter('template.advanced_search', 'ngsite'),
                [
                    'search_text' => '',
                    'search_result_pager' => [],
                ]
            );
        }

        $criteria = [
            new Subtree($this->getRootLocation()->pathString),
            new Visibility(Visibility::VISIBLE),
        ];

        if (is_array($contentTypes) && !empty($contentTypes)) {
            $criteria[] = new ContentTypeIdentifier($contentTypes);
        }

        $query = new Query();
        $query->query = new Query\Criterion\FullText($searchText);
        $query->filter = new LogicalAnd($criteria);

        $currentPage = (int) $request->get('page', 1);
        $maxPerPage = (int) $configResolver->getParameter('search.default_limit', 'ngsite');
        $pager = $this->getFindPager($query, $currentPage, $maxPerPage);

        $response = $this->render(
            $configResolver->getParameter('template.advanced_search', 'ngsite'),
            [
                'search_text' => $searchText,
                'search_result_pager' => $pager,
            ]
        );

        $response->setPrivate();
        $response->setMaxAge(0);
        $response->setSharedMaxAge(0);
        $response->headers->addCacheControlDirective('must-revalidate');
        $response->headers->addCacheControlDirective('no-store');

        return $response;
    }
}
