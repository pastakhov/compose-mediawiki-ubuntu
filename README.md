# Containerized MediaWiki install based on Ubuntu.

## Briefly

This repo contains [Docker Compose](https://docs.docker.com/compose/) containers to run the [MediaWiki](https://www.mediawiki.org/) software.

Clone the repo, create and start containers:
```sh
git clone https://github.com/pastakhov/compose-mediawiki-ubuntu.git
cd compose-mediawiki-ubuntu
docker-compose up
```
Wait for the completion of the build and initialization process and access it via `http://localhost:8080` in a browser.

Enjoy with [MediaWiki](https://www.mediawiki.org/) + [VisualEditor](https://www.mediawiki.org/wiki/VisualEditor) + [Elasticsearch](https://www.mediawiki.org/wiki/Extension:CirrusSearch) + most popular extensions

# Launching MediaWiki

## Architecture of mediawiki containers

Running `sudo docker-compose up` in a checkout of this repository will start containers:

- `db` - A MySQL [container](https://hub.docker.com/r/pastakhov/mysql/), used as the database backend for MediaWiki.
- `elasticsearch` - An [Elasticsearch](https://www.elastic.co/products/elasticsearch) [container](https://www.elastic.co/guide/en/elasticsearch/reference/current/docker.html), used as the full-text search engine for MediaWiki
- `memcached` - A memory object caching system [container](https://hub.docker.com/_/memcached/), used as the cache system for MediaWiki
- `parsoid` - A [bidirectional runtime wikitext parser](https://www.mediawiki.org/wiki/Parsoid) [container](https://hub.docker.com/r/pastakhov/parsoid/), used by [VisualEditor](https://www.mediawiki.org/wiki/VisualEditor), [Flow](https://www.mediawiki.org/wiki/Flow) and other [MediaWiki extensions](https://www.mediawiki.org/wiki/Extensions)
- `proxy` - [Varnish reverse proxy server](https://www.mediawiki.org/wiki/Manual:Varnish_caching) [container](https://github.com/pastakhov/docker-mediawiki-varnish/) which reduces the time taken to serve often-requested pages
- `restbase` - A [REST storage and service dispatcher](https://www.mediawiki.org/wiki/RESTBase) [container](https://hub.docker.com/r/pastakhov/restbase/)
- `web` - An Apache/MediaWiki container with PHP 7.0 and MediaWiki 1.29

The parsoid, restbase, proxy, web containers are based on [Ubuntu](https://hub.docker.com/_/ubuntu/) 16.04

The ElasticSearch container has requirements reguarding `vm.max_map_count`, and thus you need to do the following:

``` sh
sysctl -w vm.max_map_count=262144
```

You can also permanently set this setting by putting the file `etc/sysctl.d/99-elasticsearch.conf` on the root filesystem and doing `sysctl -p` or rebooting.

## Settings

Settings are in the `docker-compose.yml` file, the *environment* sections

### db 
Was cloned from official [mysql](https://hub.docker.com/_/mysql/) container and has the same environment variables.
The reason why it is better than the official is the ability to automatically update the database when upgrading the version of mysql.
The only one important environment variable for us is `MYSQL_ROOT_PASSWORD`, it specifies the password that will be set for the MySQL `root` superuser account.
If changed, make sure that `MW_DB_INSTALLDB_PASS` in web section was changed too.

### proxy

#### environment variables

- `PROXY_BACKEND_{name}` defines backend using format 'host:port'. Generally backend name should be the same as container name. Example: `PROXY_BACKEND_web=web:80`.

More details on [Varnish service](https://github.com/pastakhov/docker-mediawiki-varnish/) page.

#### ports
The proxy container are listening for connections on private port 80.
By default the public port for connections is 8080:
```
    ports:
        - "8080:80"
```
You are welcome to change it to any you would like, just note: *make sure that `MW_SITE_SERVER` has correct value*

### parsoid

#### environment variables

- `PARSOID_DOMAIN_{domain}` defines uri and domain for the Parsoid service. The '{domain}' word should be the same as the `MW_REST_DOMAIN` parameter in the web container. You can specify any number of such variables (by the number of domains used for the service).

More details on [Parsoid service](https://github.com/pastakhov/docker-parsoid/) page.

### restbase

#### environment variables

- `RB_CONF_DOMAIN_{domain}` defines uri and domain for the RESTBase service. The '{domain}' word should be the same as the `MW_REST_DOMAIN` parameter in the web container. You can specify any number of such variables (by the number of domains used for the service). Example: `RB_CONF_DOMAIN_web=http://web/w/api.php`.
- `RB_CONF_PARSOID_HOST` defines uri to Parsoid service. Example: `http://parsoid:8000`.
- `RB_CONF_API_URI_TEMPLATE` defines uri to the MediaWiki API. Example :`http://{domain}/w/api.php`
- `RB_CONF_BASE_URI_TEMPLATE` defines base uri for the links to RESTBase service. Example: `http://{domain}/api/rest_v1`.

More details on [RESTbase service](https://github.com/pastakhov/docker-restbase/) page.

### web

#### environment variables

- `MW_SITE_SERVER` configures [$wgServer](https://www.mediawiki.org/wiki/Manual:$wgServer), set this to the server host and include the protocol like `http://my-wiki:8080` 
- `MW_SITE_NAME` configures [$wgSitename](https://www.mediawiki.org/wiki/Manual:$wgSitename)
- `MW_SITE_LANG` configures [$wgLanguageCode](https://www.mediawiki.org/wiki/Manual:$wgLanguageCode)
- `MW_DEFAULT_SKIN` configures [$wgDefaultSkin](https://www.mediawiki.org/wiki/Manual:$wgDefaultSkin)
- `MW_ENABLE_UPLOADS` configures [$wgEnableUploads](https://www.mediawiki.org/wiki/Manual:$wgEnableUploads)
- `MW_USE_INSTANT_COMMONS` configures [$wgUseInstantCommons](https://www.mediawiki.org/wiki/Manual:$wgUseInstantCommons)
- `MW_ADMIN_USER` configures default administrator username
- `MW_ADMIN_PASS` configures default administrator password
- `MW_DB_NAME` specifies database name that will be created automatically upon container startup
- `MW_DB_USER` specifies database user for access to database specified in `MW_DB_NAME`
- `MW_DB_PASS` specifies database user password
- `MW_DB_INSTALLDB_USER` specifies database superuser name for create database and user specified above
- `MW_DB_INSTALLDB_PASS` specifies database superuser password, should be the same as `MYSQL_ROOT_PASSWORD` in db section.
- `MW_PROXY_SERVERS` (comma separated values) configures [$wgSquidServers](https://www.mediawiki.org/wiki/Manual:$wgSquidServers). Leave empty if no reverse proxy server used.
- `MW_MAIN_CACHE_TYPE` configures [$wgMainCacheType](https://www.mediawiki.org/wiki/Manual:$wgMainCacheType). `MW_MEMCACHED_SERVERS` should be provided for `CACHE_MEMCACHED`.
- `MW_MEMCACHED_SERVERS` (comma separated values) configures [$wgMemCachedServers](https://www.mediawiki.org/wiki/Manual:$wgMemCachedServers).
- `MW_SEARCH_TYPE` configures [$wgSearchType](https://www.mediawiki.org/wiki/Manual:$wgSearchType). Leave empty or set `CirrusSearch` for MediaWiki using [Elasticsearch](https://www.mediawiki.org/wiki/Extension:CirrusSearch). `MW_CIRRUS_SEARCH_SERVERS` should be provided for `CirrusSearch`.
- `MW_FLOW_NAMESPACES` (comma separated values) specifies namespaces where the Flow extension should be enabled.
- `MW_REST_DOMAIN` specifies the domain parameter used for the REST services such as Parsoid and RESTBase, generally should be the same as the name of the container.
- `MW_REST_RESTBASE_PROXY_PATH` if reverse proxy are used defines path for the $wgVisualEditorFullRestbaseURL and $wgVisualEditorRestbaseURL variables. Example: `/api/rest_`.
- `MW_AUTOUPDATE` if `true` (by default) run needed maintenance scripts automatically before web server start.
- `MW_SHOW_EXCEPTION_DETAILS` if `true` (by default) configures [$wgShowExceptionDetails](https://www.mediawiki.org/wiki/Manual:$wgShowExceptionDetails) as true.
- `PHP_LOG_ERRORS` specifies `log_errors` parameter in `php.ini` file.
- `PHP_ERROR_REPORTING` specifies `error_reporting` parameter in `php.ini` file. `E_ALL` by default, on production should be changed to `E_ALL & ~E_DEPRECATED & ~E_STRICT`.

## LocalSettings.php

The [LocalSettings.php](https://www.mediawiki.org/wiki/Manual:LocalSettings.php) devided to three parts:
- LocalSettings.php will be created automatically upon container startup, contains settings specific to the MediaWiki installed instance such as database connection, [$wgSecretKey](https://www.mediawiki.org/wiki/Manual:$wgSecretKey) and etc. **Should not be changed**
- DockerSettings.php сontains settings specific to the released containers such as database server name, path to programs, installed extensions, etc. **Should be changed if you make changes to the containers only**
- CustomSettings.php - contains user defined settings such as user rights, extensions settings and etc. **You should make changes there**. 
`CustomSettings.php` placed in folder `web` And will be copied to the container during build

### Logo
The [$wgLogo](https://www.mediawiki.org/wiki/Manual:$wgLogo) variable is set to `$wgScriptPath/logo.png` value.
The `web/logo.png` file will be copied to *$wgScriptPath/logo.png* path during build.
For change the logo just replace the `web/logo.png` file by your logo file and rebuild container

### Favicon
The [$wgFavicon](https://www.mediawiki.org/wiki/Manual:$wgFavicon) variable is set to `$wgScriptPath/favicon.ico` value.
The `web/favicon.ico` file will be copied to *$wgScriptPath/favicon.ico* path during build.
For change the favicon just replace the `web/favicon.ico` file by your favicon file and rebuild container

**How do I rebuild the containers to accept changes to the settings?**
Just use the command:

```sh
docker-compose up --build
```

It picks up the changes by stopping and recreating the containers.

**Why should I rebuild the container every time I change the settings?**
In this case you are able to check on changes locally before deploy ones to your server.
This solution significantly reduces the likelihood that something will be broken on your server when you change the settings.

## First start

During the first start, the MediaWiki will be fully initialized according to the settings specified in the `docker-compose.yml` file.
This process includes:
- initialize database, create `root` user
- initialize elasticsearch storage
- initialize MediaWiki:
    - run `install.php` maintenance script that creates MediaWiki database, user and write settings to LocalSettings.php file.
    - include `web\DockerSettings.php` file to LocalSettings.php that contains minimal needed settings for installed MediaWiki extensions
    - run `update.php` maintenance script that updated MediaWiki database schema for MediaWiki extensions
    - generate elasticsearch index and bootstrap the search index
    - get the latest data for CLDR and UniversalLanguageSelector extensions
    - run `populateContentModel.php` maintenance script that populates the fields nedeed for use the Flow extension on all namespaces

## Keeping up to date

**Make a full backup of the wiki, including both the database and the files.**
While the upgrade scripts are well-maintained and robust, things could still go awry.
```sh
cd compose-mediawiki-ubuntu
docker-compose exec db /bin/bash -c 'mysqldump --all-databases -uroot -p"$MYSQL_ROOT_PASSWORD" 2>/dev/null | gzip | base64 -w 0' | base64 -d > backup_$(date +"%Y%m%d_%H%M%S").sql.gz
docker-compose exec web /bin/bash -c 'tar -c $MW_VOLUME $MW_HOME/images 2>/dev/null | base64 -w 0' | base64 -d > backup_$(date +"%Y%m%d_%H%M%S").tar
```

picking up the latest changes, stop, rebuld and start containers:
```sh
cd compose-mediawiki-ubuntu
git pull
docker-compose build
docker-compose stop
docker-compose up
```
The upgrade process is fully automated and includes the launch of all necessary maintenance scripts (only when it is really required)

## Data volumes

* `web`
    * `/var/www/html/w/images` - files uploaded by users
    * `/mediawiki` - contains info about the MediaWiki instance

# List of installed extensions

## Bundled Skins
* [Vector](https://www.mediawiki.org/wiki/Skin:Vector)
* [Modern](https://www.mediawiki.org/wiki/Skin:Modern)
* [MonoBook](https://www.mediawiki.org/wiki/Skin:MonoBook)
* [CologneBlue](https://www.mediawiki.org/wiki/Skin:CologneBlue)

## Bundled extensions
see https://www.mediawiki.org/wiki/Bundled_extensions
* [ConfirmEdit](https://www.mediawiki.org/wiki/Extension:ConfirmEdit)
* [Gadgets](https://www.mediawiki.org/wiki/Extension:Gadgets)
* [Nuke](https://www.mediawiki.org/wiki/Extension:Nuke)
* [ParserFunctions](https://www.mediawiki.org/wiki/Extension:ParserFunctions)
* [Renameuser](https://www.mediawiki.org/wiki/Extension:Renameuser)
* [WikiEditor](https://www.mediawiki.org/wiki/Extension:WikiEditor)
* [Cite](https://www.mediawiki.org/wiki/Extension:Cite)
* [ImageMap](https://www.mediawiki.org/wiki/Extension:ImageMap)
* [InputBox](https://www.mediawiki.org/wiki/Extension:InputBox)
* [Interwiki](https://www.mediawiki.org/wiki/Extension:Interwiki)
* [LocalisationUpdate](https://www.mediawiki.org/wiki/Extension:LocalisationUpdate)
* [PdfHandler](https://www.mediawiki.org/wiki/Extension:PdfHandler)
* [Poem](https://www.mediawiki.org/wiki/Extension:Poem)
* [SpamBlacklist](https://www.mediawiki.org/wiki/Extension:SpamBlacklist)
* [TitleBlacklist](https://www.mediawiki.org/wiki/Extension:TitleBlacklist)
* [CiteThisPage](https://www.mediawiki.org/wiki/Extension:CiteThisPage)
* [SyntaxHighlight GeSHi](https://www.mediawiki.org/wiki/Extension:SyntaxHighlight_GeSHi)

## Commonly used extensions
* [VisualEditor](https://www.mediawiki.org/wiki/Extension:VisualEditor)
* [CirrusSearch](https://www.mediawiki.org/wiki/Extension:CirrusSearch)
* [Echo](https://www.mediawiki.org/wiki/Extension:Echo)
* [Flow](https://www.mediawiki.org/wiki/Extension:Flow)
* [Thanks](https://www.mediawiki.org/wiki/Extension:Thanks)
* [CheckUser](https://www.mediawiki.org/wiki/Extension:CheckUser)
