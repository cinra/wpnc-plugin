<?php
/*
   Plugin Name: wp-notification-center
   Plugin URI: http://wordpress.org/extend/plugins/wp-notification-center/
   Version: 0.99
   Author: Cinra, Co., Ltd.
   Description: Syndicate multple WP sites
   Text Domain: wp-notification-center
   License: GPLv3
*/

/*
    "WordPress Plugin Template" Copyright (C) 2014 Michael Simpson  (email : michael.d.simpson@gmail.com)

    This following part of this file is part of WordPress Plugin Template for WordPress.

    WordPress Plugin Template is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    WordPress Plugin Template is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Contact Form to Database Extension.
    If not, see http://www.gnu.org/licenses/gpl-3.0.html

	Portions 2014 Cinra, Co., Ltd.
*/

$WPNC_minimalRequiredPhpVersion = '5.0';

/**
 * Check the PHP version and give a useful error message if the user's version is less than the required version
 * @return boolean true if version check passed. If false, triggers an error which WP will handle, by displaying
 * an error message on the Admin page
 */
function WPNC_noticePhpVersionWrong()
{
    global $WPNC_minimalRequiredPhpVersion;
    echo '<div class="updated fade">' .
      __('Error: plugin "wp-notification-center" requires a newer version of PHP to be running.',  'wp-notification-center').
            '<br/>' . __('Minimal version of PHP required: ', 'wp-notification-center') . '<strong>' . $WPNC_minimalRequiredPhpVersion . '</strong>' .
            '<br/>' . __('Your server\'s PHP version: ', 'wp-notification-center') . '<strong>' . phpversion() . '</strong>' .
         '</div>';
}


function WPNC_PhpVersionCheck()
{
    global $WPNC_minimalRequiredPhpVersion;
    if (version_compare(phpversion(), $WPNC_minimalRequiredPhpVersion) < 0) {
        add_action('admin_notices', 'WPNC_noticePhpVersionWrong');
        return false;
    }
    return true;
}


/**
 * Initialize internationalization (i18n) for this plugin.
 * References:
 *      http://codex.wordpress.org/I18n_for_WordPress_Developers
 *      http://www.wdmac.com/how-to-create-a-po-language-translation#more-631
 * @return void
 */
function WPNC_i18n_init()
{
    $pluginDir = dirname(plugin_basename(__FILE__));
    load_plugin_textdomain('wp-notification-center', false, $pluginDir . '/languages/');
}

// WPNCプラグインを導入するためにはPREFIXの設定が必要（2014-05-18)
function WPNC_noticePrefixIsNotSet() {
	echo '<div class="updated fade">' .
		__('Error: plugin "wp-notification-center" requires WPNC_PREFIX constant is set properly.',  'wp-notification-center').
		'</div>';
}


function WPNC_check_prefix() {
	global $WPNC_minimalRequiredPhpVersion;
	if (!defined('WPNC_PREFIX')) {
		add_action('admin_notices', 'WPNC_noticePrefixIsNotSet');
		return false;
	}
	return true;
}


//////////////////////////////////
// Run initialization
/////////////////////////////////

// First initialize i18n
WPNC_i18n_init();


// Next, run the version check.
// If it is successful, continue with initialization for this plugin
if (WPNC_PhpVersionCheck() && WPNC_check_prefix()) {
    // Only load and run the init function if we know PHP version can parse it
    include_once('wp-notification-center_init.php');
    WPNC_init(__FILE__);
}


