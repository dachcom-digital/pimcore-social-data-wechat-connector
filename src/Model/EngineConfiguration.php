<?php

namespace SocialData\Connector\WeChat\Model;

use Carbon\Carbon;
use SocialData\Connector\WeChat\Form\Admin\Type\WeChatEngineType;
use SocialDataBundle\Connector\ConnectorEngineConfigurationInterface;

class EngineConfiguration implements ConnectorEngineConfigurationInterface
{
    /**
     * @var string
     */
    protected $appId;

    /**
     * @var string
     */
    protected $appSecret;

    /**
     * @var string
     */
    protected $accessToken;

    /**
     * @var Carbon
     */
    protected $expiresAt;

    /**
     * @var string
     */
    protected $hash;

    /**
     * @param string $appId
     */
    public function setAppId($appId)
    {
        $this->appId = $appId;
    }

    /**
     * @return string
     */
    public function getAppId()
    {
        return $this->appId;
    }

    /**
     * @param string $appSecret
     */
    public function setAppSecret($appSecret)
    {
        $this->appSecret = $appSecret;
    }

    /**
     * @return string
     */
    public function getAppSecret()
    {
        return $this->appSecret;
    }

    /**
     * @param string $accessToken
     * @param bool   $forceUpdate
     */
    public function setAccessToken($accessToken, $forceUpdate = false)
    {
        // symfony: if there are any fields on the form that aren’t included in the submitted data,
        // those fields will be explicitly set to null.
        if ($accessToken === null && $forceUpdate === false) {
            return;
        }

        $this->accessToken = $accessToken;
    }

    /**
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @param Carbon $expiresAt
     * @param bool   $forceUpdate
     */
    public function setAccessTokenExpiresAt($expiresAt, $forceUpdate = false)
    {
        // symfony: if there are any fields on the form that aren’t included in the submitted data,
        // those fields will be explicitly set to null.
        if ($expiresAt === null && $forceUpdate === false) {
            return;
        }

        $this->expiresAt = $expiresAt;
    }

    /**
     * @return Carbon
     */
    public function getAccessTokenExpiresAt()
    {
        return $this->expiresAt;
    }

    /**
     * @param string $hash
     * @param bool   $forceUpdate
     */
    public function setHash($hash, $forceUpdate = false)
    {
        // symfony: if there are any fields on the form that aren’t included in the submitted data,
        // those fields will be explicitly set to null.
        if ($hash === null && $forceUpdate === false) {
            return;
        }

        $this->hash = $hash;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * {@inheritdoc}
     */
    public static function getFormClass()
    {
        return WeChatEngineType::class;
    }
}
