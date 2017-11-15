<?php

# Protect against web entry
if ( !defined( 'MEDIAWIKI' ) ) {
    exit;
}

if ( getenv( 'MW_SHOW_EXCEPTION_DETAILS' ) === 'true' ) {
    $wgShowExceptionDetails = true;
}

########################### Core Settings ##########################
# Site language code, should be one of the list in ./languages/Names.php
$wgLanguageCode = getenv( 'MW_SITE_LANG' );

## The protocol and server name to use in fully-qualified URLs
$wgServer = getenv( 'MW_SITE_SERVER' );

# Internal server name as known to Squid, if different than $wgServer.
#$wgInternalServer = false;

# The name of the site. This is the name of the site as displayed throughout the site.
$wgSitename  = getenv( 'MW_SITE_NAME' );

# Allow images and other files to be uploaded through the wiki.
$wgEnableUploads  = getenv( 'MW_ENABLE_UPLOADS' );

# Default skin: you can change the default skin. Use the internal symbolic
# names, ie 'standard', 'nostalgia', 'cologneblue', 'monobook', 'vector':
$wgDefaultSkin = getenv( 'MW_DEFAULT_SKIN' );

# InstantCommons allows wiki to use images from http://commons.wikimedia.org
$wgUseInstantCommons  = getenv( 'MW_USE_INSTANT_COMMONS' );

# Name used for the project namespace. The name of the meta namespace (also known as the project namespace), used for pages regarding the wiki itself.
#$wgMetaNamespace = 'Project';
#$wgMetaNamespaceTalk = 'Project_talk';

# The relative URL path to the logo.  Make sure you change this from the default,
# or else you'll overwrite your logo when you upgrade!
$wgLogo = "$wgScriptPath/logo.png";

# The URL of the site favicon (the small icon displayed next to a URL in the address bar of a browser)
$wgFavicon = "$wgScriptPath/favicon.ico";

##### Short URLs
## https://www.mediawiki.org/wiki/Manual:Short_URL
$wgArticlePath = '/wiki/$1';
## Also see mediawiki.conf

##### Improve performance
# https://www.mediawiki.org/wiki/Manual:$wgMainCacheType
switch ( getenv( 'MW_MAIN_CACHE_TYPE' ) ) {
    case 'CACHE_ACCEL':
        # APC has several problems in latest versions of WediaWiki and extensions, for example:
        # https://www.mediawiki.org/wiki/Extension:Flow#.22Exception_Caught:_CAS_is_not_implemented_in_Xyz.22
        $wgMainCacheType = CACHE_ACCEL;
        $wgSessionCacheType = CACHE_DB; #This may cause problems when CACHE_ACCEL is used
        break;
    case 'CACHE_DB':
        $wgMainCacheType = CACHE_DB;
        break;
    case 'CACHE_DB':
        $wgMainCacheType = CACHE_DB;
        break;
    case 'CACHE_ANYTHING':
        $wgMainCacheType = CACHE_ANYTHING;
        break;
    case 'CACHE_MEMCACHED':
        # Use Memcached, see https://www.mediawiki.org/wiki/Memcached
        $wgMainCacheType = CACHE_MEMCACHED;
        $wgParserCacheType = CACHE_MEMCACHED; # optional
        $wgMessageCacheType = CACHE_MEMCACHED; # optional
        $wgMemCachedServers = explode( ',', getenv( 'MW_MEMCACHED_SERVERS' ) );
        $wgSessionsInObjectCache = true; # optional
        $wgSessionCacheType = CACHE_MEMCACHED; # optional
        break;
    default:
        $wgMainCacheType = CACHE_NONE;
}

# Use Varnish accelerator
$tmpProxy = getenv( 'MW_PROXY_SERVERS' );
if ( $tmpProxy ) {
    # https://www.mediawiki.org/wiki/Manual:Varnish_caching
    $wgUseSquid = true;
    $wgSquidServers = explode( ',', $tmpProxy );
    $wgUsePrivateIPs = true;
    $wgHooks['IsTrustedProxy'][] = function( $ip, &$trusted ) {
        // Proxy can be set as a name of proxy container
        if ( !$trusted ) {
            global $wgSquidServers;
            foreach ( $wgSquidServers as $proxy ) {
                if ( !ip2long( $proxy ) ) { // It is name of proxy
                    if ( gethostbyname( $proxy ) === $ip ) {
                        $trusted = true;
                        return;
                    }
                }
            }
        }
    };
}
//Use $wgSquidServersNoPurge if you don't want MediaWiki to purge modified pages
//$wgSquidServersNoPurge = array('127.0.0.1');

####################### Bundled extensions #########################
wfLoadExtension( 'Cite' );
wfLoadExtension( 'CiteThisPage' );
wfLoadExtension( 'ConfirmEdit' );
wfLoadExtension( 'Gadgets' );
wfLoadExtension( 'ImageMap' );
wfLoadExtension( 'InputBox' );
wfLoadExtension( 'LocalisationUpdate' );
wfLoadExtension( 'Nuke' );
wfLoadExtension( 'ParserFunctions' );
wfLoadExtension( 'PdfHandler' );
wfLoadExtension( 'Poem' );
wfLoadExtension( 'Renameuser' );

wfLoadExtension( 'SpamBlacklist' );
/*$wgSpamBlacklistFiles = array(
   "https://meta.wikimedia.org/w/index.php?title=Spam_blacklist&action=raw&sb_ver=1",
   "https://en.wikipedia.org/w/index.php?title=MediaWiki:Spam-blacklist&action=raw&sb_ver=1"
);*/

