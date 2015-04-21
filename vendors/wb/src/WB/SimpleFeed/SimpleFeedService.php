<?php

namespace WB\SimpleFeed;

use Psr\Log\LoggerInterface;

class SimpleFeedService
{
    /* @var \CacheStoreInterface $cacheStore */
    protected $cacheStore;

    /* @var int $cacheTtl */
    protected $cacheTtl = 1800;

    /* @var \DateFactory */
    protected $dateFactory;

    /* @var \FeedParser $feedParser */
    protected $feedParser;

    /* @var LoggerInterface $logger */
    protected $logger;

    /**
     * @param \FeedParser $FeedParser
     * @param \CacheStoreInterface $DistributedCacheStore
     * @param LoggerInterface $PsrLogger
     * @param \DateFactory $DateFactory
     * @param integer $cacheTtl
     */
    public function __construct(
        \FeedParser $FeedParser,
        \CacheStoreInterface $DistributedCacheStore,
        LoggerInterface $PsrLogger,
        \DateFactory $DateFactory,
        $cacheTtl = null
    ) {
        $this->feedParser = $FeedParser;
        $this->cacheStore = $DistributedCacheStore;
        $this->logger = $PsrLogger;
        $this->dateFactory = $DateFactory;
        if (!empty($cacheTtl)) {
            $this->cacheTtl = (int) $cacheTtl;
        }
    }

    /**
     * Returns a namepsaced cache key
     *
     * @param string $feedUrl
     * @return string
     */
    protected function cacheKey($feedUrl)
    {
        return 'wb:simplefeed:' . $feedUrl;
    }

    /**
     * Calls an rss feed and returns up to $count rows from that feed.
     * Feeds are stored in cache but passing a different count will not store
     * a new cache entry, it will simply return that number of items from the
     * cached feed.
     *
     * Count is not guaranteed to be returned if the feed doesn't supply that
     * many items.
     *
     * Returns a simple array of the feed and its items
     *
     * <code>
     * array(
     *      'status' => 200,
     *      'permalink' => '',
     *      'title' => '',
     *      'description' => '',
     *      'timestamp' => '',
     *      'item_count' => '',
     *      'items' => array(
     *          'permalink' => ''
     *          'title' => ''
     *          'description' => ''
     *          'image' => ''
     *      )
     * );
     * </code>
     *
     * @param string $feedURL   the full url to an rss feed.
     * @param int $count        number of items to return from the feed
     * @param bool $nativeOrder sets the order the feed items are returned in @see SimplePie
     * @param int $itemLimit    total number of items from the feed to store
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    public function getFeed($feedURL, $count = 25, $nativeOrder = false, $itemLimit = 100)
    {
        if (!\URLUtils::isUrl($feedURL)) {
            return array('status' => 404, 'item_count' => 0, 'items' => array());
        }

        if ($count > $itemLimit) {
            throw new \InvalidArgumentException('Count cannot be greater than the item limit.');
        }

        $cacheKey = $this->cacheKey($feedURL . ($nativeOrder ? ':nativeorder' : '') . ':' . $itemLimit);

        $this->logger->info("Lookup for key [$cacheKey]");
        $feed = $this->cacheStore->get($cacheKey);
        if (is_array($feed)) {
            $this->logger->info("Found feed in cache for key [$cacheKey]");
            $feed['items'] = array_slice($feed['items'], 0, $count);
            return $feed;
        }

        try {
            $this->logger->info("Calling feed [{$feedURL}]");

            /* @var \SimplePie $rawFeed */
            $rawFeed = $this->feedParser->parseFeed($feedURL, $nativeOrder);

            if ($publishDate = $rawFeed->get_channel_tags(SIMPLEPIE_NAMESPACE_RSS_20, 'pubDate')) {
                $publishDate = $this->dateFactory->newStorageDate($publishDate[0]['data'])->toUnix();
            } else {
                $publishDate = null;
            }

            $feed = array(
                'status' => 200,
                'permalink' => $rawFeed->get_permalink(),
                'title' => trim($rawFeed->get_title()),
                'description' => trim($rawFeed->get_description()),
                'timestamp' => $publishDate,
                'item_count' => $rawFeed->get_item_quantity(),
                'items' => array(),
            );

            $this->logger->debug($feed);

            $i = 0;
            /* @var \SimplePie_Item $item */
            foreach($rawFeed->get_items() as $item) {
                if ($i >= $itemLimit) {
                    break;
                }

                if (trim($item->get_title()) == '') {
                    continue;
                }

                $itemDate = null;
                if ($item->get_date() != '') {
                    $itemDate = $this->dateFactory->newStorageDate($item->get_date())->toUnix();
                }

                $img = null;
                // first try to get it from the enclosure node itself
                $tags = $item->get_item_tags('', 'enclosure');
                if (is_array($tags)) {
                    foreach ($tags as $tag) {
                        if (!isset($tag['attribs']['']['type'])) {
                            continue;
                        }

                        if (strpos($tag['attribs']['']['type'], 'image') !== false) {
                            $img = isset($tag['attribs']['']['url']) ? $tag['attribs']['']['url'] : null;
                            break;
                        }
                    }
                }

                if (empty($img)) {
                    /* @var \SimplePie_Enclosure $enclosure */
                    $enclosure = $item->get_enclosure();
                    if ($enclosure) {
                        $img = $enclosure->get_thumbnail();
                        if (empty($img)) {
                            $img = $enclosure->get_link();
                        }
                    }
                }

                $feed['items'][] = array(
                    'permalink'    => $item->get_permalink(),
                	'title'        => $item->get_title(),
                    'description'  => $item->get_description(),
                    'timestamp'    => $itemDate,
                    'image'        => $img,
                );

                $i++;
            }

        } catch (\FeedParserException $fpe) {
            $this->logger->error($fpe->getMessage());
            $feed = array('status' => $fpe->getCode(), 'item_count' => 0, 'items' => array());
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $feed = array('status' => $e->getCode(), 'item_count' => 0, 'items' => array());
        }

        $this->logger->info("Putting feed into cache with key [$cacheKey] and ttl of [{$this->cacheTtl}]");
        $this->cacheStore->put($cacheKey, $feed, $this->cacheTtl);

        $feed['items'] = array_slice($feed['items'], 0, $count);
        return $feed;
    }
}