<?php

namespace SocialData\Connector\WeChat\Model;

use SocialData\Connector\WeChat\Form\Admin\Type\WeChatFeedType;
use SocialDataBundle\Connector\ConnectorFeedConfigurationInterface;

class FeedConfiguration implements ConnectorFeedConfigurationInterface
{
    protected ?int $count = null;
    protected ?bool $subPosts = null;

    public static function getFormClass(): string
    {
        return WeChatFeedType::class;
    }

    public function setCount(?int $count): void
    {
        $this->count = $count;
    }

    public function getCount(): ?int
    {
        return $this->count;
    }

    public function setSubPosts(?bool $subPosts): void
    {
        $this->subPosts = $subPosts;
    }

    public function getSubPosts(): ?bool
    {
        return $this->subPosts;
    }
}
