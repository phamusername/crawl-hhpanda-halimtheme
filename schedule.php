<?php
require_once __DIR__ . '/../../../wp-load.php';
require_once __DIR__ . '/../../../wp-admin/includes/taxonomy.php';
require_once __DIR__ . '/../../../wp-admin/includes/image.php';

set_time_limit(0);
define('CRAWL_HHPANDA_PATH', plugin_dir_path(__FILE__));
define('CRAWL_HHPANDA_PATH', CRAWL_HHPANDA_PATH . 'schedule.json');
require_once CRAWL_HHPANDA_PATH . 'constant.php';

if(!isset($argv[1])) return;
if($argv[1] != get_option(CRAWL_HHPANDA_OPTION_SECRET_KEY, 'secret_key')) return;

require_once CRAWL_HHPANDA_PATH . 'functions.php';
require_once CRAWL_HHPANDA_PATH . 'crawl_movies.php';

// Get & Check Settings
$crawl_hhpanda_settings = json_decode(get_option(CRAWL_HHPANDA_OPTION_SETTINGS, false));
if(!$crawl_hhpanda_settings) return;

// Check enable
if(hhpanda_getEnable() === false) {
	update_option(CRAWL_HHPANDA_OPTION_RUNNING, 0);
	return;
}
// Check running
if((int) get_option(CRAWL_HHPANDA_OPTION_RUNNING, 0) === 1) return;

// Update Running
update_option(CRAWL_HHPANDA_OPTION_RUNNING, 1);

try {
	// Crawl Pages
	$pageFrom = $crawl_hhpanda_settings->pageFrom;
	$pageTo = $crawl_hhpanda_settings->pageTo;
	$listMovies = array();
	for ($i=$pageFrom; $i >= $pageTo; $i--) {
		if(hhpanda_getEnable() === false) {
			update_option(CRAWL_HHPANDA_OPTION_RUNNING, 0);
			return;
		}
		$result = crawl_hhpanda_page_handle(API_DOMAIN . "/danh-sach/phim-moi-cap-nhat?page=$i");
		$result = explode("\n", $result);
		$listMovies = array_merge($listMovies, $result);
	}
	shuffle($listMovies);

	$countMovies = count($listMovies);
	$countDone = 0;
	$countStatus = array(0,0,0,0,0);

	hhpanda_write_log("Start crawler {$countMovies} movies");
	// Crawl Movies
	foreach ($listMovies as $key => $data_post) {
		if(hhpanda_getEnable() === false) {
			update_option(CRAWL_HHPANDA_OPTION_RUNNING, 0);
			hhpanda_write_log("Force Stop => Done {$countDone}/{$countMovies} movies (Nothing Update: {$countStatus[0]} | Insert: {$countStatus[1]} | Update: {$countStatus[2]} | Error: {$countStatus[3]} | Filter: {$countStatus[4]})");
			return;
		}

		$url 								= explode('|', $data_post)[0];
		$hhpanda_id 					= explode('|', $data_post)[1];
		$hhpanda_update_time 	= explode('|', $data_post)[2];

		$result = crawl_hhpanda_movies_handle($url, $hhpanda_id, $hhpanda_update_time, $crawl_hhpanda_settings->filterType, $crawl_hhpanda_settings->filterCategory, $crawl_hhpanda_settings->filterCountry);
		$result = json_decode($result);
		if ($result->schedule_code == HHPANDA_SCHEDULE_CRAWLER_TYPE_ERROR) hhpanda_write_log(sprintf("ERROR: %s ==>>> %s", $url, $result->msg));
		$countStatus[$result->schedule_code]++;
		$countDone++;
	}

} catch (\Throwable $th) {
	hhpanda_write_log(sprintf("ERROR: THROW ==>>> %s", $th->getMessage()));
}

// Update Running
update_option(CRAWL_HHPANDA_OPTION_RUNNING, 0);

hhpanda_write_log("Done {$countDone}/{$countMovies} movies (Nothing Update: {$countStatus[0]} | Insert: {$countStatus[1]} | Update: {$countStatus[2]} | Error: {$countStatus[3]} | Filter: {$countStatus[4]})");

function hhpanda_getEnable()
{
	$schedule = json_decode(file_get_contents(CRAWL_HHPANDA_PATH_SCHEDULE_JSON));
	if ($schedule->enable) {
		return $schedule->enable;
	}
	return false;
}

function hhpanda_write_log($log_msg, $new_line = "\n") {
	$log_filename = __DIR__ . '/../../crawl_hhpanda_logs';
	if (!file_exists($log_filename))
	{
		mkdir($log_filename, 0777, true);
	}
	$log_file_data = $log_filename.'/log_' . date('d-m-Y') . '.log';
	file_put_contents($log_file_data, '['. date("d-m-Y H:i:s") .'] ' . $log_msg . $new_line, FILE_APPEND);
}
