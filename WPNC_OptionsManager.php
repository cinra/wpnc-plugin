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
*/

class WPNC_OptionsManager {

    public function getOptionNamePrefix() {
        return get_class($this) . '_';
    }


    /**
     * Define your options meta data here as an array, where each element in the array
     * @return array of key=>display-name and/or key=>array(display-name, choice1, choice2, ...)
     * key: an option name for the key (this name will be given a prefix when stored in
     * the database to ensure it does not conflict with other plugin options)
     * value: can be one of two things:
     *   (1) string display name for displaying the name of the option to the user on a web page
     *   (2) array where the first element is a display name (as above) and the rest of
     *       the elements are choices of values that the user can select
     * e.g.
     * array(
     *   'item' => 'Item:',             // key => display-name
     *   'rating' => array(             // key => array ( display-name, choice1, choice2, ...)
     *       'CanDoOperationX' => array('Can do Operation X', 'Administrator', 'Editor', 'Author', 'Contributor', 'Subscriber'),
     *       'Rating:', 'Excellent', 'Good', 'Fair', 'Poor')
     */
    public function getOptionMetaData() {
        return array();
    }

    /**
     * @return array of string name of options
     */
    public function getOptionNames() {
        return array_keys($this->getOptionMetaData());
    }

    /**
     * Override this method to initialize options to default values and save to the database with add_option
     * @return void
     */
    protected function initOptions() {
    }

    /**
     * Cleanup: remove all options from the DB
     * @return void
     */
    protected function deleteSavedOptions() {
        $optionMetaData = $this->getOptionMetaData();
        if (is_array($optionMetaData)) {
            foreach ($optionMetaData as $aOptionKey => $aOptionMeta) {
                $prefixedOptionName = $this->prefix($aOptionKey); // how it is stored in DB
                delete_option($prefixedOptionName);
            }
        }
    }

    /**
     * @return string display name of the plugin to show as a name/title in HTML.
     * Just returns the class name. Override this method to return something more readable
     */
    public function getPluginDisplayName() {
        return get_class($this);
    }

    /**
     * Get the prefixed version input $name suitable for storing in WP options
     * Idempotent: if $optionName is already prefixed, it is not prefixed again, it is returned without change
     * @param  $name string option name to prefix. Defined in settings.php and set as keys of $this->optionMetaData
     * @return string
     */
    public function prefix($name) {
        $optionNamePrefix = $this->getOptionNamePrefix();
        if (strpos($name, $optionNamePrefix) === 0) { // 0 but not false
            return $name; // already prefixed
        }
        return $optionNamePrefix . $name;
    }

    /**
     * Remove the prefix from the input $name.
     * Idempotent: If no prefix found, just returns what was input.
     * @param  $name string
     * @return string $optionName without the prefix.
     */
    public function &unPrefix($name) {
        $optionNamePrefix = $this->getOptionNamePrefix();
        if (strpos($name, $optionNamePrefix) === 0) {
            return substr($name, strlen($optionNamePrefix));
        }
        return $name;
    }

    /**
     * A wrapper function delegating to WP get_option() but it prefixes the input $optionName
     * to enforce "scoping" the options in the WP options table thereby avoiding name conflicts
     * @param $optionName string defined in settings.php and set as keys of $this->optionMetaData
     * @param $default string default value to return if the option is not set
     * @return string the value from delegated call to get_option(), or optional default value
     * if option is not set.
     */
    public function getOption($optionName, $default = null) {
        $prefixedOptionName = $this->prefix($optionName); // how it is stored in DB
        $retVal = get_option($prefixedOptionName);
        if (!$retVal && $default) {
            $retVal = $default;
        }
        return $retVal;
    }

    /**
     * A wrapper function delegating to WP delete_option() but it prefixes the input $optionName
     * to enforce "scoping" the options in the WP options table thereby avoiding name conflicts
     * @param  $optionName string defined in settings.php and set as keys of $this->optionMetaData
     * @return bool from delegated call to delete_option()
     */
    public function deleteOption($optionName) {
        $prefixedOptionName = $this->prefix($optionName); // how it is stored in DB
        return delete_option($prefixedOptionName);
    }

