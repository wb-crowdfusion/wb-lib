<?php

class TweetUtils
{
    /**
     * Linkifies all users, hashtags and urls in a tweet.
     *
     * @param string $tweet
     * @param string $target
     * @return string
     */
    public static function linkify($tweet, $target = '_blank')
    {
        if ($target == null) {
        	$target = '_blank';
        }

        $tweet = preg_replace("#(^|[\n ])@([a-z0-9_-]*)#ise", "'\\1<a href=\"http://twitter.com/\\2\" target=\"".$target."\" class=\"user\"><span>@</span>\\2</a>'", $tweet);
        $tweet = preg_replace("#(^|[\n ])\#([a-z0-9_-]*)#ise", "'\\1<a href=\"http://twitter.com/#!/search/%23\\2\" target=\"".$target."\" class=\"hashtag\"><span>#</span>\\2</a>'", $tweet);
        $tweet = preg_replace("#(^|[\n ])([\w]+?://[\w]+[^ \"\n\r\t<]*)#ise", "'\\1<a href=\"\\2\" target=\"".$target."\" class=\"link\">\\2</a>'", $tweet);
        $tweet = preg_replace("#(^|[\n ])((www|ftp)\.[^ \"\t\n\r<]*)#ise", "'\\1<a href=\"http://\\2\" target=\"".$target."\" class=\"link\">\\2</a>'", $tweet);
        return $tweet;
    }
}