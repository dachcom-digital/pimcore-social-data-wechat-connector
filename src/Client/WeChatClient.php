<?php

namespace SocialData\Connector\WeChat\Client;

use Carbon\Carbon;
use EasyWeChat\Factory;
use EasyWeChat\OfficialAccount\Application;
use EasyWeChat\OfficialAccount\Material\Client;
use SocialData\Connector\WeChat\Model\EngineConfiguration;
use SocialDataBundle\Service\ConnectorServiceInterface;

class WeChatClient
{
    protected ConnectorServiceInterface $connectorService;

    public function __construct(ConnectorServiceInterface $connectorService)
    {
        $this->connectorService = $connectorService;
    }

    /**
     * @throws \Exception
     */
    public function buildWeChatApplication(EngineConfiguration $configuration, bool $forceTokenGeneration = false): Application
    {
        $options = [
            'app_id' => $configuration->getAppId(),
            'secret' => $configuration->getAppSecret(),
            'token'  => 'pimcore-social-data-wechat',
            'log' => [
                'level' => 'emergency',
                'file'  => PIMCORE_PRIVATE_VAR . '/log/social-data-wechat.log',
            ]
        ];

        $app = Factory::officialAccount($options);

        $this->validateToken($configuration, $app, $forceTokenGeneration);

        return $app;
    }

    /**
     * @throws \Exception
     */
    public function buildWeChatMaterialClient(EngineConfiguration $configuration): Client
    {
        return $this->buildWeChatApplication($configuration)->material;
    }

    /**
     * @throws \Exception
     */
    protected function validateToken(EngineConfiguration $configuration, Application $app, bool $forceTokenGeneration = false): void
    {
        $refresh = empty($configuration->getAccessToken());

        if ($configuration->getAccessTokenExpiresAt() instanceof Carbon && $configuration->getAccessTokenExpiresAt()->isPast()) {
            $refresh = true;
        }

        if ($forceTokenGeneration === false && $refresh === false) {
            return;
        }

        $token = $app->access_token->getToken(!empty($configuration->getAccessToken()));

        if (!is_array($token)) {
            return;
        }

        $configuration->setAccessToken($token['access_token'], true);
        $configuration->setAccessTokenExpiresAt(Carbon::createFromTimestamp(time() + $token['expires_in']), true);

        $this->connectorService->updateConnectorEngineConfiguration('wechat', $configuration);
    }

}
