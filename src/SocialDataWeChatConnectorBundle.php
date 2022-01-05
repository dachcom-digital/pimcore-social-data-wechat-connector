<?php

namespace SocialData\Connector\WeChat;

use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use Pimcore\Extension\Bundle\Traits\PackageVersionTrait;

class SocialDataWeChatConnectorBundle extends AbstractPimcoreBundle
{
    use PackageVersionTrait;

    public const PACKAGE_NAME = 'dachcom-digital/social-data-wechat-connector';

    protected function getComposerPackageName(): string
    {
        return self::PACKAGE_NAME;
    }

    public function getCssPaths(): array
    {
        return [
            '/bundles/socialdatawechatconnector/css/admin.css'
        ];
    }

    public function getJsPaths(): array
    {
        return [
            '/bundles/socialdatawechatconnector/js/connector/wechat-connector.js',
            '/bundles/socialdatawechatconnector/js/feed/wechat-feed.js',
        ];
    }
}
