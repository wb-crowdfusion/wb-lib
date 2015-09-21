<?php

/**
 * Provides functions for getting the sourcepoint js.
 */

class WbsourcepointWebController extends NodeWebController
{
    /** @var string */
    protected $apiKey;

    /** @var string */
    protected $apiUrl;

    /**
     * Auto-wired from config 'cft.constants'
     *
     * @param array $cftConstants
     */
    public function setCftConstants(array $cftConstants)
    {
        $this->apiKey = $cftConstants['SOURCEPOINT_API_KEY'];
        $this->apiUrl = $cftConstants['SOURCEPOINT_API_URL'];
    }

    /**
     * Generates script url.
     *
     * @return string
     */
    public function getAsScript()
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, sprintf('%s?delivery=script', $this->apiUrl));
        curl_setopt($curl, CURLOPT_HTTPHEADER, [('Authorization: Token %s', $this->apiKey)]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($curl);

        curl_close($curl);

        return ['Script' => $response];
    }
}
