<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Twitter Library
#//\\ @@@@  @@@@\\\\\|
#//\\\@@@@| @@@@\\\\\|
#//\\\ @@ |\\@@\\\\\\| http://www.hframework.com
#//\\\\  ||   \\\\\\\| © Copyright 2015 Richard York, All rights Reserved
#//\\\\  \\_   \\\\\\|
#//\\\\\        \\\\\| Use and redistribution are subject to the terms of the license.
#//\\\\\  ----  \@@@@| http://www.hframework.com/license
#//@@@@@\       \@@@@|
#//@@@@@@\     \@@@@@|
#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

class hTwitterLibrary extends hPlugin {

    private $twitter;
    private $tweets = null;
    private $friends = null;
    private $hFileCache;
    private $connected = false;

    public function hConstructor()
    {
        //error_reporting(0);
        $this->hFileCache = $this->library('hFile/hFileCache');
    }

    public function connect()
    {
        if (!$this->connected)
        {
            if ($this->hTwitterUsername && $this->hTwitterPassword)
            {
                include $this->hFrameworkLibraryPath.'/Arc90_Service_Twitter/lib/Arc90/Service/Twitter.php';

                if (class_exists('Arc90_Service_Twitter'))
                {
                    $this->twitter = new Arc90_Service_Twitter(
                        $this->hTwitterUsername,
                        $this->hTwitterPassword
                    );

                    $this->connected = true;
                }
                else
                {
                    $this->warning(
                        'The third-party library, Arc90_Service_Twitter, could not be found.',
                        __FILE__,
                        __LINE__
                    );
                }
            }
            else
            {
                $this->warning(
                    'Either a twitter username or password were not provided.',
                    __FILE__,
                    __LINE__
                );
            }
        }
    }

    public function getCachedTweet($type)
    {
        $fileCacheLastModified = $this->hFileCache->getCachedTimestamp(
            'twitter',
            $type.' for '.$this->hTwitterUsername
        );

        if ($fileCacheLastModified < time() + (15 * 60))
        {
            // Keep the cache
            $fileCacheLastModified = time() - (15 * 60);
        }
        else
        {
            // Update the cache
            $fileCacheLastModified = time();
        }

        return $this->hFileCache->getCachedDocument(
            'twitter',
            $type.' for '.$this->hTwitterUsername,
            $fileCacheLastModified
        );
    }

    public function cacheTweet($type, $tweet)
    {
        $this->hFileCache->saveDocumentToCache(
            'twitter',
            $type.' for '.$this->hTwitterUsername,
            $tweet
        );
    }

    public function getLatestTweetsFromFriends()
    {
        if (empty($this->friends))
        {
            $this->friends = $this->getCachedTweet('Latest tweets from friends');

            if ($this->friends === false)
            {
                $this->connect();
                $response = $this->twitter->getFriendsTimeline('xml');
                $this->friends = $response->getData();
                $this->cacheTweet('Latest tweets from friends', $this->friends);
            }

            $this->friends = simplexml_load_string($this->friends);
        }

        return $this->friends;
    }

    /**
    * Retrieve a single tweet.
    */
    public function getLatestTweetFromFriends()
    {
        $tweet = $this->getCachedTweet('Latest tweet from friends');

        if ($tweet === false)
        {
            $this->connect();

            if (empty($this->friends))
            {
                $this->getLatestTweetsFromFriends();
            }

            if (is_array($this->friends->status))
            {
                $tweet = $this->friends->status[0]->text;
            }
            else
            {
                $tweet = $this->friends->status->text;
            }

            $this->cacheTweet('Latest tweet from friends', $tweet);
        }

        return $tweet;
    }

    public function getLastestTweetScreenName()
    {
        $screenName = $this->getCachedTweet('Latest tweet screen name');

        if ($screenName === false)
        {
            $this->connect();

            if (empty($this->friends))
            {
                $this->getLatestTweetsFromFriends();
            }

            if (is_array($this->friends->status))
            {
                $screenName = $this->friends->status[0]->user->screen_name;
            }
            else
            {
                $screenName = $this->friends->status->user->screen_name;
            }

            $this->cacheTweet('Latest tweet screen name', $screenName);
        }

        return $screenName;
    }

    public function getLatestTweets()
    {
        if (empty($this->tweets))
        {
            $this->tweets = $this->getCachedTweet('Latest tweets');

            if ($this->tweets === false)
            {
                $this->connect();
                $response = $this->twitter->getUserTimeline('xml');
                $this->tweets = $response->getData();
                $this->cacheTweet('Latest tweets', $this->tweets);
            }

            $this->tweets = simplexml_load_string($this->tweets);
        }

        return $this->tweets;
    }

    public function getScreenName()
    {
        return $this->hTwitterUsername;
    }

    /**
    * Retrieve a single tweet.
    */
    public function getLatestTweet()
    {
        $tweet = $this->getCachedTweet('Latest tweet');

        if ($tweet === false)
        {
            $this->connect();
            $response = $this->twitter->showUser($this->hTwitterUsername, 'xml');

            if ($response && is_object($response))
            {
                $xml = simplexml_load_string($response->getData());

                if (is_object($xml->status) && is_object($xml->status->text) && isset($xml->status->text))
                {
                    $tweet = $this->linkify($xml->status->text);
                }

                $this->cacheTweet('Latest tweet', $tweet);
            }
            else
            {
                $this->notice("Unable to connect to twitter.", __FILE__, __LINE__);
            }
        }

        return $tweet;
    }

    public function getLatestTweetWithoutAuth()
    {
        $tweet = $this->getCachedTweet('Latest tweet');

        if ($tweet === false)
        {
            $xml = simplexml_load_file("http://twitter.com/statuses/user_timeline.xml?screen_name={$this->hTwitterUsername}&count=1");

            if (is_object($xml->status) && is_object($xml->status->text) && isset($xml->status->text))
            {
                $tweet = $this->linkify($xml->status->text);
            }

            $this->cacheTweet('Latest tweet', $tweet);
        }

        return $tweet;
    }

    public function linkify($text)
    {
        return preg_replace(',(?!<.*?)((?:ht|f)tps?://[^ \t\n"]+)(?![^<>]*?>),i', '<a href="$1">$1</a>', $text);
    }
}

?>