<?php


include_once('WPNC_LifeCycle.php');

class WPNC_Plugin extends WPNC_LifeCycle
{

  public function getOptionMetaData()
  {
    
    $categories = array(); // TODO: get list of categories
    
    $return_value = array(
      //'_version' => array('Installed Version'), // Leave this one commented-out. Uncomment to test upgrades.
      'EndPointURI'   => array(__('APIエンドポイントURI', 'wpnc-notification-center')),
      'APIKey'    => array(__('API Key', 'wpnc-notification-center')),
      'User'      => array(__('割当ユーザ', 'wpnc-notification-center')),
      'Category'    => array(__('割当カテゴリ', 'wpnc-notification-center')),
    );
    
    $users = get_users(array());
    
    foreach ($users as $user)
    {
      $return_value['User'][] = array('name' => $user->user_login, 'value' => $user->ID);
    }
    
    $categories = get_categories('hide_empty=0');
    foreach ($categories as $category)
    {
      $return_value['Category'][] = array('name' => $category->name, 'value' => $category->term_id);
    }
    
    return $return_value;
  
  }

//    protected function getOptionValueI18nString($optionValue) {
//        $i18nValue = parent::getOptionValueI18nString($optionValue);
//        return $i18nValue;
//    }

  protected function initOptions()
  {
    
    $options = $this->getOptionMetaData();
    
    if (!empty($options))
    {
      foreach ($options as $key => $arr)
      {
        if (is_array($arr) && count($arr > 1)) $this->addOption($key, $arr[1]);
      }
    }
    
  }

  public function getPluginDisplayName()
  {
    return __('WP Notification Center');
  }

  protected function getMainPluginFileName()
  {
    return 'wp-notification-center.php';
  }

  /**
   * See: http://plugin.michael-simpson.com/?page_id=101
   * Called by install() to create any database tables if needed.
   * Best Practice:
   * (1) Prefix all table names with $wpdb->prefix
   * (2) make table names lower case only
   * @return void
   */
  protected function installDatabaseTables()
  {
        //        global $wpdb;
        //        $tableName = $this->prefixTableName('mytable');
        //        $wpdb->query("CREATE TABLE IF NOT EXISTS `$tableName` (
        //            `id` INTEGER NOT NULL");
    parent::installDatabaseTables();
  }

  /**
   * See: http://plugin.michael-simpson.com/?page_id=101
   * Drop plugin-created tables on uninstall.
   * @return void
   */
  protected function unInstallDatabaseTables()
  {
      //        global $wpdb;
      //        $tableName = $this->prefixTableName('mytable');
      //        $wpdb->query("DROP TABLE IF EXISTS `$tableName`");
    parent::unInstallDatabaseTables();
  }


  /**
   * Perform actions when upgrading from version X to version Y
   * See: http://plugin.michael-simpson.com/?page_id=35
   * @return void
   */
  public function upgrade()
  {
    parent::upgrade();
  }

  public function uninstall()
  {
    parent::uninstall();
  }

  public function addActionsAndFilters()
  {

    // Add options administration page
    // http://plugin.michael-simpson.com/?page_id=47
    add_action('admin_menu', array(&$this, 'addSettingsSubMenuPage'));
    /*    add_action('admin_menu', array(&$this, 'addNotificationsOutToSubMenuPage'));
    add_action('admin_menu', array(&$this, 'addNotificationsInToSubMenuPage'));*/
    
    
    // Add Actions & Filters
    // http://plugin.michael-simpson.com/?page_id=37
    add_action('save_post', array(&$this, 'addNotification'));
    
    
    // Register short codes
    // http://plugin.michael-simpson.com/?page_id=39
    
    
    // Register AJAX hooks
    // http://plugin.michael-simpson.com/?page_id=41
    add_action('wp_ajax_update_destinations', array(&$this, 'ajaxUpdateDestinations'));
    // http://cinra-wpnc-03.vtest01.info/wordpress/wp-admin/admin-ajax.php?action=update_destinations&whatever=100
    
    
    // for redirection to work properly, ob_start needs to be added.
    add_action('init', array(&$this, 'appOutputBuffer'));
    
    wp_enqueue_script('jquery-ui-core');
    
    // 'wp_dashboard_setup' アクションにフックし、登録する
    add_action('wp_dashboard_setup', array(&$this, 'wpnc_add_dashboard_widgets') );

  }

