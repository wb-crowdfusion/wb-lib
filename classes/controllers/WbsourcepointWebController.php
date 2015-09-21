<?php

/**
 * Provides functions for getting the sourcepoint js.
 */
class WbsourcepointWebController extends NodeWebController
{
    /** @var \CacheStoreInterface */
    protected $DistributedCacheStore;

    /** @var int */
    protected $cacheTtl = 86400; //24 hours

    /**
     * @param \CacheStoreInterface $DistributedCacheStore
     */
    public function setDistributedCacheStore(\CacheStoreInterface $DistributedCacheStore)
    {
        $this->DistributedCacheStore = $DistributedCacheStore;
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

        if ($this->DistributedCacheStore) {
            $this->Logger->debug('looking in cache for script with cacheKey:' . $this->cacheKey());

            if ($this->DistributedCacheStore->containsKey($this->cacheKey())) {
                $cachedScript = $this->DistributedCacheStore->get($this->cacheKey());
                $this->Logger->debug(sprintf('...found a match in DCS for cacheKey: %s', $this->cacheKey()));
                return ['data' => ['script' => $cachedScript]];
            }
        }

        try {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, sprintf('%sscript?delivery=script', $this->getRequiredTemplateVariable('ApiUrl')));
            curl_setopt($curl, CURLOPT_HTTPHEADER, [sprintf('Authorization: Token %s', $this->getRequiredTemplateVariable('ApiKey'))]);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 1);
            curl_setopt($curl, CURLOPT_TIMEOUT, 1);
            $script = curl_exec($curl);
            curl_close($curl);

            // validate script tag
            // ex: "<script async="async" data-client-id="RXcVfPPwlbdGjwq" type="text/javascript" src="//d3ujids68p6xmq.cloudfront.net/abw.js"></script>"
            if (preg_match('/<script(.*?)(\\/>|<\\/script>)/i', $script) !== false) {
                $this->Logger->debug(sprintf('Found script?: %s', $script));

                if ($this->DistributedCacheStore) {
                    $this->Logger->debug(sprintf('Update that cacheKey "%s" with: %s', $this->cacheKey(), $script));
                    $this->DistributedCacheStore->put($this->cacheKey(), $script, $this->cacheTtl);
                }

                return ['data' => ['script' => $script]];
            }
        } catch (\Exception $e) {
            // do nothing
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
