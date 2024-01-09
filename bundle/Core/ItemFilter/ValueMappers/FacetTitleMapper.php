<?php

declare(strict_types=1);

namespace Netgen\Bundle\eZPlatformAdvancedSearchBundle\Core\ItemFilter\ValueMappers;

use Ibexa\Core\Helper\TranslationHelper;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * FacetTitleMapper provides mapping of facet identifier to facet title.
 */
final class FacetTitleMapper
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    private $tagService;

    /**
     * @var \Ibexa\Core\Helper\TranslationHelper
     */
    private $translationHelper;

    /**
     * @var \Symfony\Component\Translation\TranslatorInterface
     */
    private $translator;

    /**
     * @var string
     */
    private $translationDomain;

    /**
     * @param \Netgen\TagsBundle\API\Repository\TagsService $tagService
     * @param \Ibexa\Core\Helper\TranslationHelper $translationHelper
     * @param \Symfony\Component\Translation\TranslatorInterface $translator
     * @param string $translationDomain
     */
    public function __construct(
        TagsService $tagService,
        TranslationHelper $translationHelper,
        TranslatorInterface $translator,
        string $translationDomain
    ) {
        $this->tagService = $tagService;
        $this->translationHelper = $translationHelper;
        $this->translator = $translator;
        $this->translationDomain = $translationDomain;
    }

    public function mapTitle(string $identifier): string
    {
        return $this->translator->trans("advanced_search.facet.name.{$identifier}", [], $this->translationDomain);
    }

    /**
     * @param string $identifier
     * @param string $type
     * @param string|int $id
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Core\Base\Exceptions\InvalidArgumentException
     *
     * @return string
     */
    public function mapSelectedTitle(string $identifier, string $type, $id): string
    {
        return $this->getLabel($type, $id);
    }

    /**
     * @param string $type
     * @param string|int $id
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Core\Base\Exceptions\InvalidArgumentException
     *
     * @return string
     */
    private function getLabel(string $type, $id): string
    {
        switch ($type) {
            case 'tag_id':
                return $this->getLabelByTagId($id);
            case 'tag_label':
                return $id;
        }

        return '';
    }

    /**
     * @param string|int $id
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Core\Base\Exceptions\InvalidArgumentException
     *
     * @return string|null
     */
    private function getLabelByTagId($id): ?string
    {
        $tag = $this->tagService->loadTag($id, $this->translationHelper->getAvailableLanguages());

        return $this->getKeywordTranslation($tag);
    }

    /**
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     *
     * @throws \Ibexa\Core\Base\Exceptions\InvalidArgumentException
     *
     * @return string|null
     */
    private function getKeywordTranslation(Tag $tag): ?string
    {
        return $this->translationHelper->getTranslatedByMethod($tag, 'getKeyword');
    }
}
