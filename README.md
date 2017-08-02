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

- `db` - A MySQL container, used as the database backend for MediaWiki.
- `elasticsearch` - An Elasticsearch container, used as the full-text search engine for MediaWiki
- `memcache` - A memory object caching system container, used as the cache system for MediaWiki
- `parsoid` - A bidirectional runtime wikitext parser, used by VisualEditor, Flow and other MediaWiki extensions
- `proxy` - A reverse proxy server [Varnish](https://www.mediawiki.org/wiki/Manual:Varnish_caching) which reduces the time taken to serve often-requested pages
- `restbase` - A caching / storing API proxy backing the REST API
- `web` - An Apache/MediaWiki container with PHP 7.0 and MediaWiki 1.28

All containers are based on [Ubuntu](https://hub.docker.com/_/ubuntu/) 16.04

## Settings

Settings are in the `docker-compose.yml` file, the *environment* sections

### db 
Was cloned from official [mysql](https://hub.docker.com/_/mysql/) container and has the same environment variables.
The reason why it is better than the official is the ability to automatically update the database when upgrading the version of mysql.
The only one important environment variable for us is `MYSQL_ROOT_PASSWORD`, it specifies the password that will be set for the MySQL `root` superuser account.
If changed, make sure that `MW_DB_INSTALLDB_PASS` in web section was changed too.

### proxy

#### ports
The proxy container are listening for connections on private port 80.
By default the public port for connections is 8080:
```
    ports:
        - "8080:80"
```
You are welcome to change it to any you would like, just note: *make sure that `MW_SITE_SERVER` has correct value*

### parsoid

#### environment

- `NUM_WORKERS` defines the number of worker processes to the parsoid service. Set to `0` to run everything in a single process without clustering. Use `ncpu` to run as many workers as there are CPU units.

### restbase

#### environment

- `NUM_WORKERS` defines the number of worker processes to the restbase service. Set to `0` to run everything in a single process without clustering. Use `ncpu` to run as many workers as there are CPU units.

### web

#### environment

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
- `MW_PROXY_SERVERS` (comma separated values) configures [$wgSquidServers](https://www.mediawiki.org/wiki/Manual:$wgSquidServers). Set empty if no reverse proxy server used.
- `PHP_LOG_ERRORS` specifies `log_errors` parameter in `php.ini` file.
- `PHP_ERROR_REPORTING` specifies `error_reporting` parameter in `php.ini` file. `E_ALL` by default, on production should be changed to `E_ALL & ~E_DEPRECATED & ~E_STRICT`.

## LocalSettings.php

The [LocalSettings.php](https://www.mediawiki.org/wiki/Manual:LocalSettings.php) devided to three parts:
- LocalSettings.php will be created automatically upon container startup, contains settings specific to the MediaWiki installed instance such as database connection, [$wgSecretKey](https://www.mediawiki.org/wiki/Manual:$wgSecretKey) and etc. **Should not be changed**
- DockerSettings.php сontains settings specific to the released containers such as database server name, path to programs, installed extensions, etc. **Should be changed if you make changes in containers only**
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

* `db`
    * `/var/lib/mysql` - database files
* `elasticsearch`
    * `/elasticsearch` - data and log files
* `restbase`
    * `/data` - sqlite files
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
