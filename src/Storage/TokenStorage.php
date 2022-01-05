<?php

namespace SocialData\Connector\WeChat\Storage;

use Carbon\Carbon;
use Garbetjie\WeChatClient\Authentication\AccessToken;
use Garbetjie\WeChatClient\Authentication\Storage\StorageInterface;
use SocialData\Connector\WeChat\Model\EngineConfiguration;
use SocialDataBundle\Service\ConnectorServiceInterface;

class TokenStorage implements StorageInterface
{
    protected ConnectorServiceInterface $connectorService;
    protected EngineConfiguration $engineConfiguration;

    public function __construct(ConnectorServiceInterface $connectorService, EngineConfiguration $configuration)
    {
        $this->connectorService = $connectorService;
        $this->engineConfiguration = $configuration;
    }

    public function hash(string $appId, string $secretKey): string
    {
        return hash('sha256', $appId . $secretKey);
    }

    public function retrieve($hash): ?AccessToken
    {
        if ($this->engineConfiguration->getHash() !== $hash) {
            return null;
        }

        return new AccessToken($this->engineConfiguration->getAccessToken(), $this->engineConfiguration->getAccessTokenExpiresAt());
    }

    public function store(string $hash, AccessToken $accessToken): void
    {
        $this->engineConfiguration->setAccessToken((string) $accessToken, true);
        $this->engineConfiguration->setAccessTokenExpiresAt(Carbon::instance($accessToken->expires()), true);
        $this->engineConfiguration->setHash($hash, true);

        $this->connectorService->updateConnectorEngineConfiguration('wechat', $this->engineConfiguration);
    }
}
