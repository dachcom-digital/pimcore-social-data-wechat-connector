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
            $freePublishService = $this->weChatClient->getFreepublishServiceClient($engineConfiguration);

        } catch (\Throwable $e) {
            throw new BuildException(sprintf('media service client error: %s', $e->getMessage()));
        }

        $countPagination = (int)ceil($count / self::DEFAULT_COUNT);

        for ($i = 0; $i < $countPagination; $i++) {
            try {
                $freePublished = $freePublishService->paginateFreePublish(self::DEFAULT_COUNT * $i, self::DEFAULT_COUNT);
            } catch (\Throwable $e) {
                throw new BuildException(sprintf('error while fetching paginated news: %s', $e->getMessage()));
            }

            foreach ($freePublished->item as $newsItem) {
                $itemId = $newsItem->article_id;
                $content = $newsItem->content;

                if (!$content->news_item) {
                    continue;
                }

                $newsContents = $content->news_item;
                $createTime = $content->create_time;
                $itemCount = 1;

                foreach ($newsContents as $newsContent) {
                    if ($withSubPosts === false && $itemCount > 1) {
                        break;
                    }

                    $paginationList[$itemId] = [
                        'item' => $newsContent,
                        'id'   => $itemId,
                        'date' => $createTime
                    ];

                    $itemCount++;

                    if (count($paginationList) === $count) {
                        break 3;
                    }

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

        try {
            $creationTime = new Carbon($element['date']);
        } catch (\Exception $e) {
            $creationTime = Carbon::now();
        }

        $newsItem = $element['item'];

        $socialPost->setSocialCreationDate($creationTime);
        $socialPost->setTitle($newsItem->title);
        $socialPost->setContent($newsItem->digest);
        $socialPost->setUrl($newsItem->url);
        $socialPost->setPosterUrl($newsItem->thumb_url);

        $data->setTransformedElement($socialPost);
    }
}