    /**
     * A wrapper function delegating to WP add_option() but it prefixes the input $optionName
     * to enforce "scoping" the options in the WP options table thereby avoiding name conflicts
     * @param  $optionName string defined in settings.php and set as keys of $this->optionMetaData
     * @param  $value mixed the new value
     * @return null from delegated call to delete_option()
     */
    public function addOption($optionName, $value) {
        $prefixedOptionName = $this->prefix($optionName); // how it is stored in DB
        return add_option($prefixedOptionName, $value);
    }

    /**
     * A wrapper function delegating to WP add_option() but it prefixes the input $optionName
     * to enforce "scoping" the options in the WP options table thereby avoiding name conflicts
     * @param  $optionName string defined in settings.php and set as keys of $this->optionMetaData
     * @param  $value mixed the new value
     * @return null from delegated call to delete_option()
     */
    public function updateOption($optionName, $value) {
        $prefixedOptionName = $this->prefix($optionName); // how it is stored in DB
        return update_option($prefixedOptionName, $value);
    }

    /**
     * A Role Option is an option defined in getOptionMetaData() as a choice of WP standard roles, e.g.
     * 'CanDoOperationX' => array('Can do Operation X', 'Administrator', 'Editor', 'Author', 'Contributor', 'Subscriber')
     * The idea is use an option to indicate what role level a user must minimally have in order to do some operation.
     * So if a Role Option 'CanDoOperationX' is set to 'Editor' then users which role 'Editor' or above should be
     * able to do Operation X.
     * Also see: canUserDoRoleOption()
     * @param  $optionName
     * @return string role name
     */
    public function getRoleOption($optionName) {
        $roleAllowed = $this->getOption($optionName);
        if (!$roleAllowed || $roleAllowed == '') {
            $roleAllowed = 'Administrator';
        }
        return $roleAllowed;
    }

    /**
     * Given a WP role name, return a WP capability which only that role and roles above it have
     * http://codex.wordpress.org/Roles_and_Capabilities
     * @param  $roleName
     * @return string a WP capability or '' if unknown input role
     */
    protected function roleToCapability($roleName) {
        switch ($roleName) {
            case 'Super Admin':
                return 'manage_options';
            case 'Administrator':
                return 'manage_options';
            case 'Editor':
                return 'publish_pages';
            case 'Author':
                return 'publish_posts';
            case 'Contributor':
                return 'edit_posts';
            case 'Subscriber':
                return 'read';
            case 'Anyone':
                return 'read';
        }
        return '';
    }

    /**
     * @param $roleName string a standard WP role name like 'Administrator'
     * @return bool
     */
    public function isUserRoleEqualOrBetterThan($roleName) {
        if ('Anyone' == $roleName) {
            return true;
        }
        $capability = $this->roleToCapability($roleName);
        return current_user_can($capability);
    }

    /**
     * @param  $optionName string name of a Role option (see comments in getRoleOption())
     * @return bool indicates if the user has adequate permissions
     */
    public function canUserDoRoleOption($optionName) {
        $roleAllowed = $this->getRoleOption($optionName);
        if ('Anyone' == $roleAllowed) {
            return true;
        }
        return $this->isUserRoleEqualOrBetterThan($roleAllowed);
    }

    /**
     * see: http://codex.wordpress.org/Creating_Options_Pages
     * @return void
     */
    public function createSettingsMenu() {
        $pluginName = $this->getPluginDisplayName();
        //create new top-level menu
        add_menu_page($pluginName . ' Plugin Settings',
                      $pluginName,
                      'administrator',
                      get_class($this),
                      array(&$this, 'settingsPage')
        /*,plugins_url('/images/icon.png', __FILE__)*/); // if you call 'plugins_url; be sure to "require_once" it

        //call register settings function
        add_action('admin_init', array(&$this, 'registerSettings'));
    }

    public function registerSettings() {
        $settingsGroup = get_class($this) . '-settings-group';
        $optionMetaData = $this->getOptionMetaData();
        foreach ($optionMetaData as $aOptionKey => $aOptionMeta) {
            register_setting($settingsGroup, $aOptionMeta);
        }
    }

