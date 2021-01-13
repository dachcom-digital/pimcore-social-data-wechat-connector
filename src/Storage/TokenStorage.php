<?php

namespace SocialData\Connector\WeChat\Storage;

use Carbon\Carbon;
use Garbetjie\WeChatClient\Authentication\AccessToken;
use Garbetjie\WeChatClient\Authentication\Storage\StorageInterface;
use SocialData\Connector\WeChat\Model\EngineConfiguration;
use SocialDataBundle\Service\ConnectorServiceInterface;

class TokenStorage implements StorageInterface
{
    protected $connectorService;

    protected $engineConfiguration;

    /**
     * @param ConnectorServiceInterface $connectorService
     * @param EngineConfiguration       $configuration
     */
    public function __construct(ConnectorServiceInterface $connectorService, EngineConfiguration $configuration)
    {
        $this->connectorService = $connectorService;
        $this->engineConfiguration = $configuration;
    }

    /**
     * @param string $appId
     * @param string $secretKey
     *
     * @return string
     */
    public function hash($appId, $secretKey)
    {
        return hash('sha256', $appId . $secretKey);
    }

    /**
     * @return AccessToken|null
     */
    public function retrieve($hash)
    {
        if ($this->engineConfiguration->getHash() !== $hash) {
            return null;
        }

        return new AccessToken($this->engineConfiguration->getAccessToken(), $this->engineConfiguration->getAccessTokenExpiresAt());
    }

    /**
     * @param string      $hash
     * @param AccessToken $accessToken
     */
    public function store($hash, AccessToken $accessToken)
    {
        $this->engineConfiguration->setAccessToken((string) $accessToken, true);
        $this->engineConfiguration->setAccessTokenExpiresAt(Carbon::instance($accessToken->expires()), true);
        $this->engineConfiguration->setHash($hash, true);

        $this->connectorService->updateConnectorEngineConfiguration('wechat', $this->engineConfiguration);
    }
}
