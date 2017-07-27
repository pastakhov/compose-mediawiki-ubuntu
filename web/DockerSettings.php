<?php

# Protect against web entry
if ( !defined( 'MEDIAWIKI' ) ) {
    exit;
}

########################### Core Settings ##########################
# Site language code, should be one of the list in ./languages/Names.php
$wgLanguageCode = getenv( 'MW_SITE_LANG' ) ?: 'en';

## The protocol and server name to use in fully-qualified URLs
$wgServer = getenv( 'MW_SITE_SERVER' ) ?: $wgServer;

# The name of the site. This is the name of the site as displayed throughout the site.
$wgSitename  = getenv( 'MW_SITE_NAME' ) ?: $wgSitename;

# Allow images and other files to be uploaded through the wiki.
$wgEnableUploads  = getenv( 'MW_ENABLE_UPLOADS' );

# Default skin: you can change the default skin. Use the internal symbolic
# names, ie 'standard', 'nostalgia', 'cologneblue', 'monobook', 'vector':
$wgDefaultSkin = getenv( 'MW_DEFAULT_SKIN' ) ?: "vector";

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
$wgArticlePath = "/wiki/$1";
## Also see mediawiki.conf

##### Improve performance
# APC has several problems in latest versions of WediaWiki and extensions, for example:
# https://www.mediawiki.org/wiki/Extension:Flow#.22Exception_Caught:_CAS_is_not_implemented_in_Xyz.22
# https://www.mediawiki.org/wiki/Manual:$wgMainCacheType
#$wgMainCacheType = CACHE_ACCEL;
#$wgSessionCacheType = CACHE_DB; #This may cause problems when CACHE_ACCEL is used

# Use Memcached, see https://www.mediawiki.org/wiki/Memcached
$wgMainCacheType = CACHE_MEMCACHED;
$wgParserCacheType = CACHE_MEMCACHED; # optional
$wgMessageCacheType = CACHE_MEMCACHED; # optional
$wgMemCachedServers = [ 'memcache:11211' ];
$wgSessionsInObjectCache = true; # optional
$wgSessionCacheType = CACHE_MEMCACHED; # optional

# Use Varnish accelerator
# https://www.mediawiki.org/wiki/Manual:Varnish_caching
$wgUseSquid = true;
$wgSquidServers = [ 'proxy' ];
$wgUsePrivateIPs = true;
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

##################### Commonly used extensions ####################
wfLoadExtension( 'Thanks' );
require_once "$IP/extensions/Echo/Echo.php"; // REL1_29+ wfLoadExtension( 'Echo' );

require_once "$IP/extensions/Flow/Flow.php"; // REL1_29+ wfLoadExtension( 'Flow' );
$wgFlowContentFormat = 'html';
$wgFlowSearchServers = [ 'elasticsearch' ];
$wgNamespaceContentModels[NS_TALK] = 'flow-board';
$wgNamespaceContentModels[NS_USER_TALK] = 'flow-board';

wfLoadExtension( 'CheckUser' );
$wgGroupPermissions['sysop']['checkuser'] = true;
$wgGroupPermissions['sysop']['checkuser-log'] = true;

############### MediaWiki Language Extension Bundle ###############
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
	// Use port 8142 if you use the Debian package
	'url' => 'http://parsoid:8000',
	// Parsoid "domain", see below (optional)
	'domain' => 'localhost',
	// Parsoid "prefix", see below (optional)
	'prefix' => 'localhost'
];

$wgVirtualRestConfig['modules']['restbase'] = [
  'url' => "http://restbase:7231",
  'domain' => 'localhost',
  'parsoidCompat' => false
];

$wgVisualEditorRestbaseURL = "$wgServer/api/rest_v1/page/html/";
$wgVisualEditorFullRestbaseURL = "$wgServer/api/rest_";

########################## CirrusSearch ###########################
wfLoadExtension( 'Elastica' );
require_once "$IP/extensions/CirrusSearch/CirrusSearch.php";
$wgCirrusSearchServers = [ 'elasticsearch' ];
$wgSearchType = 'CirrusSearch';

######################### Custom Settings #########################
@include( 'CustomSettings.php' );
