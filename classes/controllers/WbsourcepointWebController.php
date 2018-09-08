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
     * Please use getScript() going forward.
     * @deprecated
     */
    public function getAsScript() {
        return $this->getScript();
    }

    /**
     * Gets a script url from sourcepoint and caches it for a day.
     *
     * Accepts the following arguments:
     *  delivery = script | inline | bundle
     *  response = json
     *
     * Note: JSON response is only supported when delivery is set to script.
     *
     * @return array
     */
    protected function getScript()
    {
        $apiUrl = trim($this->getTemplateVariable('api_url'));
        $apiKey = trim($this->getTemplateVariable('api_key'));

        $delivery = $this->getTemplateVariable('delivery');
        if (!$delivery) {
            $delivery = 'script';
        }
        $response = $this->getTemplateVariable('response');

        $failedValue = ($delivery === 'script' && $response === 'json') ?
            '{} /* sourcepoint failed */' : '<!--sourcepoint-failed-->';
        $cacheKey = sprintf('wblib:sourcepoint:script:%s:%s', $delivery, $response);

        if (empty($apiUrl) || empty($apiKey)) {
            return ['data' => ['script' => $failedValue]];
        }

        $cachedScript = $this->cacheStore->get($cacheKey);
        if (false !== $cachedScript) {
            return ['data' => ['script' => $cachedScript]];
        }

        $scriptEndpoint = sprintf('%sscript/detection?delivery=%s', $apiUrl, $delivery);
        $script = null;

        try {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $scriptEndpoint);
            curl_setopt($curl, CURLOPT_HTTPHEADER, [sprintf('Authorization: Token %s', $apiKey)]);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 1);
            curl_setopt($curl, CURLOPT_TIMEOUT, 1);

            $script = trim(curl_exec($curl));
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            curl_close($curl);

            if ((200 <= $httpCode) && ($httpCode < 400)) {
                if ($delivery === 'script' && $response === 'json') {
                    $el = new SimpleXmlElement($script);
                    $attributes = (array) $el->attributes();
                    $attributesArr = $attributes['@attributes'];
                    $script = json_encode($attributesArr);
                    $this->cacheStore->put($cacheKey, $script, $this->cacheTtl);
                } else {
                    // This can be a number of different formats
                    // URL: https://analytics.sourcepoint.com/doc/index#get-script-as-tag-on-page
                    // e.g.: <script async="async" data-client-id="YOUR_CLIENT_ID" type="text/javascript" src="//d3ujids68p6xmq.cloudfront.net/abw.js"></script>
                    // e.g.: <script data-client-id="YOUR_CLIENT_ID" type="text/javascript">function detect() {};</script>
                    // e.g.: function detect() {};
                    $this->cacheStore->put($cacheKey, $script, $this->cacheTtl);
                }
            } else {
                $this->Logger->error(sprintf('Received an HTTP error code: %s', $httpCode));
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
