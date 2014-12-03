<?php

/*  Copyright 2014  Matthew Van Andel  (email : matt@mattvanandel.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA


  Portions 2014 Cinra, Co., Ltd.
*/


/*************************** LOAD THE BASE CLASS *******************************
 *******************************************************************************
 * The WP_List_Table class isn't automatically available to plugins, so we need
 * to check if it's available and load it if necessary. In this tutorial, we are
 * going to use the WP_List_Table class directly from WordPress core.
 *
 * IMPORTANT:
 * Please note that the WP_List_Table class technically isn't an official API,
 * and it could change at some point in the distant future. Should that happen,
 * I will update this plugin with the most current techniques for your reference
 * immediately.
 *
 * If you are really worried about future compatibility, you can make a copy of
 * the WP_List_Table class (file path is shown just below) to use and distribute
 * with your plugins. If you do that, just remember to change the name of the
 * class to avoid conflicts with core.
 *
 * Since I will be keeping this tutorial up-to-date for the foreseeable future,
 * I am going to work with the copy of the class provided in WordPress core.
 */
if (!class_exists('WP_List_Table'))
{
  require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}




/************************** CREATE A PACKAGE CLASS *****************************
 *******************************************************************************
 * Create a new list table package that extends the core WP_List_Table class.
 * WP_List_Table contains most of the framework for generating the table, but we
 * need to define and override some methods so that our data can be displayed
 * exactly the way we need it to be.
 * 
 * To display this example on a page, you will first need to instantiate the class,
 * then call $yourInstance->prepare_items() to handle any data manipulation, then
 * finally call $yourInstance->display() to render the table to the page.
 * 
 * Our theme for this list table is going to be movies.
 */
class Notifications_In_List extends WP_List_Table
{

    /** ************************************************************************
     * REQUIRED. Set up a constructor that references the parent constructor. We 
     * use the parent reference to set some default configs.
     ***************************************************************************/
    function __construct(){
        global $status, $page;
                
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'Notification In',     //singular name of the listed records
            'plural'    => 'Notifications In',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
        
    }


    /** ************************************************************************
     * Recommended. This method is called when the parent class can't find a method
     * specifically build for a given column. Generally, it's recommended to include
     * one method for each column you want to render, keeping your package class
     * neat and organized. For example, if the class needs to process a column
     * named 'title', it would first see if a method named $this->column_title() 
     * exists - if it does, that method will be used. If it doesn't, this one will
     * be used. Generally, you should try to use custom column methods as much as 
     * possible. 
     * 
     * Since we have defined a column_title() method later on, this method doesn't
     * need to concern itself with any column with a name of 'title'. Instead, it
     * needs to handle everything else.
     * 
     * For more detailed insight into how columns are handled, take a look at 
     * WP_List_Table::single_row_columns()
     * 
     * @param array $item A singular item (one full row's worth of data)
     * @param array $column_name The name/slug of the column to be processed
     * @return string Text or HTML to be placed inside the column <td>
     **************************************************************************/
    function column_default($item, $column_name){
        return $item[$column_name];
/*
        switch($column_name){
            case 'rating':
            case 'director':
                return $item[$column_name];
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
*/
    }

  function column_website_id($item)
  {
    
    $my_websites_json = get_option('WPNC_Plugin_Websites', 'none');
    
    if ($my_websites_json == 'none')
    {
      return $item['website_id']; // Fallback for missing website_id entries.
    }
    else
    {
      
      $my_websites = json_decode($my_websites_json, true);
      
      if (!is_null($my_websites))
      {
        $name = $item['website_id'];
        foreach ($my_websites as $website)
        {
          if ($website['id'] == $item['website_id'])
          {
            $name = $website['name'];
            break;
          }
        }
        return $name;

      }
      else
      {
        return $item['website_id']; // Fallback for missing website_id entries.
      }
    }
  }


  function column_wp_eyechatch_path_org ($item)
  {
    
    $ret_value = '';

    if (strlen($item['wp_eyechatch_path_org']) > 0)
    {
      $ret_value = "<img src=\"".$item['wp_eyechatch_path_org']."\" width=\"100\">";
    }

    return $ret_value;
    
  }


