<?php

namespace SocialData\Connector\WeChat\Client;

use Garbetjie\WeChatClient\Client;
use Garbetjie\WeChatClient\Media;
use Garbetjie\WeChatClient\Authentication;
use SocialData\Connector\WeChat\Model\EngineConfiguration;
use SocialData\Connector\WeChat\Storrage\TokenStorage;
use SocialDataBundle\Service\ConnectorServiceInterface;

class WeChatClient
{
    /**
     * @var ConnectorServiceInterface
     */
    protected $connectorService;

    /**
     * @param ConnectorServiceInterface $connectorService
     */
    public function __construct(
        ConnectorServiceInterface $connectorService)
    {
        $this->connectorService = $connectorService;
    }

    /**
     * @param EngineConfiguration $configuration
     *
     * @return Client
     * @throws Authentication\Exception
     */
    public function getAuthenticatedClient(EngineConfiguration $configuration)
    {
        $client = new Client();
        $storage = new TokenStorage($this->connectorService, $configuration);
        $authService = new Authentication\Service($client);

        $appID = $configuration->getAppId();
        $secret = $configuration->getAppSecret();

        /*
         * @see https://github.com/garbetjie/wechat-php/issues/12
         */
        $authenticatedClientOrToken = $authService->authenticate($appID, $secret, $storage);

        if ($authenticatedClientOrToken instanceof Authentication\AccessToken) {
            return $client->withAccessToken($authenticatedClientOrToken);
        }

        return $authenticatedClientOrToken;
    }

    /**
     * @param EngineConfiguration $configuration
     *
     * @return Media\Service
     * @throws Authentication\Exception
     */
    public function getMediaServiceClient(EngineConfiguration $configuration)
    {
        $client = $this->getAuthenticatedClient($configuration);

        return new Media\Service($client);
    }
}
