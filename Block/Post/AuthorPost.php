<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Blog
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Blog\Block\Post;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Mageplaza\Blog\Helper\Data;
use Mageplaza\Blog\Model\Post;

/**
 * Class AuthorPost
 * @package Mageplaza\Blog\Block\Post
 */
class AuthorPost extends \Mageplaza\Blog\Block\Listpost
{
    /**
     * @return \Mageplaza\Blog\Block\Frontend
     */
    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    /**
     * @return \Mageplaza\Blog\Model\ResourceModel\Post\Collection
     * @throws LocalizedException
     */
    public function getPostCollection()
    {
        $collection = $this->getCollection();

        $userId = $this->getAuthor()->getId();

        $collection->addFieldToFilter('author_id', $userId);

        if ($collection && $collection->getSize()) {
            $pager = $this->getLayout()->createBlock(\Magento\Theme\Block\Html\Pager::class, 'mpblog.post.pager');

            $perPageValues = (string) $this->helperData->getConfigGeneral('pagination');
            $perPageValues = explode(',', $perPageValues);
            $perPageValues = array_combine($perPageValues, $perPageValues);

            $pager->setAvailableLimit($perPageValues)
                ->setCollection($collection);

            $this->setChild('pager', $pager);
        }

        return $collection;
    }

    /**
     * @return mixed
     */
    public function getVersion()
    {
        $array = explode('/', $this->helperData->getConfigValue('cms/wysiwyg/editor'));
        if ($array[count($array) - 1] === 'tinymce4Adapter') {
            return 4;
        }

        return 3;
    }

    /**
     * @return int
     */
    public function getMagentoVersion()
    {
        return (int) $this->helperData->versionCompare('2.3.0') ? 3 : 2;
    }

    /**
     * @param $postCollection
     *
     * @return string
     * @throws LocalizedException
     */
    public function getPostDatas($postCollection)
    {
        $result = [];

        /** @var Post $post */
        foreach ($postCollection->getItems() as $post) {
            $post->getCategoryIds();
            $post->getTopicIds();
            $post->getTagIds();
            $result[$post->getId()] = $post->getData();
        }

        return Data::jsonEncode($result);
    }

    /**
     * @return mixed
     */
    public function getAuthorName()
    {
        return $this->getAuthor()->getName();
    }

    /**
     * @return bool
     */
    public function getAuthorStatus()
    {
        $author = $this->getAuthor();

        return $author->getStatus() === '1' ? true : false;
    }

    /**
     * @return mixed
     */
    public function getAuthor()
    {
        return $this->coreRegistry->registry('mp_author');
    }

    /**
     * @param bool $meta
     *
     * @return array
     */
    public function getBlogTitle($meta = false)
    {
        return $meta ? [$this->getAuthor()->getName()] : $this->getAuthor()->getName();
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getBaseMediaUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
    }
}
