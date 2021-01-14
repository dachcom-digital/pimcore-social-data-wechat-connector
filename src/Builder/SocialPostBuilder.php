<?php

namespace SocialData\Connector\WeChat\Builder;

use Carbon\Carbon;
use Garbetjie\WeChatClient\Media;
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
    /**
     * @var int
     */
    public const DEFAULT_COUNT = 20;

    /**
     * @var WeChatClient
     */
    protected $weChatClient;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param WeChatClient    $weChatClient
     * @param LoggerInterface $logger
     */
    public function __construct(
        WeChatClient $weChatClient,
        LoggerInterface $logger
    ) {
        $this->weChatClient = $weChatClient;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function configureFetch(BuildConfig $buildConfig, OptionsResolver $resolver): void
    {
        // nothing to configure so far.
    }

    /**
     * {@inheritdoc}
     */
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
            $mediaService = $this->weChatClient->getMediaServiceClient($engineConfiguration);
        } catch (\Throwable $e) {
            throw new BuildException(sprintf('media service client error: %s', $e->getMessage()));
        }

        $countPagination = (int) ceil($count / self::DEFAULT_COUNT);

        for ($i = 0; $i < $countPagination; $i++) {
            try {
                $newsMaterial = $mediaService->paginateNews(self::DEFAULT_COUNT * $i, self::DEFAULT_COUNT);
            } catch (\Throwable $e) {
                throw new BuildException(sprintf('error while fetching paginated news: %s', $e->getMessage()));
            }

            foreach ($newsMaterial->getItems() as $newsItem) {
                $itemCount = 1;

                /** @var Media\Paginated\NewsItem $item */
                foreach ($newsItem->getItems() as $key => $item) {
                    if ($withSubPosts === false && $itemCount > 1) {
                        break;
                    }

                    $displayUrl = $item->getDisplayUrl();

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
                        'date' => $newsItem->getUpdatedDate()
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

    /**
     * {@inheritdoc}
     */
    public function configureFilter(BuildConfig $buildConfig, OptionsResolver $resolver): void
    {
        // nothing to configure so far.
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    public function configureTransform(BuildConfig $buildConfig, OptionsResolver $resolver): void
    {
        // nothing to configure so far.
    }

    /**
     * {@inheritdoc}
     */
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

        if ($element['date'] instanceof \DateTimeImmutable) {
            try {
                $creationTime = new Carbon($element['date']);
            } catch (\Exception $e) {
                $creationTime = Carbon::now();
            }
        } else {
            $creationTime = Carbon::now();
        }

        $newsItem = $element['item'];
        if (!$newsItem instanceof Media\Downloaded\NewsItem) {
            return;
        }

        $socialPost->setSocialCreationDate($creationTime);
        $socialPost->setTitle($newsItem->getTitle());
        $socialPost->setContent($newsItem->getSummary());
        $socialPost->setUrl($newsItem->getDisplayURL());
        $socialPost->setPosterUrl($newsItem->getThumbnailURL());

        $data->setTransformedElement($socialPost);
    }
}
