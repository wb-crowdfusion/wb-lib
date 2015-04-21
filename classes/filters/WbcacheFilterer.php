<?php
/**
 *
 * @package     wb-lib
 * @version     $Id: $
 *
 */

class WbcacheFilterer extends AbstractFilterer
{
    /**
     * [IoC] Sets the durations from pluginconfig
     *
     * @param array $durations
     *
     * @return void
     *
     */
    private $durations = array();
    public function setWbcacheDurations(array $durations)
    {
        $this->durations = $durations;

        if (!array_key_exists('default', $this->durations))
            $this->durations['default'] = 300;

        if (!array_key_exists('forever', $this->durations))
            $this->durations['forever'] = 31536000; // 1 year
    }

    /*
     * Returns the duration, in seconds, by the
     * duration name.  Optionally provide FullPage
     * boolean to get an appropriate full page cache
     * time setting.
     *
     */
    public function getDuration()
    {
        $name = trim((string) $this->getParameter('name'));
        $fullPage = StringUtils::strToBool((string)$this->getParameter('FullPage'));
        if (empty($name))
            $name = 'default';

        if (in_array($name, explode(',', 'none,never,off,0,zero,no')))
            return 0;

        if (!array_key_exists($name, $this->durations))
            $name = 'default';

        $duration = abs((int)$this->durations[$name]);

        if (!$fullPage || $duration < 1)
            return $duration;

        // full page cache duration should be half the cache time
        return ceil($duration/2);
    }
}