  public function appNotificationOutCustomBox($column_name, $screen)
  {
    
    $my_fields = array(
      array(
        'column_name' => 'destinations',
        'field_title' => 'Destinations',
        'field_name' => 'destinations',
      ),
    );

    foreach ($my_fields as $field)
    {

      if ( $column_name === $field['column_name'] && $screen === 'edit-tags' )
      {

        print( '<fieldset><div class="inline-edit-col">' );
        print( '<label>' );
        printf( '<span class="title">%1$s</span>', _e( $field['field_title'] ) );
        printf( '<span class="input-text-wrap"><input type="text" name="%1$s" class="ptitle" value=""></span>', $field['field_name'] );
        print( '</label>' );
        print( '</div></fieldset>' );

      }

    }

  }

  public function appOutputBuffer()
  {
    ob_start();
  }

  public function addNotification($post_ID)
  {
    
    global $wpdb;
    $table_prefix = WPNC_PREFIX;

    // only "post"s are to be notified
    $post_type = get_post_type($post_ID);
    if ($post_type != 'post') return;

    // my site id has to be set.
    $my_site_id = get_option('WPNC_Plugin_MySiteID', 'none');
    if ($my_site_id == 'none') return;

    // do nothing for posts in category for inbound notification.
    $assigned_category = get_option('WPNC_Plugin_Category', "none");
    if ($assigned_category != 'none')
    {
      $post_categories   = wp_get_post_categories( $post_ID );
      foreach ($post_categories as $c)
      {
        // $cat = get_category( $c );
        // $cats[] = array( 'name' => $cat->name, 'slug' => $cat->slug );
        if ($assigned_category == $c)
        {
          return;
        }
      }
    }

    // eyechatch path
    $thumb = wp_get_attachment_image_src( get_post_thumbnail_id($post_ID), 'thumbnail' );
    $url   = $thumb['0'];

    // tags
    $tags     = wp_get_post_tags($post_ID);
    $tags_arr = array();
    foreach ($tags as $tag) {
      $tags_arr[] = $tag->name;
    }
    
    // INSERT/UPDATE NOTIFICATIONS
    $post = get_post($post_ID);

    $website_id = $my_site_id;
    $wp_postid = $post->ID;
    $wp_post_title = $post->post_title;
    $wp_post_content = $post->post_content;
    $wp_tags = implode(',', $tags_arr);
    $wp_eyecatch_path_org = $url;
    $post_date = $post->post_date;
    $post_status = $post->post_status;
    $notification_status = "NEW";
    $create_date = date('Y-m-d H:i:s');
    $modify_date = date('Y-m-d H:i:s');

    // save only if status is "Publish"
    if (!in_array($post_status, array('publish')))
    {
      return;
    }

    // check if the id is already in.
    $sql = "select count(*) cnt from {$table_prefix}notifications_out where wp_postid = '".$post_ID."'";
    $where = "";
    $results = $wpdb->get_results($sql);
    $is_exist = $results[0]->cnt;
    if ($is_exist > 0)
    {
      $notification_status = "UPDATE";
      $modify_date = date('Y-m-d H:i:s');
      
      $query_u = $wpdb->prepare(
        "update {$table_prefix}notifications_out set wp_post_title = %s, wp_post_content = %s, wp_tags = %s, wp_eyechatch_path_org = %s, post_date = %s, post_status = %s, notification_status = %s, modify_date = %s where wp_postid = %s",
        $wp_post_title, $wp_post_content, $wp_tags, $wp_eyecatch_path_org, $post_date, $post_status, $notification_status, $modify_date, $wp_postid);
      $wpdb->query($query_u);
    }
    else
    {
      $query_i = $wpdb->prepare(
        "insert into {$table_prefix}notifications_out set wp_postid = %s, website_id = %s, wp_post_title = %s, wp_post_content = %s, wp_tags = %s, wp_eyechatch_path_org = %s, post_date = %s, post_status = %s, notification_status = %s, create_date = %s, modify_date = %s",
        $post_ID, $website_id, $wp_post_title, $wp_post_content, $wp_tags, $wp_eyecatch_path_org, $post_date, $post_status, $notification_status, $create_date, $modify_date);
      $wpdb->query($query_i);
    }

  }

