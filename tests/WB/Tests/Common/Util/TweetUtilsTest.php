<?php

namespace WB\Tests\Common\Util;

use \TweetUtils;

class TweetUtilsTest extends \PHPUnit_Framework_TestCase
{
    public function testLinkify()
    {
        $tweets = [
            [
                'original' => 'A @user #hashtags a http://link.com/test',
                'linkified' => 'A <a href="https://twitter.com/user" target="_blank" class="user"><span>@</span>user</a> ' .
                    '<a href="https://twitter.com/search?q=%23hashtags" target="_blank" class="hashtag"><span>#</span>hashtags</a> a ' .
                    '<a href="http://link.com/test" target="_blank" class="link">http://link.com/test</a>'
            ],
            [
                'original' => 'A .@user #hashtags\'invalid a http://link.com/test',
                'linkified' => 'A .@user ' .
                    '<a href="https://twitter.com/search?q=%23hashtags" target="_blank" class="hashtag"><span>#</span>hashtags</a>\'invalid a ' .
                    '<a href="http://link.com/test" target="_blank" class="link">http://link.com/test</a>'
            ],
            [
                'original' => '@user #1 a www.link.com/test',
                'linkified' => '<a href="https://twitter.com/user" target="_blank" class="user"><span>@</span>user</a> ' .
                    '<a href="https://twitter.com/search?q=%231" target="_blank" class="hashtag"><span>#</span>1</a> a ' .
                    '<a href="http://www.link.com/test" target="_blank" class="link">www.link.com/test</a>'
            ],
        ];

        foreach ($tweets as $tweet) {
            $result = TweetUtils::linkify($tweet['original']);
            $this->assertSame($result, $tweet['linkified']);
        }
    }
}
