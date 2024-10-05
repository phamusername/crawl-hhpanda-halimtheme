<?php
/**
 * Plugin Name: Crawl HHPANDA.TV
 * Description: Crawl + Update Dữ liệu từ HHPANDA.TV
 * Version: 1.2.0
 * Author: GGG
 * Author URI: https://t.me/gggforyou
 */
set_time_limit(0);
define('CRAWL_HHPANDA_URL', plugin_dir_url(__FILE__));
define('CRAWL_HHPANDA_PATH', plugin_dir_path(__FILE__));
define('CRAWL_HHPANDA_PATH_SCHEDULE_JSON', CRAWL_HHPANDA_PATH . 'schedule.json');
require_once CRAWL_HHPANDA_PATH . 'constant.php';

function hhpanda_crawl_tools_script()
{
	global $pagenow;
	if ('admin.php' == $pagenow && ($_GET['page'] == 'crawl-hhpanda-tools' || $_GET['page'] == 'crawl-tools')) {
		wp_enqueue_script('hhpanda_crawl_tools_js', CRAWL_HHPANDA_URL . 'assets/js/main.js?v=1.2.0.0');
		wp_enqueue_style('hhpanda_crawl_tools_css', CRAWL_HHPANDA_URL . 'assets/css/styles.css?v=1.2.0.0');
	} else {
		return;
	}
}
add_action('in_admin_header', 'hhpanda_crawl_tools_script');

// Custom metabox in post
function hhpanda_meta_box() {
	add_meta_box( 'hhpanda-custom-edit', 'hhpanda Custom Edit', 'hhpanda_custom_meta_box', 'post', 'advanced', 'high' );
}
add_action( 'add_meta_boxes', 'hhpanda_meta_box' );

function hhpanda_custom_meta_box($post, $metabox) {
	$_halim_metabox_options = get_post_meta($post->ID, '_halim_metabox_options', true);
	wp_nonce_field(basename(__FILE__), 'post_media_metabox');
?>
  <div class="inside">
    <label for="fetch_hhpanda_id">hhpanda ID: </label><input styles="width: 100%" name="fetch_hhpanda_id" type="text" id="fetch_hhpanda_id" value="<?php echo $_halim_metabox_options["fetch_hhpanda_id"];?>">
    <label for="fetch_hhpanda_update_time">Thời gian cập nhật: </label><input styles="width: 100%" name="fetch_hhpanda_update_time" type="text" id="fetch_hhpanda_update_time" value="<?php echo $_halim_metabox_options["fetch_hhpanda_update_time"];?>">
	</div>
<?php
}

function hhpanda_custom_save_metabox($post_id, $post)
{
  if (!wp_verify_nonce($_POST["post_media_metabox"], basename(__FILE__))) {
		return $post_id;
	}
	if (defined("DOING_AUTOSAVE") && DOING_AUTOSAVE) {
		return $post_id;
	}
	if ('post' != $post->post_type) {
		return $post_id;
	}
  $fetch_hhpanda_id = (isset($_POST["fetch_hhpanda_id"])) ? sanitize_text_field($_POST["fetch_hhpanda_id"]) : '';
  $fetch_hhpanda_update_time = (isset($_POST["fetch_hhpanda_update_time"])) ? sanitize_text_field($_POST["fetch_hhpanda_update_time"]) : '';

	$_halim_metabox_options = get_post_meta($post_id, '_halim_metabox_options', true);
	$_halim_metabox_options["fetch_hhpanda_id"] = $fetch_hhpanda_id;
	$_halim_metabox_options["fetch_hhpanda_update_time"] = $fetch_hhpanda_update_time;

	update_post_meta($post_id, '_halim_metabox_options', $_halim_metabox_options);
}
add_action('save_post', 'hhpanda_custom_save_metabox', 20, 2);

include_once CRAWL_HHPANDA_PATH . 'functions.php';
include_once CRAWL_HHPANDA_PATH . 'crawl_movies.php';