  public function ajaxUpdateDestinations()
  {
    
    global $wpdb;
    $table_prefix = WPNC_PREFIX;

    $charset = get_bloginfo( 'charset' );
    $output_array = array();
    $output_array = array( 'foo' => 'bar', 'hoge' => 'fuga');
    
    $my_websites = get_option('WPNC_Plugin_Websites', 'none');
    $destination_websites = json_decode($my_websites, true);


    if ($my_websites == 'none'
      || is_null($destination_websites)
      || !isset($_POST['record_id'])
      || !isset($_POST['data']))
    {
      // target_id and data are require.
      $output_array['message'] = 'Failed';
    }
    else
    {
      
      $record_id = $_POST['record_id']; // select_destinations_X
      $data = $_POST['data'];
      $destinations_string_arr = array();
      $destinations_value_arr = array();

      foreach ($destination_websites as $website)
      {
        if (array_search($website['id'], $data) !== false)
        { // has to be !==
          $destinations_string_arr[] = $website['name'];
          $destinations_value_arr[] = $website['id'];
        }
      }

      $destinations_string  = implode('<br>', $destinations_string_arr);
      $destinations_value = implode(',', $destinations_value_arr);

      global $wpdb;
      $query_u = $wpdb->prepare(
              "update {$table_prefix}notifications_out set destinations = %s, destinations_values = %s where id = %s",
              $destinations_string, $destinations_value, $record_id);
      $wpdb->query($query_u);

      $output_array['html'] = $destinations_string;

      $output_array['message'] = 'OK';
      
    }

    $json = json_encode( $output_array );
    nocache_headers();
    header( "Content-Type: application/json; charset=$charset" );
    echo $json;

    die(); // this is required to return a proper result

  }

  // ダッシュボードウィジェットにコンテンツを出力する関数を作成する
  function wpnc_dashboard_widget_function()
  {

    $plugin_url = plugins_url( $path, $plugin );
    $url_notifications_out = "plugins.php?page=WPNC_PluginNotifcationsOut";
    $url_refresh           = admin_url('edit.php?page=WPNC_PluginNotifcationsIn&action=refresh_dashboard');

    echo "<a href='$url_refresh'><button class='button button-primary'>リフレッシュ</button></a>&nbsp;&nbsp;&nbsp;&nbsp;";
    // Display whatever it is you want to show
    echo "<a href='edit.php?page=WPNC_PluginNotifcationsIn'>全ての通知(IN)を見る</a><br/>";

    echo $this->wpnc_dashboard_list_notifications_out();
    echo "<br/><br/>";

    echo "通知(OUT)は<a href='$url_notifications_out'>こちら</a>";
    echo "<script type='text/javascript'>function dashboardRefresh() { location.href='".$url_refresh."';} setInterval('dashboardRefresh()',3600000);</script>";
    
  }

  // アクションフックで使用する関数を作成する
  function wpnc_add_dashboard_widgets()
  {
    wp_add_dashboard_widget('wpnc_dashboard_widget', 'Wordpress Notification Center', array(&$this, 'wpnc_dashboard_widget_function'));
  }

  function wpnc_dashboard_list_notifications_out()
  {
    global $wpdb;
    $table_prefix = WPNC_PREFIX;


    $query = "SELECT * FROM {$table_prefix}notifications_in order by post_date desc";
    $rowcount = 0;
    $rows_html = array();
    $rows_html[] = "<ul>";
    $rows = $wpdb->get_results($query);
    foreach ($rows as $row) {
      $rowcount += 1;
      if (/* is_null($row) || */ $rowcount > 10) {
        break;
      }
      $create_date     = $row->create_date;
      $id              = $row->id;
      $title           = mb_substr($row->wp_post_title,0,10)."...";
      $post_create_url = "edit.php?page=WPNC_PluginNotifcationsIn&action=send&notificationin[]=$id";
      $rows_html[] =<<<EOF
    <li>
      <span>$create_date</span>
      <a href="$post_create_url">$title</a>
    </li>
EOF;
    }

    $rows_html[] = "</ul>";

    $output = implode("\n",$rows_html);

    return $output;

  }

}