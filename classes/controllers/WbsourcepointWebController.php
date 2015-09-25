<?php

/**
 * Provides functions for getting the sourcepoint js.
 */
class WbsourcepointWebController extends AbstractWebController
{
    /** @var \CacheStoreInterface */
    protected $cacheStore;

    /**
     * 24 hours
     * @var int
     */
    protected $cacheTtl = 86400;

    /**
     * 1 hour
     * @var int
     */
    protected $failedCacheTtl = 3600;

    /**
     * @param \CacheStoreInterface $val
     */
    public function setDistributedCacheStore(\CacheStoreInterface $val)
    {
        $this->cacheStore = $val;
    }

    /**
     * Gets a script url from sourcepoint and caches it for a day.
     *
     * @return array
     */
    protected function getAsScript()
    {
        $apiUrl = trim($this->getTemplateVariable('api_url'));
        $apiKey = trim($this->getTemplateVariable('api_key'));

        $failedValue = '<!--sourcepoint-failed-->';
        $cacheKey = 'wblib:sourcepoint:script';

        if (empty($apiUrl) || empty($apiKey)) {
            return ['data' => ['script' => $failedValue]];
        }

        $cachedScript = $this->cacheStore->get($cacheKey);
        if (false !== $cachedScript) {
            return ['data' => ['script' => $cachedScript]];
        }

        $scriptEndpoint = sprintf('%sscript?delivery=script', $apiUrl);
        $script = null;

        try {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $scriptEndpoint);
            curl_setopt($curl, CURLOPT_HTTPHEADER, [sprintf('Authorization: Token %s', $apiKey)]);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 1);
            curl_setopt($curl, CURLOPT_TIMEOUT, 1);
            $script = curl_exec($curl);
            curl_close($curl);

            // validate script tag
            // e.g.: "<script async="async" data-client-id="RXcVfPPwlbdGjwq" type="text/javascript" src="//d3ujids68p6xmq.cloudfront.net/abw.js"></script>"
            if (preg_match('/<script(.*?)(\\/>|<\\/script>)/i', $script) !== false) {
                $this->cacheStore->put($cacheKey, $script, $this->cacheTtl);
            } else {
                $script = $failedValue;
                $this->cacheStore->put($cacheKey, $failedValue, $this->failedCacheTtl);
            }
        } catch (\Exception $e) {
            $this->Logger->error(sprintf('CURL to [%s] failed with: %s', $scriptEndpoint, $e->getMessage()));
            $this->cacheStore->put($cacheKey, $failedValue, $this->failedCacheTtl);
        }

        return ['data' => ['script' => $script]];
    }
}
