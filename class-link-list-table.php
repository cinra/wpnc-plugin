<?php
/*
 * For Notifications In
 */

class Link_List_Table_Out extends WP_List_Table {

	/**
	 * Constructor, we override the parent to pass our own arguments
	 * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
	 */
	 function __construct() {
		 parent::__construct( array(
		'singular'=> 'wp_notification_out', //Singular label
		'plural' => 'wp_notifications_out', //plural label, also this well be one of the table css class
		'ajax'	=> false //We won't support Ajax for this table
		) );
	 }

	function extra_tablenav( $which ) {
		if ( $which == "top" ){
			//The code that goes before the table is here
			echo "Hello, I'm before the table";
		}

		if ( $which == "bottom" ){
			//The code that goes after the table is there
			echo "Hi, I'm after the table";
		}
	}

	function get_columns() {
		return $columns= array(
			'col_id'=>__('ID'),
			'col_wp_postid'=>__('PostID'),
			'col_wp_post_title'=>__('Title'),
			'col_wp_post_content'=>__('Body'),
			'col_wp_tags'=>__('Tags'),
			'col_wp_eyechatch_path_org'=>__('Thumbnail'),
			'col_wp_post_status'=>__('Post Status'),
			'col_notification_status'=>__('Notification Status'),
			'col_post_date'=>__('Post Date'),
			'col_create_date'=>__('Create Date'),
			'col_modify_date'=>__('Modify Date'),

		);
	}

	public function get_sortable_columns() {
		return $sortable = array(
			'col_wp_postid'=>'link_id',
			'col_wp_post_title'=>'link_title',
			'col_post_date'=>'link_post_date',
			'col_create_date'=>'link_create_date',
			'col_modify_date'=>'link_modify_date',
		);
	}
function prepare_items() {
	global $wpdb, $_wp_column_headers;
	$screen = get_current_screen();

	/* -- Preparing your query -- */
        $query = "SELECT * FROM notifications_out";

	/* -- Ordering parameters -- */
	    //Parameters that are going to be used to order the result
	    $orderby = !empty($_GET["orderby"]) ? mysql_real_escape_string($_GET["orderby"]) : 'ASC';
	    $order = !empty($_GET["order"]) ? mysql_real_escape_string($_GET["order"]) : '';
	    if(!empty($orderby) & !empty($order)){ $query.=' ORDER BY '.$orderby.' '.$order; }

	/* -- Pagination parameters -- */
        //Number of elements in your table?
        $totalitems = $wpdb->query($query); //return the total number of affected rows
        //How many to display per page?
        $perpage = 5;
        //Which page is this?
        $paged = !empty($_GET["paged"]) ? mysql_real_escape_string($_GET["paged"]) : '';
        //Page Number
        if(empty($paged) || !is_numeric($paged) || $paged<=0 ){ $paged=1; }
        //How many pages do we have in total?
        $totalpages = ceil($totalitems/$perpage);
        //adjust the query to take pagination into account
	    if(!empty($paged) && !empty($perpage)){
		    $offset=($paged-1)*$perpage;
    		$query.=' LIMIT '.(int)$offset.','.(int)$perpage;
	    }

	/* -- Register the pagination -- */
		$this->set_pagination_args( array(
			"total_items" => $totalitems,
			"total_pages" => $totalpages,
			"per_page" => $perpage,
		) );
		//The pagination links are automatically built according to those parameters

	/* -- Register the Columns -- */
		$columns = $this->get_columns();
		$_wp_column_headers[$screen->id]=$columns;

	/* -- Fetch the items -- */
		$this->items = $wpdb->get_results($query);

}

function display_rows() {

	//Get the records registered in the prepare_items method
	$records = $this->items;

	//Get the columns registered in the get_columns and get_sortable_columns methods
	list( $columns, $hidden ) = $this->get_column_info();
	echo "<pre>";
	var_dump($this->get_column_info());

	//Loop for each record
	if(!empty($records)){foreach($records as $rec){

		//Open the line
        // echo '<tr id="record_'.$rec->link_id.'">';
		foreach ( $columns as $column_name => $column_display_name ) {
				echo "$column_name => $column_display_name <BR>";

			//Style attributes for each col
			$class = "class='$column_name column-$column_name'";
			$style = "";
			if ( in_array( $column_name, $hidden ) ) $style = ' style="display:none;"';
			$attributes = $class . $style;

			//edit link
			$editlink  = '/wp-admin/link.php?action=edit&link_id='.(int)$rec->link_id;

			//Display the cell
			switch ( $column_name ) {
				case "col_link_id":	echo '< td '.$attributes.'>'.stripslashes($rec->link_id).'< /td>';	break;
				case "col_link_name": echo '< td '.$attributes.'>'.stripslashes($rec->link_name).'< /td>'; break;
				case "col_link_url": echo '< td '.$attributes.'>'.stripslashes($rec->link_url).'< /td>'; break;
				case "col_link_description": echo '< td '.$attributes.'>'.$rec->link_description.'< /td>'; break;
				case "col_link_visible": echo '< td '.$attributes.'>'.$rec->link_visible.'< /td>'; break;
			}
		}

		//Close the line
		echo'</tr>';
	}}
}

}
