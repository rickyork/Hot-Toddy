<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Flickr Library
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

class hFlickrLibrary extends hPlugin {

    private $phpFlickr;

    public function hConstructor()
    {
        error_reporting(0);

        include $this->hFrameworkLibraryPath.'/phpFlickr/phpFlickr.php';

        if ($this->hFlickrKey && $this->hFlickrSecret)
        {
            $this->phpFlickr = new phpFlickr($this->hFlickrKey, $this->hFlickrSecret);

            //For accessing private photos, use this tool to get your login token and put it in hFramework.conf
            //http://www.phpflickr.com/tools/auth/
            if($this->hFlickrToken)
            {
                $this->phpFlickr->setToken('72157623466888302-3a112fc8627e3eed');
            }

/*
            $this->phpFlickr->enableCache(
                'db',
                'mysql://'.
                    urlencode($this->hDatabaseUser).':'.
                    urlencode($this->hDatabasePassword).'@'.
                    $this->hDatabaseHost.'/'.
                    $this->hDatabaseInitial
            );
*/
        }
        else
        {
            $this->warning('Either the Flickr API key or secret key is not set.', __FILE__, __LINE__);
        }
    }

    // http://www.flickr.com/groups/westlakedesign
    public function getGroupPhotosByURL($url, $countPerPage = 6, $page = 1)
    {
        $group  = $this->phpFlickr->urls_lookupGroup($url);
        $photos = $this->phpFlickr->groups_pools_getPhotos($group['id'], null, null, null, $countPerPage, $page);

        $urls = array();

        foreach ($photos['photo'] as $photo)
        {
            // var_dump($photos['photo']);
            array_push(
                $urls,
                "<a href='http://www.flickr.com/photos/{$photo['owner']}/{$photo['id']}/in/pool-".basename($url)."' target='_blank'>".
                    "<img src='".$this->phpFlickr->buildPhotoURL($photo, 'Square')."' alt='Flickr Photo' />".
                "</a>\n"
            );
        }

        return $urls;
    }

    public function getUserPhotos($username, $countPerPage = 6, $page = 1)
    {
        // Find the NSId of the username inputted via the form
        $person     = $this->phpFlickr->people_findByUsername($username);

        // Get the friendly URL of the user's photos
        $photos_url = $this->phpFlickr->urls_getUserPhotos($person['id']);

        // Get the user's first 6 public photos
        $photos     = $this->phpFlickr->people_getPublicPhotos($person['id'], null, null, $countPerPage, $page);

        $urls = array();

        foreach ($photos['photos']['photo'] as $photo)
        {
            array_push(
                $urls,
                "<a href='{$photos_url}{$photo['id']}' target='_blank'><img src='".$phpFlickr->buildPhotoURL($photo, 'Square')."' alt='Flickr Photo' /></a>\n"
            );
        }

        return $urls;
    }

    public function getRandomPhotosByURL($url, $countPerPage = 6)
    {
        // Get the group Id and query the public photos
        $group  = $this->phpFlickr->urls_lookupGroup($url);
        $photos = $this->phpFlickr->groups_pools_getPhotos($group['id'], null, null, null, null, null);

        // Randomize the order
        shuffle($photos['photo']);

        $urls = array();
        $i = 0;

        foreach ($photos['photo'] as $photo)
        {
            array_push(
                $urls,
                "<a href='http://www.flickr.com/photos/{$photo['owner']}/{$photo['id']}/in/pool-".basename($url)."' target='_blank'>".
                    "<img src='".$this->phpFlickr->buildPhotoURL($photo, 'Square')."' alt='Flickr Photo' />".
                "</a>\n"
            );

            //End the loop once we have reached the requested countPerPage (default 6)
            $i++;

            if ($i >= $countPerPage)
            {
                break;
            }
        }

        return $urls;
    }

    // Return some data to build a basic thumbnail of a photoset
    public function getPhotoSetThumbnail($photoset_id)
    {
        $info = array();
        $photoset = $this->phpFlickr->photosets_getInfo($photoset_id);

        $info['URL'] = "http://www.flickr.com/photos/{$photoset['owner']}/sets/{$photoset['id']}";

        $info['Title'] = $photoset['title'];

        $info['Thumbnail'] = $this->phpFlickr->buildPhotoURL(
            array(
                'id'=>$photoset['primary'],
                'farm'=>$photoset['farm'],
                'server'=>$photoset['server'],
                'secret'=>$photoset['secret']
            ),
            'small'
        );

        return $info;
    }

    //Return an unordered html list of photos from a Flickr Set
    public function getPhotoSet($photoset_id, $num_photos = null)
    {
            $photos = $this->phpFlickr->photosets_getPhotos($photoset_id, null, null, $num_photos);
            $info = $this->phpFlickr->photosets_getInfo($photoset_id);
            $html = "<div class=\"hFlickrPhotoSet\"><h3>{$info['title']}</h3><ul>";
            foreach($photos['photoset']['photo'] as $photo)
            {
                    $photoUrl = $this->phpFlickr->buildPhotoUrl($photo);
                    $thumbUrl = $this->phpFlickr->buildPhotoUrl($photo, "square");
                    $html .= "<li><a href=\"$photoUrl\" title=\"{$photo['title']}\"><img src=\"$thumbUrl\" /></a></li>";
            }
            $html .= "</ul></div>";
            return $html;
    }
}

?>