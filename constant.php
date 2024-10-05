<?php

define('HHPANDA_API_DOMAIN', 'https://phimapi.com');
define('CRAWL_HHPANDA_OPTION_SETTINGS', 'crawl_hhpanda_schedule_settings');
define('CRAWL_HHPANDA_OPTION_RUNNING', 'crawl_hhpanda_schedule_running');
define('CRAWL_HHPANDA_OPTION_SECRET_KEY', 'crawl_hhpanda_schedule_secret_key');

define('HHPANDA_SCHEDULE_CRAWLER_TYPE_NOTHING', 0);
define('HHPANDA_SCHEDULE_CRAWLER_TYPE_INSERT', 1);
define('HHPANDA_SCHEDULE_CRAWLER_TYPE_UPDATE', 2);
define('HHPANDA_SCHEDULE_CRAWLER_TYPE_ERROR', 3);
define('HHPANDA_SCHEDULE_CRAWLER_TYPE_FILTER', 4);