    /**
     * Creates HTML for the Administration page to set options for this plugin.
     * Override this method to create a customized page.
     * @return void
     */
    public function settingsPage() {

        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'wp-notification-center'));
        }

        $optionMetaData = $this->getOptionMetaData();

		// Destinatios (websites) are automatically updated.
		
		$websites = $this->acquire_websites();
		
		$my_site_id = $this->getMySiteID($websites);
    
		// var_dump($websites);
		$this->updateOption('Websites', $websites);
		$my_websites = get_option('WPNC_Plugin_Websites', 'none');

		$this->updateOption('MySiteID', $my_site_id);


        // Save Posted Options
        if ($optionMetaData != null) {
            foreach ($optionMetaData as $aOptionKey => $aOptionMeta) {
                if (isset($_POST[$aOptionKey])) {
                    $this->updateOption($aOptionKey, $_POST[$aOptionKey]);
                }
            }
        }

        // HTML for the page
        $settingsGroup = get_class($this) . '-settings-group';
        ?>
        <div class="wrap">
            <h2>Wordpress通知センター 設定</h2>
            <table class="form-table"><tbody>
            <tr><td><?php _e('System', 'wp-notification-center'); ?></td><td><?php echo php_uname(); ?></td></tr>
            <tr><td><?php _e('PHP Version', 'wp-notification-center'); ?></td>
                <td><?php echo phpversion(); ?>
                <?php
                if (version_compare('5.2', phpversion()) > 0) {
                    echo '&nbsp;&nbsp;&nbsp;<span style="background-color: #ffcc00;">';
                    _e('(WARNING: This plugin may not work properly with versions earlier than PHP 5.2)', 'wp-notification-center');
                    echo '</span>';
                }
                ?>
                </td>
            </tr>
            <tr><td><?php _e('MySQL Version', 'wp-notification-center'); ?></td>
                <td><?php echo $this->getMySqlVersion() ?>
                    <?php
                    echo '&nbsp;&nbsp;&nbsp;<span style="background-color: #ffcc00;">';
                    if (version_compare('5.0', $this->getMySqlVersion()) > 0) {
                        _e('(WARNING: This plugin may not work properly with versions earlier than MySQL 5.0)', 'wp-notification-center');
                    }
                    echo '</span>';
                    ?>
                </td>
            </tr>
            </tbody></table>

            <h2>設定内容<?php /* echo $this->getPluginDisplayName(); echo ' '; _e('Settings', 'wp-notification-center'); */?></h2>

            <form method="post" action="">
            <?php settings_fields($settingsGroup); ?>
                <table class="form-table"><tbody>
                <?php
                if ($optionMetaData != null) {
                    foreach ($optionMetaData as $aOptionKey => $aOptionMeta) {
                        $displayText = is_array($aOptionMeta) ? $aOptionMeta[0] : $aOptionMeta;
                        ?>
                            <tr valign="top">
                                <th scope="row"><p><label for="<?php echo $aOptionKey ?>"><?php echo $displayText ?></label></p></th>
                                <td>
                                <?php $this->createFormControl($aOptionKey, $aOptionMeta, $this->getOption($aOptionKey)); ?>
                                </td>
                            </tr>
                        <?php
                    }
                }
                ?>
                </tbody></table>
                <p class="submit">
                    <input type="submit" class="button-primary"
                           value="<?php _e('Save Changes', 'wp-notification-center') ?>"/>
                </p>

            </form>
			<?php /* echo $my_websites; echo "  TODO: Destinations needs to be added"; */ ?>
        </div>
        <?php

    }

    /**
     * Helper-function outputs the correct form element (input tag, select tag) for the given item
     * @param  $aOptionKey string name of the option (un-prefixed)
     * @param  $aOptionMeta mixed meta-data for $aOptionKey (either a string display-name or an array(display-name, option1, option2, ...)
     * @param  $savedOptionValue string current value for $aOptionKey
     * @return void
     */
    protected function createFormControl($aOptionKey, $aOptionMeta, $savedOptionValue) {
        if (is_array($aOptionMeta) && count($aOptionMeta) >= 2) { // Drop-down list
            $choices = array_slice($aOptionMeta, 1);
            ?>
            <p><select name="<?php echo $aOptionKey ?>" id="<?php echo $aOptionKey ?>">
            <?php
                            foreach ($choices as $aChoice) {
								$aChoice_is_array = false;
								if (is_array($aChoice)) {
									$aChoice_is_array = true;
									$selected = ($aChoice['value'] == $savedOptionValue) ? 'selected' : '';
								} else {
									$selected = ($aChoice == $savedOptionValue) ? 'selected' : '';
								}
                ?>
                    <option value="<?php if ($aChoice_is_array) { echo $aChoice['value']; } else { echo $aChoice; } ?>" <?php echo $selected ?>><?php if($aChoice_is_array) { echo $this->getOptionValueI18nString($aChoice['name']); } else { echo $this->getOptionValueI18nString($aChoice); } ?></option>
                <?php
            }
            ?>
            </select></p>
            <?php

        }
        else { // Simple input field
            ?>
            <p><input type="text" name="<?php echo $aOptionKey ?>" id="<?php echo $aOptionKey ?>"
                      value="<?php echo esc_attr($savedOptionValue) ?>" size="50"/></p>
            <?php

        }
    }

    /**
     * Override this method and follow its format.
     * The purpose of this method is to provide i18n display strings for the values of options.
     * For example, you may create a options with values 'true' or 'false'.
     * In the options page, this will show as a drop down list with these choices.
     * But when the the language is not English, you would like to display different strings
     * for 'true' and 'false' while still keeping the value of that option that is actually saved in
     * the DB as 'true' or 'false'.
     * To do this, follow the convention of defining option values in getOptionMetaData() as canonical names
     * (what you want them to literally be, like 'true') and then add each one to the switch statement in this
     * function, returning the "__()" i18n name of that string.
     * @param  $optionValue string
     * @return string __($optionValue) if it is listed in this method, otherwise just returns $optionValue
     */
    protected function getOptionValueI18nString($optionValue) {
        switch ($optionValue) {
            case 'true':
                return __('true', 'wp-notification-center');
            case 'false':
                return __('false', 'wp-notification-center');

            case 'Administrator':
                return __('Administrator', 'wp-notification-center');
            case 'Editor':
                return __('Editor', 'wp-notification-center');
            case 'Author':
                return __('Author', 'wp-notification-center');
            case 'Contributor':
                return __('Contributor', 'wp-notification-center');
            case 'Subscriber':
                return __('Subscriber', 'wp-notification-center');
            case 'Anyone':
                return __('Anyone', 'wp-notification-center');
        }
        return $optionValue;
    }

    /**
     * Query MySQL DB for its version
     * @return string|false
     */
    protected function getMySqlVersion() {
        global $wpdb;
        $rows = $wpdb->get_results('select version() as mysqlversion');
        if (!empty($rows)) {
             return $rows[0]->mysqlversion;
        }
        return false;
    }

    /**
     * If you want to generate an email address like "no-reply@your-site.com" then
     * you can use this to get the domain name part.
     * E.g.  'no-reply@' . $this->getEmailDomain();
     * This code was stolen from the wp_mail function, where it generates a default
     * from "wordpress@your-site.com"
     * @return string domain name
     */
    public function getEmailDomain() {
        // Get the site domain and get rid of www.
        $sitename = strtolower($_SERVER['SERVER_NAME']);
        if (substr($sitename, 0, 4) == 'www.') {
            $sitename = substr($sitename, 4);
        }
        return $sitename;
    }

    /**
     * 通知の一覧表示 
     * Override this method to create a customized page.
     * @return void
     */
    public function notificationsOutPage() {
		// $screen = get_current_screen(); var_dump($screen);
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'wp-notification-center'));
        }
		    //Create an instance of our package class...
    $testListTable = new Notifications_Out_List();
    //Fetch, prepare, sort, and filter our data...
    $testListTable->prepare_items();

    ?>
    <div class="wrap">
        
    <div id="icon-users" class="icon32"><br/></div>
  		<h2>更新通知センターへの送信キュー</h2>
  
  		<div style="background:#ECECEC;border:1px solid #CCC;padding:15px;margin-top:5px;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;">
  			<p>この画面では、当ウェブサイトで更新した記事で、更新通知センターに送信準備ができている記事一覧を表示しています。</p>
  			<p>送信先サイトの「編集」をクリックすると送信先サイトを複数選択可能です（完了したら「更新」ボタンを押して下さい。また、送信せず削除するときはID列の「削除」をクリックして下さい。</p>
  			<p>通知センターに送信するときには、対象レコードを選択し、一括操作の「通知」を選択して適用して下さい。</p>
  		</div>
          
      <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
      <form id="movies-filter" method="get">
        <!-- For plugins, we also need to ensure that the form posts back to our current page -->
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
        <!-- Now we can render the completed list table -->
        <?php $testListTable->display() ?>
      </form>
    </div>
		<style>
			.multiselect {
				width:auto;
				height:auto;
				border:solid 1px #c0c0c0;
				overflow:auto;
			}

			.multiselect label {
				display:block;
				background-color:#ffffff;
				color:#000000;
			}

			.multiselect-on {
				color:#ffffff;
				background-color:#000099;
			}

			.wp-list-table .column-ID {width: 5%; }
			.wp-list-table .column-cb {width: 5%; }
			.wp-list-table .column-wp_postid {width: 5%; }
			.wp-list-table .column-post_status {width: 5%; }
			.wp-list-table .column-notification_status {width: 5%; }
		</style>
		<script>
			jQuery.fn.multiselect = function() {
				jQuery(this).each(function() {
					var checkboxes = jQuery(this).find("input:checkbox");
					checkboxes.each(function() {
						var checkbox = jQuery(this);
						// Highlight pre-selected checkboxes
						if (checkbox.prop("checked"))
							checkbox.parent().addClass("multiselect-on");

						// Highlight checkboxes that the user selects
						checkbox.click(function() {
							if (checkbox.prop("checked"))
								checkbox.parent().addClass("multiselect-on");
							else
								checkbox.parent().removeClass("multiselect-on");
						});
					});
				});
			};


			jQuery(document).ready(function(){
				jQuery(".quick_edit_show").click(function() {
					target_id = jQuery(this).attr("id");
					if (jQuery("#edit_"+target_id).hasClass("quick_edit_showing")) {
						jQuery("#edit_"+target_id).removeClass("quick_edit_showing");
						jQuery("#edit_"+target_id).hide();
					} else {
						jQuery("#edit_"+target_id).addClass("quick_edit_showing");
						jQuery("#edit_"+target_id).show();
					}
					console.log("#edit_"+target_id);
				});

				jQuery(".quick_edit_update").click(function() {
					target_id = jQuery(this).attr("id").replace("button","select");
					record_id = jQuery(this).attr("id").replace("button_destinations_",""); // button_destinations_X -> X
					hide_target_id = jQuery(this).attr("id").replace("button","edit"); // button_destinations_X -> X
					// alert(target_id);
					var post_val = [];
					jQuery(":checkbox[name='"+target_id+"']:checked").each(function (index, checkbox) {
						post_val.push(jQuery(checkbox).val());
					});
					console.log(post_val);
					var post_data = {
						action: 'update_destinations',
						record_id: record_id,
						data: post_val
					};
					jQuery.ajax({
						type: "POST",
						url: "admin-ajax.php?action=update_destinations",
						dataType: "json",
						async: true,
						data: post_data,
						success: function(data){
							alert('update done:' + data.message );
							jQuery("#text_"+target_id).html(data.html);

							jQuery("#"+hide_target_id).removeClass("quick_edit_showing");
							jQuery("#"+hide_target_id).hide();

						}

					});

				});

					jQuery(".multiselect").multiselect();

			});
		</script>

    <?php
	}

	/**
	 * 通知の一覧表示
	 * Override this method to create a customized page.
	 * @return void
	 */
	public function notificationsInPage() {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have sufficient permissions to access this page.', 'wp-notification-center'));
		}
		//Create an instance of our package class...
		$testListTable = new Notifications_In_List();
		//Fetch, prepare, sort, and filter our data...
		$testListTable->prepare_items();

		$refresh_url = admin_url('edit.php?page=WPNC_PluginNotifcationsIn&action=refresh');

		?>
		<div class="wrap">

			<div id="icon-users" class="icon32"><br/></div>
			<h2>更新通知センターからの受信記事</h2>

			<div style="background:#ECECEC;border:1px solid #CCC;padding:0 10px;margin-top:5px;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;">
				<p>この画面では、更新通知センターから送信された、他サイトの更新内容を表示しています。「リフレッシュ」をクリックすると再度更新通知センターからデータを受信します。</p>
				<p>記事を作成する場合はタイトル項目の「記事作成」をクリックして下さい。記事を作成せずに削除する場合にはタイトル項目の「削除」をクリックしてください。</p>
				<p>記事作成・受信レコード削除は一括操作も可能です。</p>
			</div>

			<!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
			<div><a href="<?php echo $refresh_url; ?>">リフレッシュ</a></div>
			<form id="movies-filter" method="get">
				<!-- For plugins, we also need to ensure that the form posts back to our current page -->
				<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
				<!-- Now we can render the completed list table -->
				<?php $testListTable->display() ?>

			</form>
			<div><a href="#" onclick="location.reload();">リフレッシュ</a></div>

		</div>
	<?php
	}

	function acquire_websites() {
		$endpoint_uri = get_option('WPNC_Plugin_EndPointURI', '');
		$apikey = get_option('WPNC_Plugin_APIKey', '');
    
		if ($endpoint_uri == '' || $apikey == '') {
			
			return null;
			// We can't die here
			// wp_die( "EndPoint URIと API Keyを正しく設定して下さい.");
		}
    
		$websites_json = json_encode($this->fetch_websites($endpoint_uri, $apikey));
		
		return $websites_json;
	}

	function getMySiteID($in_websites, $in_apikey = null) {
		if (!isset($in_websites['AuthInfo']['my_site_id'])) {
			return null;
		} else {
			return $in_websites['AuthInfo']['my_site_id'];
		}
	}


	function fetch_websites($in_endpoint_uri, $apikey) {
		
		$api_function = 'ApiWebsites/';
		#exit("$in_endpoint_uri/$api_function?apikey=$apikey");
		
		
		/*$curl = @curl_init("$in_endpoint_uri/$api_function?apikey=$apikey");
		
		if (!$curl) die('データの取得に失敗しました');
		
		curl_setopt($curl,CURLOPT_POST, FALSE);
		curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, FALSE);  // 自己署名証明書対策
		curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, FALSE);  //
		curl_setopt($curl,CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($curl,CURLOPT_COOKIEJAR,      'cookie');
		curl_setopt($curl,CURLOPT_COOKIEFILE,     'tmp');
		curl_setopt($curl,CURLOPT_FOLLOWLOCATION, TRUE); // Locationヘッダを追跡
		
		#curl_setopt($ch, CURLOPT_PROXY, "wnw3u.cloudapp.net");
    #curl_setopt($ch, CURLOPT_PROXYPORT, 10080);
		
		//curl_setopt($curl,CURLOPT_REFERER,        "REFERER");
		//curl_setopt($curl,CURLOPT_USERAGENT,      "USER_AGENT");
		
		$output 	 = curl_exec($curl);*/
		
		$output = file_get_contents("$in_endpoint_uri/$api_function?apikey=$apikey");
    $output_json = json_decode($output, true);

		if (is_null($output_json)) {
			return null;
		} else {
			if (isset($output_json['error'])) {
				return null;
			}
		}
		
		

		$websites = array();
		if (isset($output_json['result'])) {
			foreach ($output_json['result'] as $website) {
				$websites[] = $website['websites'];
			}
			return $websites;
		}

	}


}

