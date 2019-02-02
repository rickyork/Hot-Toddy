<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Facebook Library
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
# @description
# <h1>Facebook API</h1>
# <p>
#   This object is the beginning of Hot Toddy integration with Facebook.  So far, the 
#   only functionality implemented is the following three things:
# </p>
# <ol>
#   <li>
#       <p>
#           <b><i>Like button</i></b>: In Hot Toddy blogs the Facebook like button can be 
#           enabled by setting the framework variable <var>hCalendarBlogFacebookLikeButton</var>
#           to <var>true</var> in your blog or site configuration file.
#       </p>
#   </li>
#   <li>
#       <p>
#           <b><i>Thumbnails</i></b>: Facebook thumbnails can be customized for links people
#           post to Facebook.  To customize a page's thumbnail, specify the path to the thumbnail
#           you'd like to use in the framework variable <var>hFacebookThumbnail</var>.
#           <var>hFacebookThumbnail</var> adds the thumbnail configuration to the HTML 
#           <var>&lt;head&gt;</var> section.  This is the HTML added:
#       </p>
#       <code>&lt;meta property='og:image' content='{/hFacebookThumbnail}' /&gt;</code>
#   </li>
#   <li>
#       <p>
#           <b><i>Facebook Applications</i></b>: This object, <var>hFacebookLibrary</var> when 
#           initiated  adds the following to the document <var>&lt;body&gt;</var>.
#       </p>
#       <p>
#           <a href='/System/Framework/Hot Toddy/hFacebook/HTML/Facebook.html' target='_blank'>Facebook.html</a>
#       </p>
#   </li>
# </ol>
# <p>
#   Work on this plugin is ongoing.  Feel free to improve it.
# </p>
# @end

class hFacebookLibrary extends hPlugin {

    public function hConstructor()
    {
        $this->hFileDocumentAppend .= $this->getTemplate('Facebook');
    }
    
    public function like()
    {
        
    }
    
    public function comments()
    {
    
    }
}

?>