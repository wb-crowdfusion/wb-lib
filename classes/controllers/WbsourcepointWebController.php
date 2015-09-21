<?php

/**
 * Provides functions for getting the sourcepoint js.
 */
class WbsourcepointWebController extends NodeWebController
{
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
            return ['script' => null];
        }

        try {
            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL, sprintf('%sscript?delivery=script', $this->getRequiredTemplateVariable('ApiUrl')));
            curl_setopt($curl, CURLOPT_HTTPHEADER, [('Authorization: Token %s', $this->getRequiredTemplateVariable('ApiKey'))]);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 1);
            curl_setopt($curl, CURLOPT_TIMEOUT, 1);

            $script = curl_exec($curl);

            curl_close($curl);

            // validate script tag
            // ex: "<script async="async" data-client-id="RXcVfPPwlbdGjwq" type="text/javascript" src="//d3ujids68p6xmq.cloudfront.net/abw.js"></script>"
            if (preg_match('/<script(.*?)(\\/>|<\\/script>)/i', $script) !== false) {
                return ['script' => $script];
            }
        } catch (\Exception $e) {
            // do nothing
        }

        return ['script' => null];
    }
}