wfLoadExtension( 'TitleBlacklist' );
/*$wgTitleBlacklistSources = array(
    array(
         'type' => 'localpage',
         'src'  => 'MediaWiki:Titleblacklist',
    ),
    array(
         'type' => 'url',
         'src'  => 'https://meta.wikimedia.org/w/index.php?title=Title_blacklist&action=raw',
    ),
    array(
         'type' => 'file',
         'src'  => '/home/wikipedia/blacklists/titles',
    ),
);*/

wfLoadExtension( 'SyntaxHighlight_GeSHi' );
$wgPygmentizePath = '/usr/bin/pygmentize';

wfLoadExtension( 'Interwiki' );
$wgGroupPermissions['sysop']['interwiki'] = true;

##################### Commonly used extensions #####################
# https://www.mediawiki.org/wiki/Extension:Flow
$flowNamespaces = getenv( 'MW_FLOW_NAMESPACES' );
if ( $flowNamespaces ) {
    wfLoadExtension( 'Flow' );
    $wgFlowContentFormat = 'html';
    foreach ( explode( ',', $flowNamespaces ) as $ns ) {
        $wgNamespaceContentModels[ constant( $ns ) ] = 'flow-board';
    }
}

wfLoadExtension( 'Thanks' );
wfLoadExtension( 'Echo' );

wfLoadExtension( 'CheckUser' );
$wgGroupPermissions['sysop']['checkuser'] = true;
$wgGroupPermissions['sysop']['checkuser-log'] = true;

############### MediaWiki Language Extension Bundle ################
wfLoadExtension( 'Babel' );

wfLoadExtension( 'CleanChanges' );
#$wgDefaultUserOptions['usenewrc'] = 1;

wfLoadExtension( 'UniversalLanguageSelector' );

wfLoadExtension( 'cldr' );

############################ WikiEditor ############################
wfLoadExtension( 'WikiEditor' );
# Enables use of WikiEditor by default but still allows users to disable it in preferences
$wgDefaultUserOptions['usebetatoolbar'] = 1;

# Enables link and table wizards by default but still allows users to disable them in preferences
$wgDefaultUserOptions['usebetatoolbar-cgd'] = 1;

# Displays the Preview and Changes tabs
$wgDefaultUserOptions['wikieditor-preview'] = 1;

# Displays the Publish and Cancel buttons on the top right side
$wgDefaultUserOptions['wikieditor-publish'] = 1;

########################### VisualEditor ###########################
$tmpRestDomain = getenv( 'MW_REST_DOMAIN' );
$tmpRestParsoidUrl = getenv( 'MW_REST_PARSOID_URL' );
if ( $tmpRestDomain && $tmpRestParsoidUrl ) {
    wfLoadExtension( 'VisualEditor' );

    // Enable by default for everybody
    $wgDefaultUserOptions['visualeditor-enable'] = 1;

    // Optional: Set VisualEditor as the default for anonymous users
    // otherwise they will have to switch to VE
    // $wgDefaultUserOptions['visualeditor-editor'] = "visualeditor";

    // Don't allow users to disable it
    $wgHiddenPrefs[] = 'visualeditor-enable';

    // OPTIONAL: Enable VisualEditor's experimental code features
    #$wgDefaultUserOptions['visualeditor-enable-experimental'] = 1;

    $wgVirtualRestConfig['modules']['parsoid'] = [
            // URL to the Parsoid instance
            'url' => $tmpRestParsoidUrl,
            // Parsoid "domain", see below (optional)
            'domain' => $tmpRestDomain,
            // Parsoid "prefix", see below (optional)
            'prefix' => $tmpRestDomain,
    ];

    $tmpRestRestbaseUrl = getenv( 'MW_REST_RESTBASE_URL' );
    if ( $tmpRestRestbaseUrl ) {
        $wgVirtualRestConfig['modules']['restbase'] = [
        'url' => $tmpRestRestbaseUrl,
        'domain' => $tmpRestDomain,
        'parsoidCompat' => false
        ];

        $tmpRestProxyPath = getenv( 'MW_REST_RESTBASE_PROXY_PATH' );
        if ( $tmpProxy && $tmpRestProxyPath ) {
            $wgVisualEditorFullRestbaseURL = $wgServer . $tmpRestProxyPath;
        } else {
            $wgVisualEditorFullRestbaseURL = $wgServer . ':' . getenv( 'MW_REST_RESTBASE_PORT' ) . "/$tmpRestDomain/";
        }
        $wgVisualEditorRestbaseURL = $wgVisualEditorFullRestbaseURL . 'v1/page/html/';
    }
}

########################### Search Type ############################
switch( getenv( 'MW_SEARCH_TYPE' ) ) {
    case 'CirrusSearch':
        # https://www.mediawiki.org/wiki/Extension:CirrusSearch
        wfLoadExtension( 'Elastica' );
        require_once "$IP/extensions/CirrusSearch/CirrusSearch.php";
        $wgCirrusSearchServers =  explode( ',', getenv( 'MW_CIRRUS_SEARCH_SERVERS' ) );
        if ( $flowNamespaces ) {
            $wgFlowSearchServers = $wgCirrusSearchServers;
        }
        $wgSearchType = 'CirrusSearch';
        break;
    default:
        $wgSearchType = null;
}

######################### MultimediaViewer ##########################
wfLoadExtension('MultimediaViewer');

######################### MobileFrontend ##########################
wfLoadExtension( 'MobileFrontend' );
$wgMFAutodetectMobileView = true;

######################### ElectronPdfService ##########################
wfLoadExtension('ElectronPdfService');

######################### Custom Settings ##########################
@include( 'CustomSettings.php' );
