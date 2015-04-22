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

        $tweet = preg_replace_callback(
            "#(^|[\n ])@([a-z0-9_-]*)#is",
            function($matches) use ($target) {
                return sprintf('%s<a href="http://twitter.com/%s" target="%s" class="user"><span>@</span>%s</a>',
                    $matches[1], $matches[2], $target, $matches[2]);
            }, $tweet
        );

        $tweet = preg_replace_callback(
            "#(^|[\n ])\#([a-z0-9_-]*)#is",
            function($matches) use ($target) {
                return sprintf('%s<a href="http://twitter.com/#!/search/%%23%s" target="%s" class="hashtag"><span>#</span>%s</a>',
                    $matches[1], $matches[2], $target, $matches[2]);
            }, $tweet
        );

        $tweet = preg_replace_callback(
            "#(^|[\n ])([\w]+?://[\w]+[^ \"\n\r\t<]*)#is",
            function($matches) use ($target) {
                return sprintf('%s<a href="%s" target="%s" class="link">%s</a>',
                    $matches[1], $matches[2], $target, $matches[2]);
            }, $tweet
        );

        $tweet = preg_replace_callback(
            "#(^|[\n ])((www|ftp)\.[^ \"\t\n\r<]*)#is",
            function($matches) use ($target) {
                return sprintf('%s<a href="http://%s" target="%s" class="link">%s</a>',
                    $matches[1], $matches[2], $target, $matches[2]);
            }, $tweet
        );

        return $tweet;
    }
}
