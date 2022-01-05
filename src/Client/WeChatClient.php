<?php

namespace SocialData\Connector\WeChat\Client;

use Garbetjie\WeChatClient\Authentication;
use Garbetjie\WeChatClient\Client;
use Garbetjie\WeChatClient\Media;
use SocialData\Connector\WeChat\Model\EngineConfiguration;
use SocialData\Connector\WeChat\Storage\TokenStorage;
use SocialDataBundle\Service\ConnectorServiceInterface;

class WeChatClient
{
    protected ConnectorServiceInterface $connectorService;

    public function __construct(ConnectorServiceInterface $connectorService)
    {
        $this->connectorService = $connectorService;
    }

    /**
     * @throws Authentication\Exception
     */
    public function getAuthenticatedClient(EngineConfiguration $configuration): Client
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
     * @throws Authentication\Exception
     */
    public function getMediaServiceClient(EngineConfiguration $configuration): Media\Service
    {
        $client = $this->getAuthenticatedClient($configuration);

        return new Media\Service($client);
    }
}
