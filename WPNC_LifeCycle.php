<?php
/*
    "WordPress Plugin Template" Copyright (C) 2014 Michael Simpson  (email : michael.d.simpson@gmail.com)

    This file is part of WordPress Plugin Template for WordPress.

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

include_once('WPNC_InstallIndicator.php');

class WPNC_LifeCycle extends WPNC_InstallIndicator {

    public function install() {

        // Initialize Plugin Options
        $this->initOptions();

        // Initialize DB Tables used by the plugin
        // $this->installDatabaseTables();

        // Other Plugin initialization - for the plugin writer to override as needed
        $this->otherInstall();

        // Record the installed version
        $this->saveInstalledVersion();

        // To avoid running install() more then once
        $this->markAsInstalled();
    }

    public function uninstall() {
        $this->otherUninstall();
        $this->unInstallDatabaseTables();
        $this->deleteSavedOptions();
        $this->markAsUnInstalled();
    }

    /**
     * Perform any version-upgrade activities prior to activation (e.g. database changes)
     * @return void
     */
    public function upgrade() {
    }

    /**
     * See: http://plugin.michael-simpson.com/?page_id=105
     * @return void
     */
    public function activate() {
		// Initialize DB Tables used by the plugin
		$this->installDatabaseTables();

	}

    /**
     * See: http://plugin.michael-simpson.com/?page_id=105
     * @return void
     */
    public function deactivate() {
    }

    /**
     * See: http://plugin.michael-simpson.com/?page_id=31
     * @return void
     */
    protected function initOptions() {
    }

    public function addActionsAndFilters() {
		add_filter('xmlrpc_methods', array(&$this, 'addWPNCReceivePing'));
    }

	public function addWPNCReceivePing($methods) {
		return array_merge($methods, array('wp.receiveWPNCPing' => array(&$this, 'processWPNCReceivePing')));	
	}

	public function processWPNCReceivePing() {
		$args = func_get_args();
		foreach ($args as $k => $v){
			logIO("I", sprintf("%s => %s", $k, var_export($v, true)));
		}
		return true;
	}

    /**
     * See: http://plugin.michael-simpson.com/?page_id=101
     * Called by install() to create any database tables if needed.
     * Best Practice:
     * (1) Prefix all table names with $wpdb->prefix
     * (2) make table names lower case only
     * @return void
     */
    protected function installDatabaseTables() {
		global $wpdb;
		$table_prefix = WPNC_PREFIX;

		$sql1 =<<<EOF
CREATE TABLE IF NOT EXISTS `{$table_prefix}notifications_in` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `website_id` int(11) DEFAULT NULL,
  `wp_postid` bigint(20) unsigned DEFAULT NULL,
  `wp_post_title` text,
  `wp_post_content` longtext,
  `wp_tags` text,
  `wp_eyechatch_path_org` varchar(1024) DEFAULT NULL,
  `wp_eyechatch_path` varchar(1024) DEFAULT NULL,
  `post_date` datetime DEFAULT NULL,
  `post_status` varchar(20) DEFAULT NULL,
  `notification_status` varchar(20) DEFAULT NULL,
  `description` varchar(1024) DEFAULT NULL,
  `create_date` datetime DEFAULT NULL,
  `modify_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8
EOF;

		$sql2 =<<<EOF
CREATE TABLE IF NOT EXISTS `{$table_prefix}notifications_out` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `website_id` int(11) DEFAULT NULL,
  `wp_postid` bigint(20) unsigned DEFAULT NULL,
  `wp_post_title` text,
  `wp_post_content` longtext,
  `wp_tags` text,
  `wp_eyechatch_path_org` varchar(1024) DEFAULT NULL,
  `wp_eyechatch_path` varchar(1024) DEFAULT NULL,
  `post_date` datetime DEFAULT NULL,
  `post_status` varchar(20) DEFAULT NULL,
  `notification_status` varchar(20) DEFAULT NULL,
  `description` varchar(1024) DEFAULT NULL,
  `create_date` datetime DEFAULT NULL,
  `modify_date` datetime DEFAULT NULL,
  `destinations` text,
  `destinations_values` text,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8
EOF;

		$wpdb->query($sql1);
		$wpdb->query($sql2);
		error_log("plugin install tables done.");

    }

    /**
     * See: http://plugin.michael-simpson.com/?page_id=101
     * Drop plugin-created tables on uninstall.
     * @return void
     */
    protected function unInstallDatabaseTables() {
    }

    /**
     * Override to add any additional actions to be done at install time
     * See: http://plugin.michael-simpson.com/?page_id=33
     * @return void
     */
    protected function otherInstall() {
    }

    /**
     * Override to add any additional actions to be done at uninstall time
     * See: http://plugin.michael-simpson.com/?page_id=33
     * @return void
     */
    protected function otherUninstall() {
    }

    /**
     * Puts the configuration page in the Plugins menu by default.
     * Override to put it elsewhere or create a set of submenus
     * Override with an empty implementation if you don't want a configuration page
     * @return void
     */
    public function addSettingsSubMenuPage() {
        $this->addSettingsSubMenuPageToPluginsMenu();
        $this->addNotificationsOutToSubMenuPage();
		$this->addNotificationsInToSubMenuPage();
        // $this->addNotificationsSendListToPluginsMenu();
		
        //$this->addSettingsSubMenuPageToSettingsMenu();
    }


    protected function requireExtraPluginFiles() {
		// TODO: USE WP_CONTENT_DIR
		/*
		 	$basedir = str_replace($_SERVER['DOCUMENT_ROOT'], '', preg_replace('(\/wp$)', '', dirname(__FILE__)));
			define('WP_CONTENT_DIR', $_SERVER['DOCUMENT_ROOT'] . $basedir . '/assets');
			define('WP_CONTENT_URL', 'http://'.$_SERVER["HTTP_HOST"]. $basedir .'/assets');
		 */
        require_once(ABSPATH . 'wp-includes/pluggable.php');
        require_once(ABSPATH . 'wp-admin/includes/plugin.php');
		if(!class_exists('WP_List_Table')){
			require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
		}
		if(!class_exists('Notifications_Out_List')){
			// require_once( ABSPATH . 'wp-content/plugins/wp-notification-center/list-table-out.php' );
			require_once( WP_CONTENT_DIR . '/plugins/wp-notification-center/list-table-out.php' );
		}

		if(!class_exists('Notifications_In_List')){
			// require_once( ABSPATH . 'wp-content/plugins/wp-notification-center/list-table-in.php' );
			require_once( WP_CONTENT_DIR .  '/plugins/wp-notification-center/list-table-in.php' );
		}


	}

    /**
     * @return string Slug name for the URL to the Setting page
     * (i.e. the page for setting options)
     */
    protected function getSettingsSlug() {
        return get_class($this) . 'Settings';
    }

    protected function getNotificationsInSlug() {
        return get_class($this) . 'NotifcationsIn';
    }

    protected function getNotificationsOutSlug() {
        return get_class($this) . 'NotifcationsOut';
    }

    protected function addSettingsSubMenuPageToPluginsMenu() {
        $this->requireExtraPluginFiles();
        $displayName = $this->getPluginDisplayName();
        add_submenu_page('plugins.php',
                         $displayName,
                         $displayName,
                         'manage_options',
                         $this->getSettingsSlug(),
                         array(&$this, 'settingsPage'));
    }


    protected function addSettingsSubMenuPageToSettingsMenu() {
        $this->requireExtraPluginFiles();
        $displayName = $this->getPluginDisplayName();
        add_options_page($displayName,
                         $displayName,
                         'manage_options',
                         $this->getSettingsSlug(),
                         array(&$this, 'settingsPage'));
    }

    protected function addNotificationsOutToSubMenuPage() {
        $this->requireExtraPluginFiles();
		$displayName = "通知(OUT)";
        add_submenu_page('plugins.php',
                         $displayName,
                         $displayName,
                         'manage_options',
                         $this->getNotificationsOutSlug(),
                         array(&$this, 'notificationsOutPage'));
    }

	protected function addNotificationsInToSubMenuPage() {
		$this->requireExtraPluginFiles();
		$displayName = "通知(IN)";
		add_submenu_page('plugins.php',
						$displayName,
						$displayName,
						'manage_options',
						$this->getNotificationsInSlug(),
						array(&$this, 'notificationsInPage'));
	}


	protected function addNotificationsReceiveListToSettingsMenu() {
        $this->requireExtraPluginFiles();
        $displayName = $this->getPluginDisplayName();
        add_options_page($displayName,
                         $displayName,
                         'manage_options',
                         $this->getSettingsSlug(),
                         array(&$this, 'settingsPage'));
    }

    /**
     * @param  $name string name of a database table
     * @return string input prefixed with the WordPress DB table prefix
     * plus the prefix for this plugin (lower-cased) to avoid table name collisions.
     * The plugin prefix is lower-cases as a best practice that all DB table names are lower case to
     * avoid issues on some platforms
     */
    protected function prefixTableName($name) {
        global $wpdb;
        return $wpdb->prefix .  strtolower($this->prefix($name));
    }


    /**
     * Convenience function for creating AJAX URLs.
     *
     * @param $actionName string the name of the ajax action registered in a call like
     * add_action('wp_ajax_actionName', array(&$this, 'functionName'));
     *     and/or
     * add_action('wp_ajax_nopriv_actionName', array(&$this, 'functionName'));
     *
     * If have an additional parameters to add to the Ajax call, e.g. an "id" parameter,
     * you could call this function and append to the returned string like:
     *    $url = $this->getAjaxUrl('myaction&id=') . urlencode($id);
     * or more complex:
     *    $url = sprintf($this->getAjaxUrl('myaction&id=%s&var2=%s&var3=%s'), urlencode($id), urlencode($var2), urlencode($var3));
     *
     * @return string URL that can be used in a web page to make an Ajax call to $this->functionName
     */
    public function getAjaxUrl($actionName) {
        return admin_url('admin-ajax.php') . '?action=' . $actionName;
    }

}