    /** ************************************************************************
     * Recommended. This is a custom column method and is responsible for what
     * is rendered in any column with a name/slug of 'title'. Every time the class
     * needs to render a column, it first looks for a method named 
     * column_{$column_title} - if it exists, that method is run. If it doesn't
     * exist, column_default() is called instead.
     * 
     * This example also illustrates how to implement rollover actions. Actions
     * should be an associative array formatted as 'slug'=>'link html' - and you
     * will need to generate the URLs yourself. You could even ensure the links
     * 
     * 
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
    function column_title($item)
    {
        
        //Build row actions
        $actions = array(
            'send'      => sprintf('<a href="?page=%s&action=%s&notificationin[]=%s">投稿作成</a>',$_REQUEST['page'],'send',$item['id']),
            'delete'    => sprintf('<a href="?page=%s&action=%s&notificationin[]=%s">削除</a>',$_REQUEST['page'],'delete',$item['id']),
        );
        
        //Return the title contents
        return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
            /*$1%s*/ $item['wp_post_title'],
            /*$2%s*/ $item['id'],
            /*$3%s*/ $this->row_actions($actions)
        );
    }


    /** ************************************************************************
     * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
     * is given special treatment when columns are processed. It ALWAYS needs to
     * have it's own method.
     * 
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
    function column_cb($item)
    {
      
      return sprintf(
        '<input type="checkbox" name="%1$s[]" value="%2$s" />',
        /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
        /*$2%s*/ $item['id']                //The value of the checkbox should be the record's id
      );
      
    }


  /** ************************************************************************
   * REQUIRED! This method dictates the table's columns and titles. This should
   * return an array where the key is the column slug (and class) and the value 
   * is the column's title text. If you need a checkbox for bulk actions, refer
   * to the $columns array below.
   * 
   * The 'cb' column is treated differently than the rest. If including a checkbox
   * column in your table you must create a column_cb() method. If you don't need
   * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
   * 
   * @see WP_List_Table::::single_row_columns()
   * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
   **************************************************************************/
  function get_columns()
  {
    $columns = array(
      'cb'                => '<input type="checkbox" />', //Render a checkbox instead of text
      //'button'              => '<button />', //Render a checkbox instead of text
      
      'title'             => 'タイトル',
      //'id'              => 'ID',
      //'wp_postid'           => 'PostID',
      //'wp_post_title'       => 'Title',
      'wp_post_content'       => '投稿内容',
      'wp_tags'             => 'タグ',
      'wp_eyechatch_path_org'     => 'アイキャッチ画像',
      'post_date'           => '投稿日',//ほんとに投稿日？
      'post_status'           => '投稿ステータス',
      'notification_status'   => '通知ステータス',
      'website_id'        => '通知元サイト',
      //'create_date'     => 'Create Date',
      //'modify_date'     => 'Modify Date',
    );
    return $columns;
  }


    /** ************************************************************************
     * Optional. If you want one or more columns to be sortable (ASC/DESC toggle), 
     * you will need to register it here. This should return an array where the 
     * key is the column that needs to be sortable, and the value is db column to 
     * sort by. Often, the key and value will be the same, but this is not always
     * the case (as the value is a column name from the database, not the list table).
     * 
     * This method merely defines which columns should be sortable and makes them
     * clickable - it does not handle the actual sorting. You still need to detect
     * the ORDERBY and ORDER querystring variables within prepare_items() and sort
     * your data accordingly (usually by modifying your query).
     * 
     * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
     **************************************************************************/
    function get_sortable_columns()
    {
        $sortable_columns = array(
      /*
            'title'     => array('title',false),     //true means it's already sorted
            'rating'    => array('rating',false),
            'director'  => array('director',false)
       */
        );
        return $sortable_columns;
    }


    /** ************************************************************************
     * Optional. If you need to include bulk actions in your list table, this is
     * the place to define them. Bulk actions are an associative array in the format
     * 'slug'=>'Visible Title'
     * 
     * If this method returns an empty value, no bulk action will be rendered. If
     * you specify any bulk actions, the bulk actions box will be rendered with
     * the table automatically on display().
     * 
     * Also note that list tables are not automatically wrapped in <form> elements,
     * so you will need to create those manually in order for bulk actions to function.
     * 
     * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_bulk_actions() {
        $actions = array(
            'delete'    => '削除',
            'send'      => '投稿作成',
        );
        return $actions;
    }


  /** ************************************************************************
   * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
   * For this example package, we will handle it in the class to keep things
   * clean and organized.
   * 
   * @see $this->prepare_items()
   **************************************************************************/
  function process_bulk_action()
  {
    //    var_dump($_REQUEST['notificationin']);
    
    //Detect when a bulk action is being triggered...
    if ( 'delete'=== $this->current_action() )
    {
      $this->process_bulk_delete($_REQUEST['notificationin']);
      // wp_die('Items deleted (or they would be if we had items to delete)!');
    }
    else if ( 'send'=== $this->current_action() )
    {
      $this->process_bulk_createpost($_REQUEST['notificationin']);
      // wp_die('投稿を作成しました!');
    }
    else if ( 'refresh' === $this->current_action() )
    {
      $this->process_refresh();
      $redirect_to = admin_url('edit.php?page=WPNC_PluginNotifcationsIn#refresh_done');
      wp_redirect($redirect_to, 302);
    }
    else if ( 'refresh_dashboard' === $this->current_action() )
    {
      $this->process_refresh();
      $redirect_to = admin_url('index.php');
      wp_redirect($redirect_to, 302);
    }
        
  }

  function process_bulk_delete($in_target_ids) {
    global $wpdb;
    $table_prefix = WPNC_PREFIX;

    if (!is_array($in_target_ids)) {
      $in_target_ids = array($in_target_ids);
    }


    global $wpdb;
    foreach ($in_target_ids as $rec_id) {
      $query = $wpdb->prepare(
        "delete from {$table_prefix}notifications_in where id = %s",
        $rec_id);
      $rows = $wpdb->get_results($query);
    }

    // redirect after deletion
    $redirect_to = admin_url('edit.php?page=WPNC_PluginNotifcationsIn#deletion_done');
    wp_redirect($redirect_to, 302);
    exit;

  }

  function process_bulk_createpost($in_target_ids)
  {
    
    global $wpdb;

    if (!is_array($in_target_ids)) $in_target_ids = array($in_target_ids);

    $endpoint_uri = get_option('WPNC_Plugin_EndPointURI', 'none');
    $apikey = get_option('WPNC_Plugin_APIKey', 'none');
    if ($endpoint_uri == 'none' || $apikey == 'none')
    {
      echo "EndPoint URIと API Keyを正しく設定して下さい.<br>";
    }

    $assigned_user_id = get_option('WPNC_Plugin_User');
    $assigned_category = get_option('WPNC_Plugin_Category');
    
    foreach ($in_target_ids as $rec_id)
    {
      $result = $this->create_post_from_notification($rec_id);
    }

    // redirect after creation
    if ($result != 0)
    {
      wp_redirect(admin_url("/post.php?post=$result&action=edit"), 302);
      exit;
    }

    return null;
    
  }

  function process_refresh()
  {
    
    $endpoint_uri = get_option('WPNC_Plugin_EndPointURI', 'none');
    $apikey = get_option('WPNC_Plugin_APIKey', 'none');

    if ($endpoint_uri == 'none' || $apikey == 'none')
    {
      wp_die( "EndPoint URIと API Keyを正しく設定して下さい.<br>");
    }

    $this->fetch_notifications($endpoint_uri, $apikey);

    return 0;
    
  }

  function create_post_from_notification($in_notofication_id)
  {
    
    global $wpdb;
    $table_prefix = WPNC_PREFIX;
    
    $query = $wpdb->prepare(
      "select * from {$table_prefix}notifications_in where id = %s",
      $in_notofication_id);
    $rows = $wpdb->get_results($query);
    
    if (is_null($rows)) return 0;
    
    $row = $rows[0];
    
    $id                    = $row->id;
    $website_id            = $row->website_id;
    $wp_postid             = $row->wp_postid;
    $wp_post_title         = $row->wp_post_title;
    $wp_post_content       = $row->wp_post_content;
    $wp_tags               = $row->wp_tags;
    $wp_meta               = $row->wp_post_meta;
    $wp_eyechatch_path_org = $row->wp_eyechatch_path_org;
    $post_date             = $row->post_date;
    $post_status           = $row->post_status;
    $notification_status   = $row->notification_status;
    $create_date           = $row->create_date;
    $modify_date           = $row->modify_date;

    $assigned_user_id   = get_option('WPNC_Plugin_User');
    $assigned_category  = get_option('WPNC_Plugin_Category');

    $new_post = array(
      'post_title' => $wp_post_title, // 'My New Post',
      'post_content' => $wp_post_content, // 'Lorem ipsum dolor sit amet...',
      'post_status' => 'draft',
      'post_date' => date('Y-m-d H:i:s'),
      'post_author' => $assigned_user_id, // $user_ID,
      'post_type' => 'post',
      'post_category' => array($assigned_category)// array(0)
    );

    $post_id = wp_insert_post($new_post);
    if ($post_id == 0)
    {
      wp_die("投稿作成に失敗しました <BR>");
    }
    else
    {

      // Eyecatch
      if (!is_null($wp_eyechatch_path_org))
      {
        
        $upload_dir = wp_upload_dir();
        $image_data = file_get_contents($wp_eyechatch_path_org);
        $filename = basename($wp_eyechatch_path_org);
        
        if(wp_mkdir_p($upload_dir['path']))
        {
          $file = $upload_dir['path'] . '/' . $filename;
        }
        else
        {
          $file = $upload_dir['basedir'] . '/' . $filename;
        }
        
        file_put_contents($file, $image_data);
        $wp_filetype = wp_check_filetype($filename, null );
        $attachment = array(
          'post_mime_type' => $wp_filetype['type'],
          'post_title' => sanitize_file_name($filename),
          'post_content' => '',
          'post_status' => 'inherit'
        );
        $attach_id = wp_insert_attachment( $attachment, $file, $post_id );
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
        wp_update_attachment_metadata( $attach_id, $attach_data );
        set_post_thumbnail( $post_id, $attach_id );
        
      }
      
      // Meta
      $metadata = json_decode($wp_meta);
      if (count($metadata) > 0)
      {
        foreach ($metadata as $k => $v)
        {
          if (!in_array($k, array('_edit_last', '_edit_lock', '_thumbnail_id'))) update_post_meta($post_id, $k, $v);
        }
      }
      
      // Tags
      $tags = json_decode($wp_tags);
      if (count($wp_tags) > 0)
      {
        wp_set_post_tags( $post_id, $tags, true);
      }
      
      $query = $wpdb->prepare(
        "delete from {$table_prefix}notifications_in where id = %s",
        $in_notofication_id);
      $rows = $wpdb->get_results($query);

    }

    return $post_id;

  }

  function fetch_notifications($in_endpoint_uri, $apikey)
  {
    
    $api_function = 'ApiNotifications';
    
    /*$curl = curl_init("$in_endpoint_uri/$api_function?apikey=$apikey");
    curl_setopt($curl,CURLOPT_POST, FALSE);
    curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, FALSE);  // 我輩証明書対策
    curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, FALSE);  // 
    curl_setopt($curl,CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl,CURLOPT_COOKIEJAR,      'cookie');
    curl_setopt($curl,CURLOPT_COOKIEFILE,     'tmp');
    curl_setopt($curl,CURLOPT_FOLLOWLOCATION, TRUE); // Locationヘッダを追跡
    //curl_setopt($curl,CURLOPT_REFERER,        "REFERER");
    //curl_setopt($curl,CURLOPT_USERAGENT,      "USER_AGENT"); 

    $output    = curl_exec($curl);*/
    
    $output = file_get_contents("$in_endpoint_uri/$api_function?apikey=$apikey");
    
    $output_json = json_decode($output, true);

    if (is_null($output_json))
    {
      return null;
    }
    else
    {
      if (isset($output_json['error'])) return null;
    }

    global $wpdb;
    $table_prefix = WPNC_PREFIX;
    
    #echo '<pre>';print_r($output_json['result']);echo '</pre>';exit;

    foreach ($output_json['result'] as $notification)
    {
      
      $data = $notification['notifications'];
      $sql = "select count(*) cnt from {$table_prefix}notifications_in where wp_postid = '".$data['wp_postid']."' and website_id = '".$data['website_id']."'";
      $results = $wpdb->get_results($sql);

      if (is_null($results))
      {
        continue;
      }

      $is_exist = $results[0]->cnt;
      //var_dump($sql);
      //var_dump($results);exit;

      $notification_status = "UPDATE";
      $modify_date = date('Y-m-d H:i:s');

      $website_id            = $data['website_id'];
      $wp_postid             = $data['wp_postid'];
      $wp_post_title         = $data['wp_post_title'];
      $wp_post_content       = $data['wp_post_content'];
      $wp_tags               = $data['wp_tags'];
      $wp_meta               = $data['post_meta'];
      $wp_eyechatch_path_org = $data['wp_eyechatch_path_org'];
      $post_date             = $data['post_date'];
      $post_status           = $data['post_status'];
      $notification_status   = $data['notification_status'];
      $org_website_id        = $data['org_website_id'];
      $create_date           = $data['create_date'];
      $modify_date           = $data['modify_date'];

      if ($is_exist > 0)
      {
        $query_u = $wpdb->prepare(
          "update {$table_prefix}notifications_in set wp_post_title = %s, wp_post_content = %s, wp_tags = %s, wp_eyechatch_path_org = %s, post_date = %s, post_status = %s, notification_status = %s, modify_date = %s where wp_postid = %s, website_id = %s, org_website_id = %s, wp_post_meta = %s",
        $wp_post_title, $wp_post_content, $wp_tags, $wp_eyechatch_path_org, $post_date, $post_status, $notification_status, $modify_date, $wp_postid, $website_id, $org_website_id, $wp_meta);
        $wpdb->query($query_u);
      }
      else
      {
        $query_i = $wpdb->prepare(
          "insert into {$table_prefix}notifications_in set wp_postid = %s, website_id = %s, wp_post_title = %s, wp_post_content = %s, wp_tags = %s, wp_eyechatch_path_org = %s, post_date = %s, post_status = %s, notification_status = %s, create_date = %s, modify_date = %s, org_website_id = %s, wp_post_meta = %s",
        $wp_postid, $website_id, $wp_post_title, $wp_post_content, $wp_tags, $wp_eyechatch_path_org, $post_date, $post_status, $notification_status, $create_date, $modify_date, $org_website_id, $wp_meta);
        $wpdb->query($query_i);
      }

    }

    return $output;
  }


  /** ************************************************************************
   * REQUIRED! This is where you prepare your data for display. This method will
   * usually be used to query the database, sort and filter the data, and generally
   * get it ready to be displayed. At a minimum, we should set $this->items and
   * $this->set_pagination_args(), although the following properties and methods
   * are frequently interacted with here...
   * 
   * @global WPDB $wpdb
   * @uses $this->_column_headers
   * @uses $this->items
   * @uses $this->get_columns()
   * @uses $this->get_sortable_columns()
   * @uses $this->get_pagenum()
   * @uses $this->set_pagination_args()
   **************************************************************************/
  
  function prepare_items()
  {
    
    global $wpdb; //This is used only if making any database queries
    $table_prefix = WPNC_PREFIX;
    
    /**
     * First, lets decide how many records per page to show
     */
    $per_page = 100;
    
    
    /**
     * REQUIRED. Now we need to define our column headers. This includes a complete
     * array of columns to be displayed (slugs & titles), a list of columns
     * to keep hidden, and a list of columns that are sortable. Each of these
     * can be defined in another method (as we've done here) before being
     * used to build the value for our _column_headers property.
     */
    $columns = $this->get_columns();
    $hidden = array();
    $sortable = $this->get_sortable_columns();
    
    
    /**
     * REQUIRED. Finally, we build an array to be used by the class for column 
     * headers. The $this->_column_headers property takes an array which contains
     * 3 other arrays. One for all columns, one for hidden columns, and one
     * for sortable columns.
     */
    $this->_column_headers = array($columns, $hidden, $sortable);
        
        
    /**
     * Optional. You can handle your bulk actions however you see fit. In this
     * case, we'll handle them within our package just to keep things clean.
     */
    $this->process_bulk_action();
    
    
    /**
     * Instead of querying a database, we're going to fetch the example data
     * property we created for use in this plugin. This makes this example 
     * package slightly different than one you might build on your own. In 
     * this example, we'll be using array manipulation to sort and paginate 
     * our data. In a real-world implementation, you will probably want to 
     * use sort and pagination data to build a custom query instead, as you'll
     * be able to use your precisely-queried data immediately.
     */
    $query = "SELECT * FROM {$table_prefix}notifications_in order by post_date desc";
    $totalitems = $wpdb->query($query);
    $items = $wpdb->get_results($query);
    $data = array();
    foreach ($items as $item)
    {
      $rec = array();
      $rec['id'] = $item->id;
      $rec['website_id'] = $item->website_id;
      $rec['wp_postid'] = $item->wp_postid;
      $rec['wp_post_title'] = $item->wp_post_title;
      $rec['title'] = $item->id;
      $rec['wp_post_content'] = mb_substr(strip_tags($item->wp_post_content),0,10)."...";
      $rec['wp_tags'] = $item->wp_tags;
      $rec['wp_eyechatch_path_org'] = $item->wp_eyechatch_path_org;
      $rec['post_date'] = $item->post_date;
      $rec['post_status'] = $item->post_status;
      $rec['notification_status'] = $item->notification_status;
      // $rec['destinations'] = 'XXX';
      
      $rec['create_date'] = $item->create_date;
      $rec['modify_date'] = $item->modify_date;
      $data[] = $rec;
    }
                
        
    /**
     * This checks for sorting input and sorts the data in our array accordingly.
     * 
     * In a real-world situation involving a database, you would probably want 
     * to handle sorting by passing the 'orderby' and 'order' values directly 
     * to a custom query. The returned data will be pre-sorted, and this array
     * sorting technique would be unnecessary.
     */
    function usort_reorder($a,$b)
    {
      $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'title'; //If no sort, default to title
      $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
      $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
      return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
    }
    // usort($data, 'usort_reorder');
        
        
    /***********************************************************************
     * ---------------------------------------------------------------------
     * vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv
     * 
     * In a real-world situation, this is where you would place your query.
     *
     * For information on making queries in WordPress, see this Codex entry:
     * http://codex.wordpress.org/Class_Reference/wpdb
     * 
     * ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
     * ---------------------------------------------------------------------
     **********************************************************************/
    
            
    /**
     * REQUIRED for pagination. Let's figure out what page the user is currently 
     * looking at. We'll need this later, so you should always include it in 
     * your own package classes.
     */
    $current_page = $this->get_pagenum();
    
    /**
     * REQUIRED for pagination. Let's check how many items are in our data array. 
     * In real-world use, this would be the total number of items in your database, 
     * without filtering. We'll need this later, so you should always include it 
     * in your own package classes.
     */
    $total_items = count($data);
    
    
    /**
     * The WP_List_Table class does not handle pagination for us, so we need
     * to ensure that the data is trimmed to only the current page. We can use
     * array_slice() to 
     */
    $data = array_slice($data,(($current_page-1)*$per_page),$per_page);
        
    /**
     * REQUIRED. Now we can add our *sorted* data to the items property, where 
     * it can be used by the rest of the class.
     */
    $this->items = $data;
    
    /**
     * REQUIRED. We also have to register our pagination options & calculations.
     */
    $this->set_pagination_args( array(
        'total_items' => $total_items,                  //WE have to calculate the total number of items
        'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
        'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
    ) );
  }
}