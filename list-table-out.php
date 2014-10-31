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
if(!class_exists('WP_List_Table')){
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
class Notifications_Out_List extends WP_List_Table {
    


    /** ************************************************************************
     * REQUIRED. Set up a constructor that references the parent constructor. We 
     * use the parent reference to set some default configs.
     ***************************************************************************/
    function __construct(){
        global $status, $page;
                
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'Notification Out',     //singular name of the listed records
            'plural'    => 'Notifications Out',    //plural name of the listed records
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
        switch($column_name){
            case 'rating':
            case 'director':
                return $item[$column_name];
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
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
    function column_ID($item){
        
        //Build row actions
    $actions = array(
      'delete'    => sprintf('<a href="?page=%s&action=%s&notificationout[]=%s">削除</a>',$_REQUEST['page'],'delete',$item['id']),
    );

        
        //Return the title contents
        return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
            /*$1%s*/ $item['title'],
            /*$2%s*/ $item['ID'],
            /*$3%s*/ $this->row_actions($actions)
        );
    }

  function column_wp_eyechatch_path_org ($item) {
    $ret_value = '';

    if (strlen($item['wp_eyechatch_path_org']) > 0) {
      $ret_value = "<img src=\"".$item['wp_eyechatch_path_org']."\" width=\"100\">";
    }

    return $ret_value;
  }


  function column_destinations($item){

    //Build row actions
    $actions = array(
//      'edit'      => sprintf('<a href="?page=%s&action=%s&id=%s">Edit</a>',$_REQUEST['page'],'edit',$item['ID']),
      'edit2'      => sprintf('<a href="javascript:void(0)" class="quick_edit_show" id="destinations_%s">通知先編集</a>',$item['ID']),
    );

    $select_string = $this->build_select_string($item['destinations_values'],$item['ID']);
    $destinations = '<div id="text_select_destinations_'.$item['ID'].'">'
      . str_replace(',', '<br>', $item['destinations'])
      . '</div>';

    //Return the title contents
    return sprintf('%s<span style="color:silver; display: none;" id="edit_destinations_%s">%s<button type="button" class="quick_edit_update" id="button_destinations_%s">更新</button></span>%s',
      /*$1%s*/ $destinations /* $item['destinations'] */,
      /*$2%s*/ $item['ID'],
      /*$3%s*/ $select_string,
      /*$2%s*/ $item['ID'],
      /*$4%s*/ $this->row_actions($actions)
    );
  }

  function build_select_string($in_d_values, $in_id) {
    $my_websites = json_decode(get_option('WPNC_Plugin_Websites', 'none'), true);
    $selected_values = explode(',', $in_d_values);
    $output = '<div class="multiselect">';
    foreach ($my_websites as $website) {
      if (is_null($website['id'])) {
        continue;
      }
      if (array_search($website['id'], $selected_values)) {
        $selected = "checked";
      } else {
        $selected = " ";
      }
      $output .= '<label><input type="checkbox" name="select_destinations_'.$in_id.'" value="'.$website['id'].'" />'.$website['name'].'</label>'."\n";
    }
    $output .= "</div>";

/*
    // echo "<pre>";var_dump($selected_values); var_dump($my_websites);
    $output = '<select multiple id="select_destinations_'.$in_id.'" name="select_destinations_'.$in_id.'" class="multiselect" >';
    foreach ($my_websites as $website) {
      if (array_search($website['id'], $selected_values)) {
        $selected = "selected";
      } else {
        $selected = " ";
      }
      $output .= "<option value='".$website['id']."' $selected >".$website['name']."</option>";
    }
    $output .= "</select>";
*/
    return $output;

  }

/*  function column_destinations_values($item) {
    return sprintf('<div id="value_destinations_%s">%s</div>',
      $item['ID'],
      $item['destinations_value']
    );
  }*/


  /** ************************************************************************
     * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
     * is given special treatment when columns are processed. It ALWAYS needs to
     * have it's own method.
     * 
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/ $item['ID']                //The value of the checkbox should be the record's id
        );
    }

  function column_wp_post_title($item) {
    return sprintf(
      '<a href="post.php?post=%1$s&action=edit">%2$s</a>',
      $item['wp_postid'],
      $item['wp_post_title']
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
    function get_columns(){
        $columns = array(
            'cb'                    => '<input type="checkbox" />', //Render a checkbox instead of text
            #'ID'                     => 'ID',
            #'wp_postid'            => 'PostID',
            'wp_eyechatch_path_org' => '画像',
            'wp_post_title'         => 'タイトル',
            #'wp_post_content'       => '投稿内容',
            'post_date'             => '投稿日',
            'wp_tags'               => 'タグ',
            'post_status'           => '投稿ステータス',
            'notification_status'   => '通知ステータス',
            'destinations'          => '通知先サイト',
//            'create_date'         => 'Create Date',
//            'modify_date'         => 'Modify Date',
            'destinations_values'   => 'Destinations Values',
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
    function get_sortable_columns() {
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
            'send'    => '更新通知センターに送信',
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
    function process_bulk_action() {
    // var_dump($_REQUEST['notificationout']);
        
        //Detect when a bulk action is being triggered...
        if( 'delete'===$this->current_action() ) {
      $this->process_bulk_delete($_REQUEST['notificationout']);
            wp_die('Items deleted (or they would be if we had items to delete)!');
        } else if( 'send'===$this->current_action() ) {
      // var_dump($_REQUEST['notificationout']);
      $this->process_bulk_send($_REQUEST['notificationout']);
            // wp_die('Items are sent!');
        }
        
    }

  function process_bulk_delete($in_target_ids) {
    global $wpdb;

    if (!is_array($in_target_ids)) {
      $in_target_ids = array($in_target_ids);
    }


    global $wpdb;
    $table_prefix = WPNC_PREFIX;

    foreach ($in_target_ids as $rec_id) {
      $query = $wpdb->prepare(
        "delete from {$table_prefix}notifications_out where id = %s",
        $rec_id);
      $rows = $wpdb->get_results($query);
    }

    // redirect after deletion
    $redirect_to = admin_url('plugins.php?page=WPNC_PluginNotifcationsOut#deletion_done');
    wp_redirect($redirect_to, 302);
    exit;

  }

  function process_bulk_send($in_target_ids) {
    global $wpdb;
    $table_prefix = WPNC_PREFIX;

    $endpoint_uri = get_option('WPNC_Plugin_EndPointURI', 'none');
    $apikey = get_option('WPNC_Plugin_APIKey', 'none');
    if ($endpoint_uri == 'none' || $apikey == 'none') {
      echo "EndPoint URIと API Keyを正しく設定して下さい.";
      return null;
    }

    if (!is_array($in_target_ids)) {
      $target_ids = array ($in_target_ids);
    } else {
      $target_ids = $in_target_ids;
    }
    error_log("TARGETS:".print_r($target_ids,true));

    $show_alert = false;
    foreach ($target_ids as $rec_id) {
      $query = $wpdb->prepare(
        "select * from {$table_prefix}notifications_out where id = %s",
        $rec_id);
      $row = $wpdb->get_results($query);
      if (is_null($row)) {
        continue;
      }
      error_log(json_encode($row));
      $row_data = $row[0];
      /*
      $id                    = $row->id;
      $website_id            = $row->website_id;
      $wp_postid             = $row->wp_postid;
      $wp_post_title         = $row->wp_post_title;
      $wp_post_content       = $row->wp_post_content;
      $wp_tags               = $row->wp_tags;
      $wp_eyechatch_path_org = $row->wp_eyechatch_path_org;
      $post_date             = $row->post_date;
      $post_status           = $row->post_status;
      $notification_status   = $row->notification_status;
      $create_date           = $row->create_date;
      $modify_date           = $row->modify_date;
       */
      unset($row_data->id);

      // only send records with destinations(s)
      if (!isset($row_data->destinations_values) || empty($row_data->destinations_values)) {
        $show_alert = true;
        continue;
      }

      // data is sent one notification per one destination.
      $destinations = explode(',', $row_data->destinations_values);

      foreach ($destinations as $website_id) {
        error_log(json_encode($row_data));
        $row_data->website_id = $website_id;
        $notification_data = json_encode($row_data);
        $send_result = $this->send_notification($endpoint_uri, $apikey, $notification_data);
        error_log(json_encode($send_result));
      // var_dump($send_result);
      }

      $query = $wpdb->prepare(
        "delete from {$table_prefix}notifications_out where id = %s",
        $rec_id);
      $rows = $wpdb->get_results($query);

    }

    // redirect after deletion
    // $redirect_to = admin_url('plugins.php?page=WPNC_PluginNotifcationsOut#bulk_send_done');
    // wp_redirect($redirect_to, 302);
    // exit;

    if ($show_alert == true) {
      echo "<div class='error'><p>通知先が設定されていないレコードは送信されませんでした</p></div>";
    }
    return null;
  }

  function send_notification($in_endpoint_uri, $apikey, $in_notification_data) {
    $api_function = 'ApiNotifications/add.json';
    
    $curl = curl_init("$in_endpoint_uri/$api_function?apikey=$apikey");
    curl_setopt($curl,CURLOPT_POST, TRUE);
    curl_setopt($curl,CURLOPT_POSTFIELDS, $in_notification_data);
    curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, FALSE);  // オレオレ証明書対策
    curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, FALSE);  // 
    curl_setopt($curl,CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl,CURLOPT_COOKIEJAR,      'cookie');
    curl_setopt($curl,CURLOPT_COOKIEFILE,     'tmp');
    curl_setopt($curl,CURLOPT_FOLLOWLOCATION, TRUE); // Locationヘッダを追跡
    //curl_setopt($curl,CURLOPT_REFERER,        "REFERER");
    //curl_setopt($curl,CURLOPT_USERAGENT,      "USER_AGENT"); 

    $output = curl_exec($curl);

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
    function prepare_items() {
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
        $hidden = array('destinations_values');
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
    $query = "SELECT * FROM {$table_prefix}notifications_out order by post_date desc";
    $totalitems = $wpdb->query($query);
    $items = $wpdb->get_results($query);
    $data = array();
    foreach ($items as $item) {
      $rec = array();
      $rec['ID'] = $item->id;
      $rec['website_id'] = $item->website_id;
      $rec['wp_postid'] = $item->wp_postid;
      $rec['wp_post_title'] = $item->wp_post_title;
      $rec['wp_post_content'] = mb_substr(strip_tags($item->wp_post_content),0,10)."...";
      $rec['wp_tags'] = $item->wp_tags;
      $rec['wp_eyechatch_path_org'] = $item->wp_eyechatch_path_org;
      $rec['post_date'] = $item->post_date;
      $rec['post_status'] = $item->post_status;
      $rec['notification_status'] = $item->notification_status;
      $rec['destinations'] = $item->destinations;
      
      $rec['create_date'] = $item->create_date;
      $rec['modify_date'] = $item->modify_date;
      $rec['destinations_values'] = $item->destinations_values;

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
        function usort_reorder($a,$b){
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







