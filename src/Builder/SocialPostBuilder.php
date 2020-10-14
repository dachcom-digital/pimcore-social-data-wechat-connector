<?php

namespace SocialData\Connector\WeChat\Builder;

use Carbon\Carbon;
use SocialData\Connector\WeChat\Model\FeedConfiguration;
use SocialDataBundle\Dto\BuildConfig;
use SocialData\Connector\WeChat\Model\EngineConfiguration;
use SocialData\Connector\WeChat\Client\WeChatClient;
use SocialDataBundle\Connector\SocialPostBuilderInterface;
use SocialDataBundle\Dto\FetchData;
use SocialDataBundle\Dto\FilterData;
use SocialDataBundle\Dto\TransformData;
use SocialDataBundle\Exception\BuildException;
use SocialDataBundle\Logger\LoggerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Garbetjie\WeChatClient\Media;

class SocialPostBuilder implements SocialPostBuilderInterface
{
    /**
     * @var int
     */
    const DEFAULT_COUNT = 20;

    /**
     * @var WeChatClient
     */
    protected $weChatClient;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param WeChatClient $weChatClient
     */
    public function __construct(
        WeChatClient $weChatClient,
        LoggerInterface $logger
    )
    {
        $this->weChatClient = $weChatClient;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function configureFetch(BuildConfig $buildConfig, OptionsResolver $resolver): void
    {
        // nothing to configure so far.
    }

    /**
     * {@inheritDoc}
     */
    public function fetch(FetchData $data): void
    {
        $options = $data->getOptions();
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

        $mediaService = $this->weChatClient->getMediaServiceClient($engineConfiguration);

        $countPagination = (int)ceil($count / self::DEFAULT_COUNT);

        for ($i = 0; $i < $countPagination; $i++) {

            try {
                $newsMaterial = $mediaService->paginateNews(self::DEFAULT_COUNT * $i, self::DEFAULT_COUNT);
            } catch (\Throwable $e) {
                throw new BuildException(sprintf('wechat api error: %s', $e->getMessage()));
            }

            foreach ($newsMaterial->getItems() as $newsItem) {

                /** @var Media\Paginated\NewsItem $item */
                foreach ($newsItem->getItems() as $item) {

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
                        'id' => $itemId,
                        'date' => $newsItem->getUpdatedDate()
                    ];

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
     * {@inheritDoc}
     */
    public function configureFilter(BuildConfig $buildConfig, OptionsResolver $resolver): void
    {
        // nothing to configure so far.
    }

    /**
     * {@inheritDoc}
     */
    public function filter(FilterData $data): void
    {
        $options = $data->getOptions();
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
     * {@inheritDoc}
     */
    public function configureTransform(BuildConfig $buildConfig, OptionsResolver $resolver): void
    {
        // nothing to configure so far.
    }

    /**
     * {@inheritDoc}
     */
    public function transform(TransformData $data): void
    {
        $options = $data->getOptions();
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
            $creationTime = Carbon::createFromImmutable($element['date']);
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
