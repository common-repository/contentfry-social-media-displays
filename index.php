<?php
/**
 * Plugin Name: Contentfry Social Media Displays
 * Plugin URI:
 * Description: Embed contentfry displays into your WordPress posts with just one click.
 * Version: 1.0.5
 * Author: Contentfry
 * Author URI: https://contentfry.com
 */

defined( 'ABSPATH' ) || exit;

require_once plugin_dir_path( __FILE__ ) . 'core/ContentfryGutenberg.php';
require_once plugin_dir_path( __FILE__ ) . 'core/ContentfryBlock.php';

new ContentfryGutenberg();
new ContentfryBlock();
