<?php

namespace SocialData\Connector\WeChat\Builder;

use Carbon\Carbon;
use SocialData\Connector\WeChat\Client\WeChatClient;
use SocialData\Connector\WeChat\Model\EngineConfiguration;
use SocialData\Connector\WeChat\Model\FeedConfiguration;
use SocialDataBundle\Connector\SocialPostBuilderInterface;
use SocialDataBundle\Dto\BuildConfig;
use SocialDataBundle\Dto\FetchData;
use SocialDataBundle\Dto\FilterData;
use SocialDataBundle\Dto\TransformData;
use SocialDataBundle\Exception\BuildException;
use SocialDataBundle\Logger\LoggerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SocialPostBuilder implements SocialPostBuilderInterface
{
    protected const DEFAULT_COUNT = 20;
    protected const MEDIA_TYPE = 'news';

    protected WeChatClient $weChatClient;
    protected LoggerInterface $logger;

    public function __construct(WeChatClient $weChatClient, LoggerInterface $logger)
    {
        $this->weChatClient = $weChatClient;
        $this->logger = $logger;
    }

    public function configureFetch(BuildConfig $buildConfig, OptionsResolver $resolver): void
    {
        // nothing to configure so far.
    }

    public function fetch(FetchData $data): void
    {
        $buildConfig = $data->getBuildConfig();

        $engineConfiguration = $buildConfig->getEngineConfiguration();
        $feedConfiguration = $buildConfig->getFeedConfiguration();

        if (!$engineConfiguration instanceof EngineConfiguration) {
            return;
        }

        if (!$feedConfiguration instanceof FeedConfiguration) {
            return;
        }

        $paginationList = [];
        $count = $feedConfiguration->getCount();
        $withSubPosts = $feedConfiguration->getSubPosts();

        try {
            $mediaService = $this->weChatClient->buildWeChatMaterialClient($engineConfiguration);
        } catch (\Throwable $e) {
            throw new BuildException(sprintf('media service client error: %s', $e->getMessage()));
        }

        $countPagination = (int) ceil($count / self::DEFAULT_COUNT);

        for ($i = 0; $i < $countPagination; $i++) {

            try {
                $newsMaterial = $mediaService->list(self::MEDIA_TYPE, self::DEFAULT_COUNT * $i, self::DEFAULT_COUNT);
            } catch (\Throwable $e) {
                throw new BuildException(sprintf('error while fetching paginated %s: %s', $e->getMessage(), self::MEDIA_TYPE));
            }

            if (!isset($newsMaterial['item'])) {
                break;
            }

            $items = $newsMaterial['item'];

            if (!is_array($items) || count($items) === 0) {
                break;
            }

            foreach ($items as $newsItem) {

                $itemCount = 1;

                if (!isset($newsItem['content']['news_item'])) {
                    continue;
                }

                foreach ($newsItem['content']['news_item'] as $item) {

                    if ($withSubPosts === false && $itemCount > 1) {
                        break;
                    }

                    $displayUrl = $item['url'] ?? null;

                    if (empty($displayUrl)) {
                        continue;
                    }

                    parse_str(parse_url($displayUrl, PHP_URL_QUERY), $params);

                    if (!isset($params['sn'])) {
                        $this->logger->error('SN field for post not found. skipping...', [$buildConfig->getFeed()]);

                        continue;
                    }

                    $itemId = $params['sn'];

                    // already added
                    if (isset($paginationList[$itemId])) {
                        continue;
                    }

                    $paginationList[$itemId] = [
                        'item' => $item,
                        'id'   => $itemId,
                        'date' => $newsItem['content']['update_time'] ?? null
                    ];

                    if (count($paginationList) === $count) {
                        break 3;
                    }

                    $itemCount++;
                }
            }
        }

        if (count($paginationList) === 0) {
            return;
        }

        $data->setFetchedEntities($paginationList);
    }

    public function configureFilter(BuildConfig $buildConfig, OptionsResolver $resolver): void
    {
        // nothing to configure so far.
    }

    public function filter(FilterData $data): void
    {
        $buildConfig = $data->getBuildConfig();

        $element = $data->getTransferredData();

        $feedConfiguration = $buildConfig->getFeedConfiguration();
        if (!$feedConfiguration instanceof FeedConfiguration) {
            return;
        }

        if (!is_array($element)) {
            return;
        }

        $data->setFilteredElement($element);
        $data->setFilteredId($element['id']);
    }

    public function configureTransform(BuildConfig $buildConfig, OptionsResolver $resolver): void
    {
        // nothing to configure so far.
    }

    public function transform(TransformData $data): void
    {
        $buildConfig = $data->getBuildConfig();

        $element = $data->getTransferredData();
        $socialPost = $data->getSocialPostEntity();

        $engineConfiguration = $buildConfig->getEngineConfiguration();
        if (!$engineConfiguration instanceof EngineConfiguration) {
            return;
        }

        $feedConfiguration = $buildConfig->getFeedConfiguration();
        if (!$feedConfiguration instanceof FeedConfiguration) {
            return;
        }

        if (is_int($element['date'])) {
            try {
                $creationTime = Carbon::createFromTimestamp($element['date']);
            } catch (\Exception $e) {
                $creationTime = Carbon::now();
            }
        } else {
            $creationTime = Carbon::now();
        }

        $newsItem = $element['item'] ?? null;

        if (!is_array($newsItem)) {
            return;
        }

        $socialPost->setSocialCreationDate($creationTime);
        $socialPost->setTitle($newsItem['title'] ?? null);
        $socialPost->setContent($newsItem['digest'] ?? null);
        $socialPost->setUrl($newsItem['url'] ?? null);
        $socialPost->setPosterUrl($newsItem['thumb_url'] ?? null);

        $data->setTransformedElement($socialPost);
    }
}
