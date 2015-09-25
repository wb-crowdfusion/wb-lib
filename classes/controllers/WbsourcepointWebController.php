<?php

/**
 * Provides functions for getting the sourcepoint js.
 */
class WbsourcepointWebController extends NodeWebController
{
    /** @var \CacheStoreInterface */
    protected $distributedCacheStore;

    /** @var int */
    protected $cacheTtl = 86400; //24 hours

    /** @var int */
    protected $failedCacheTtl = 3600; //1 hour

    /**
     * @param \CacheStoreInterface $val
     */
    public function setDistributedCacheStore(\CacheStoreInterface $val)
    {
        $this->distributedCacheStore = $val;
    }

    /**
     * Generates script url.
     *
     * @return string
     */
    public function getAsScript()
    {
        $apiUrl = $this->getRequiredTemplateVariable('ApiUrl');
        $apiKey = $this->getRequiredTemplateVariable('ApiKey');

        if (!$apiUrl || !$apiKey) {
            return ['data' => ['script' => null]];
        }

        //$this->Logger->debug('looking in cache for script with cacheKey:' . $this->cacheKey());
        if ($this->distributedCacheStore->containsKey($this->cacheKey())) {
            $cachedScript = $this->distributedCacheStore->get($this->cacheKey());
            //$this->Logger->debug(sprintf('...found a match in DCS for cacheKey: %s', $this->cacheKey()));
            return ['data' => ['script' => $cachedScript]];
        }

        try {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, sprintf('%sscript?delivery=script', $apiUrl));
            curl_setopt($curl, CURLOPT_HTTPHEADER, [sprintf('Authorization: Token %s', $apiKey)]);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 1);
            curl_setopt($curl, CURLOPT_TIMEOUT, 1);
            $script = curl_exec($curl);
            curl_close($curl);

            // validate script tag
            // ex: "<script async="async" data-client-id="RXcVfPPwlbdGjwq" type="text/javascript" src="//d3ujids68p6xmq.cloudfront.net/abw.js"></script>"
            if (preg_match('/<script(.*?)(\\/>|<\\/script>)/i', $script) !== false) {
                //$this->Logger->debug(sprintf('Found script?: %s', $script));
                //$this->Logger->debug(sprintf('Update that cacheKey "%s" with: %s', $this->cacheKey(), $script));
                $this->distributedCacheStore->put($this->cacheKey(), $script, $this->cacheTtl);

                return ['data' => ['script' => $script]];
            }
        } catch (\Exception $e) {
            $this->Logger->debug(sprintf('CURL failed with: %s', $e->getMessage()));
            $this->Logger->debug(sprintf('Update that cacheKey "%s"  to null', $this->cacheKey()));
            $this->distributedCacheStore->put($this->cacheKey(), null, $this->failedCacheTtl);
        }

        return ['data' => ['script' => null]];
    }

    /**
     * Returns a namepsaced cache key
     *
     * @return string
     */
    private function cacheKey()
    {
        return 'wblib:aws:sourcepoint';
    }
}
