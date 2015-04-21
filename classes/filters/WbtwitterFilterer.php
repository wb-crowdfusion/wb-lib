<?php
class WbtwitterFilterer extends AbstractFilterer
{
    /**
     * @return string
     */
    protected function getDefaultMethod()
	{
		return "linkify";
	}

    /**
     * @return string
     */
    public function linkify()
	{
        $tweet = (string) $this->getRequiredParameter('tweet');
        $target = $this->getParameter('target');
        return TweetUtils::linkify($tweet, $target);
	}
}