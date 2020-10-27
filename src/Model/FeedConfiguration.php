<?php

namespace SocialData\Connector\WeChat\Model;

use SocialDataBundle\Connector\ConnectorFeedConfigurationInterface;
use SocialData\Connector\WeChat\Form\Admin\Type\WeChatFeedType;

class FeedConfiguration implements ConnectorFeedConfigurationInterface
{
    /**
     * @var int|null
     */
    protected $count;

    /**
     * @var bool|null
     */
    protected $subPosts;

    /**
     * {@inheritdoc}
     */
    public static function getFormClass()
    {
        return WeChatFeedType::class;
    }

    /**
     * @param int|null $count
     */
    public function setCount(?int $count)
    {
        $this->count = $count;
    }

    /**
     * @return int|null
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @param bool|null $subPosts
     */
    public function setSubPosts(?bool $subPosts)
    {
        $this->subPosts = $subPosts;
    }

    /**
     * @return bool|null
     */
    public function getSubPosts()
    {
        return $this->subPosts;
    }
}
