<?php
	/**
	 * wp-cache v1.0.0 (2019-02-10)
	 * Copyright 2019 Oliver Findl
	 * @license MIT
	 */

	define("WP_INDEX_PATH", __DIR__ . "/index.php"); // path to index.php file
	define("WP_LOAD_PATH", __DIR__ . "/wp-load.php"); // path to wp-load.php file

	define("QUOTA_ENABLE", false); // enable quota, format: true|false
	define("QUOTA_PERIOD", 60); // value in seconds, format: integer
	define("QUOTA_LIMIT", 60); // number of requests per QUOTA_PERIOD, format: integer

	define("CACHE_ENABLE", true); // enable cache, format: true|false
	define("CACHE_PERIOD", 5 * 60); // value in seconds, format: integer
	define("CACHE_SERVERS", [ // array of memcached server configs, format: [ [ host, port ], ... ]
		["127.0.0.1", 11211],
//		["mc0.example.com", 11211],
//		["mc1.example.com", 11211],
//		...
	]);

	/* DO NOT MODIFY ANY CONTENT BELOW */

	define("SCRIPT_START_TIME", microtime(true));

	$_SERVER = array_map("trim", $_SERVER);
	define("SERVER", $_SERVER["SERVER_NAME"]);
	define("PROTOCOL", $_SERVER["SERVER_PROTOCOL"]);
	define("METHOD", $_SERVER["REQUEST_METHOD"]);
	define("URL", filter_var("http" . (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] === "on" ? "s" : "") . "://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"], FILTER_SANITIZE_URL));
	define("IP", $_SERVER["REMOTE_ADDR"]);

	define("MD5_URL", md5(URL));
	define("MD5_IP", md5(IP));

	if(strtoupper(METHOD) !== "GET" || empty(ENABLE_QUOTA) && empty(ENABLE_CACHE)) {
		require_once(WP_INDEX_PATH);
		exit(0);
	}

	require_once(WP_LOAD_PATH);

	if(is_user_logged_in()) {
		require_once(WP_INDEX_PATH);
		exit(0);
	}

	$cache = new Memcached("wp-cache");
	$cache->setOptions([
		Memcached::OPT_DISTRIBUTION => Memcached::DISTRIBUTION_CONSISTENT,
		Memcached::OPT_NO_BLOCK => true,
		Memcached::OPT_BINARY_PROTOCOL => true,
		Memcached::OPT_PREFIX_KEY => implode(":", ["wp-cache", SERVER])
	]);
	if(empty($cache->getServerList())) {
		$cache->addServers(CACHE_SERVERS);
	}

	if(!empty(QUOTA_ENABLE)) {
		$quota = intval($cache->get(MD5_IP));
		if($quota >= QUOTA_LIMIT) {
			header(PROTOCOL . " 429 Too Many Requests");
			header("Retry-After: " . QUOTA_PERIOD);
			print("[ERROR] Quota limit HIT. Try again after " . QUOTA_PERIOD . " seconds.\n");
			exit(1);
		}
		$cache->set(MD5_IP, $quota + 1, QUOTA_PERIOD);
	}

	if(!filter_var(URL, FILTER_VALIDATE_URL)) {
		header(PROTOCOL . " 400 Bad Request");
		print("[ERROR] Invalid URL!\n");
		exit(1);
	}

	if(!empty(CACHE_ENABLE) && !empty(MD5_URL)) {
		$response = @$cache->get(MD5_URL);
		$response = @unserialize($response);
	}

	define("CACHE_STATUS", !empty($response) ? "HIT" : "MISS");

	if(empty($response)) {
		ob_start();
		require_once(WP_INDEX_PATH);
		$response = [
			"headers" => array_map("trim", headers_list()),
			"body" => trim(ob_get_contents())
		];
		ob_end_clean();

		if(!empty(CACHE_ENABLE) && !empty(MD5_URL) && !empty($response)) {
			$cache->set(MD5_URL, @serialize($response), CACHE_PERIOD);
		}
	}

	while(!empty($response["headers"])) {
		header(array_shift($response["headers"]));
	}

	header("X-Cache-Status: " . CACHE_STATUS);

	print($response["body"]);

	define("SCRIPT_END_TIME", microtime(true));

	print("\n<!-- wp-cache: page generated in " . round(SCRIPT_END_TIME - SCRIPT_START_TIME, 4) . " seconds -->\n");
	exit(0);
?>
