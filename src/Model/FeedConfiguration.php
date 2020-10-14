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
}
