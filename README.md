# wp-cache

![license](https://img.shields.io/github/license/oliverfindl/wp-cache.svg?style=flat)
[![paypal](https://img.shields.io/badge/donate-paypal-blue.svg?colorB=0070ba&style=flat)](https://paypal.me/oliverfindl)

Simple cache script for [WordPress][WP] CMS based on [Memcached][MC]. Script also includes simple quota mechanism.

> This script is proof of concept. Never was used in production.

---

## Usage

If you completed the [installation](#install) and [setup](#setup) process, you should see new HTTP header called X-Cache-Status for each request telling you, if script is serving cached version or not. Additionally you get generation time displayed in comment at end of document. Cache is automatically turned off for logged users.

## Requirements

* [PHP 7][PHP-7]
* [PHP Memcached extension][PHP-MC-EXT]
* [Memcached][MC]
* [WordPress][WP]

## Install

```bash
# change directory to wp root
$ cd /path/to/your/wp-root

# clone this repo
$ git clone https://github.com/oliverfindl/wp-cache.git wp-cache-temp

# [optional] backup original .htaccess file if available
$ mv .htaccess .htaccess.bak

# copy wp-cache files from repo to wp root
$ cp wp-cache-temp/src/{.htaccess,wp-cache.php} .

# delete repo
$ rm -r wp-cache-temp
```

## Setup

```bash
# update rewrite base to wp root
$ vim .htaccess

# set preferred options in wp-cache.php file
$ vim wp-cache.php
```

## Options

```php
define("WP_INDEX_PATH", __DIR__ . "/index.php"); // path to index.php file
define("WP_LOAD_PATH", __DIR__ . "/wp-load.php"); // path to wp-load.php file

define("QUOTA_ENABLE", false); // enable quota, format: true|false
define("QUOTA_PERIOD", 60); // value in seconds, format: integer
define("QUOTA_LIMIT", 60); // number of requests per QUOTA_PERIOD, format: integer

define("CACHE_ENABLE", true); // enable cache, format: true|false
define("CACHE_PERIOD", 5 * 60); // value in seconds, format: integer
define("CACHE_SERVERS", [ // array of memcached server configs, format: [ [ host, port ], ... ]
	["127.0.0.1", 11211],
//	["mc0.example.com", 11211],
//	["mc1.example.com", 11211],
//	...
]);
```

## Uninstall

```bash
# change directory to wp root
$ cd /path/to/your/wp-root

# remove wp-cache files
$ rm {.htaccess,wp-cache.php}

# [optional] restore .htaccess file from backup if available
$ mv .htaccess.bak .htaccess
```

---

## License

[MIT](http://opensource.org/licenses/MIT)

[WP]: https://wordpress.org/
[MC]: https://www.memcached.org/
[PHP-7]: https://secure.php.net/manual/en/install.php
[PHP-MC-EXT]: https://secure.php.net/manual/en/book.memcached.php
