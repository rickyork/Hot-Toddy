CREATE TABLE `hFileActivity` (

    `hFileActivityId`
        int(32)
        NOT NULL
        auto_increment,

    `hFileId`
        int(11)
        NOT NULL
        default '0',

    `hFilePath`
        text
        NULL,
        
    `hFileWildcardPath`
        text
        NULL,

    `hFileReferrer`
        text
        NULL,

    `hUserId`
        int(11)
        NOT NULL 
        default '0',

    `hUserIPAddress`
        varchar(15)
        NULL,

    `hUserAgent` enum(
        'opera',
        'ie',
        'trident',
        'webkit',  # Safari, Chrome, etc.
        'khtml',   
        'gecko', # Firefox, et al.
        'w3c',
        'google',
        'bing',
        'ask jeeves',
        'yahoo',
        'netscape',
        'Other'
    ),

    `hUserAgentVersion` 
        float(4,2)
        NOT NULL 
        default '0',

    `hUserAgentOS` enum(
        'Windows CE',
        'Windows 95',
        'Windows 98',
        'Windows Server 2003',
        'Windows XP',
        'Windows 2000',
        'Windows Vista',
        'Windows 7',
        'Windows Phone',
        'Android',
        'BlackBerry',
        'iOS',
        'Mac OS X',
        'Nintendo Wii',
        'Nokia',
        'Windows NT',
        'Windows',
        'Linux',
        'Google',
        'Yahoo',
        'Ask Jeeves',
        'Bing',
        'Other Bot',
        'Other'
    ),

    `hUserAgentOSVersion`
        float(4,2)
        NOT NULL
        default '0',

    `hUserInterfaceIdiom` enum(
        'Pad',
        'Phone',
        'Desktop'
    ),
    
    `hUserAgentIsRobot`  # Boolean
        tinyint(1)
        NOT NULL
        default '0',
        
    `hUserAgentIsMobile` # Boolean
        tinyint(1)
        NOT NULL
        default '0',
        
    `hUserAgentChromeFrame` # Boolean
        tinyint(1)
        NOT NULL
        default '0',
        
    `hUserAgentRaw` # Full, unparsed user-agent string
        text
        NULL,
        
    `hUserScreenResolution`
        varchar(15)
        NULL,
        
    `hUserScreenColorDepth`
        int(3)
        NOT NULL
        default '0',
        
    `hFileExecutionBenchmark` # Time in milliseconds
        int(11)
        NOT NULL
        default '0',
        
    `hDatabaseQueryBenchmark` # Time in milliseconds
        int(11)
        NOT NULL 
        default '0',
        
    `hDatabaseQueryCount` # Number of queries executed during the page build
        int(5)
        NOT NULL 
        default '0',
        
    `hFileNetworkBenchmark` # Time in milliseconds
        int(5)
        NOT NULL 
        default '0',
        
    `hFilePageLoadBenchmark` # Time in milliseconds
        int(11)
        NOT NULL 
        default '0',
    
    `hFileAccessed`
        int(32)
        NOT NULL 
        default '0',
        
    `hFileAccessedGMT`
        int(32)
        NOT NULL 
        default '0',
    
    PRIMARY KEY  (`hFileActivityId`)

) ENGINE=MyISAM  DEFAULT CHARSET=utf8;