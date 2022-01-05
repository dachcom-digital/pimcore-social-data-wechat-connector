<?php

namespace SocialData\Connector\WeChat\Model;

use Carbon\Carbon;
use SocialData\Connector\WeChat\Form\Admin\Type\WeChatEngineType;
use SocialDataBundle\Connector\ConnectorEngineConfigurationInterface;

class EngineConfiguration implements ConnectorEngineConfigurationInterface
{
    protected ?string $appId = null;
    protected ?string $appSecret = null;
    protected ?string $accessToken = null;
    protected ?Carbon $expiresAt = null;
    protected ?string $hash = null;

    public function setAppId(string $appId): void
    {
        $this->appId = $appId;
    }

    public function getAppId(): ?string
    {
        return $this->appId;
    }

    public function setAppSecret(string $appSecret): void
    {
        $this->appSecret = $appSecret;
    }

    public function getAppSecret(): ?string
    {
        return $this->appSecret;
    }

    public function setAccessToken(?string $accessToken, $forceUpdate = false): void
    {
        // symfony: if there are any fields on the form that are not included in the submitted data,
        // those fields will be explicitly set to null.
        if ($accessToken === null && $forceUpdate === false) {
            return;
        }

        $this->accessToken = $accessToken;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function setAccessTokenExpiresAt(?Carbon $expiresAt, bool $forceUpdate = false): void
    {
        // symfony: if there are any fields on the form that are not included in the submitted data,
        // those fields will be explicitly set to null.
        if ($expiresAt === null && $forceUpdate === false) {
            return;
        }

        $this->expiresAt = $expiresAt;
    }

    public function getAccessTokenExpiresAt(): ?Carbon
    {
        return $this->expiresAt;
    }

    public function setHash(?string $hash, bool $forceUpdate = false): void
    {
        // symfony: if there are any fields on the form that are not included in the submitted data,
        // those fields will be explicitly set to null.
        if ($hash === null && $forceUpdate === false) {
            return;
        }

        $this->hash = $hash;
    }

    public function getHash(): ?string
    {
        return $this->hash;
    }

    public static function getFormClass(): string
    {
        return WeChatEngineType::class;
    }
}
