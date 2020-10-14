<?php

namespace SocialData\Connector\WeChat\Controller\Admin;

use Pimcore\Bundle\AdminBundle\Controller\AdminController;
use SocialData\Connector\WeChat\Client\WeChatClient;
use SocialData\Connector\WeChat\Model\EngineConfiguration;
use SocialDataBundle\Controller\Admin\Traits\ConnectResponseTrait;
use SocialDataBundle\Service\ConnectorServiceInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class WeChatController extends AdminController
{
    use ConnectResponseTrait;

    /**
     * @var WeChatClient
     */
    protected $weChatClient;

    /**
     * @var ConnectorServiceInterface
     */
    protected $connectorService;

    /**
     * @param WeChatClient $weChatClient
     * @param ConnectorServiceInterface $connectorService
     */
    public function __construct(
        WeChatClient $weChatClient,
        ConnectorServiceInterface $connectorService
    ){
        $this->weChatClient = $weChatClient;
        $this->connectorService = $connectorService;
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function connectAction(Request $request)
    {
        try {
            $connectorEngineConfig = $this->getConnectorEngineConfig();
        } catch (\Throwable $e) {
            return $this->buildConnectErrorResponse(500, 'general_error', 'connector engine configuration error', $e->getMessage());
        }

        try {
            $this->weChatClient->getAuthenticatedClient($connectorEngineConfig);
        } catch (\Throwable $e) {
            return $this->buildConnectErrorResponse(500, 'general_error', 'connector authenticate error', $e->getMessage());
        }

        return $this->buildConnectSuccessResponse();
    }

    /**
     * @return EngineConfiguration
     */
    protected function getConnectorEngineConfig()
    {
        $connectorDefinition = $this->connectorService->getConnectorDefinition('wechat', true);

        if (!$connectorDefinition->engineIsLoaded()) {
            throw new HttpException(400, 'Engine is not loaded.');
        }

        $connectorEngineConfig = $connectorDefinition->getEngineConfiguration();
        if (!$connectorEngineConfig instanceof EngineConfiguration) {
            throw new HttpException(400, 'Invalid wechat configuration. Please configure your connector "wechat" in backend first.');
        }

        return $connectorEngineConfig;
    }
}
