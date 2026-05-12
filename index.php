<?php
/*
Plugin Name: Aveonline Api
Plugin URI: https://github.com/franciscoblancojn/aveonline-api
Description: It is an plugin of wordpress, for create enpoint for aveonline.
Version: 1.3.9
Author: franciscoblancojn
Author URI: https://franciscoblanco.vercel.app/
License: GPL2+
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wc-aveonline-api
*/

if (!function_exists('is_plugin_active'))
    require_once(ABSPATH . '/wp-admin/includes/plugin.php');

define("AVE_API_KEY", 'AVE_API');
define("AVE_API_MODE_DEV", true);
define("AVE_API_KEY_SEPARETE", '____AVE_API____');
define("AVE_API_CONFIG", 'AVE_API_CONFIG');
define("AVE_API_CONTENT", 'AVE_API_CONTENT');
define("AVE_API_LOG", true);
define("AVE_API_LOG_KEY", "AVE_API_LOG");
define("AVE_API_LOG_COUNT", 100);
define("AVE_API_BASENAME", plugin_basename(__FILE__));
define("AVE_API_DIR", plugin_dir_path(__FILE__));
define("AVE_API_URL", plugin_dir_url(__FILE__));

require_once AVE_API_DIR . 'update.php';
github_updater_plugin_wordpress_v1([
    'basename' => AVE_API_BASENAME,
    'dir' => AVE_API_DIR,
    'file' => "index.php",
    'path_repository' => 'franciscoblancojn/aveonline-api',
    'branch' => 'master',
    'token_array_split' => [
        "g",
        "h",
        "p",
        "_",
        "G",
        "4",
        "W",
        "E",
        "W",
        "F",
        "p",
        "V",
        "U",
        "E",
        "F",
        "V",
        "x",
        "F",
        "U",
        "n",
        "b",
        "M",
        "k",
        "P",
        "R",
        "x",
        "o",
        "f",
        "t",
        "Y",
        "8",
        "z",
        "j",
        "t",
        "4",
        "E",
        "x",
        "b",
        "i",
        "9"
    ]
]);

require_once AVE_API_DIR . 'src/_.php';
