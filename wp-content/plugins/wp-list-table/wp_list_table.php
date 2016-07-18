<?php
/**
 * Plugin Name: WP List Table
 * Description: WP List Table for student
 * Author URI: http://jeromedumodizon.com/
 * Version: 1.0
 */
if (!class_exists( 'WP_List_Table' )) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Student_List_Table extends WP_List_Table {
	private $student_data;

	public function get_columns() {
		$columns = array(
			'title' => 'Student Name',
			'description'  => 'Description',
			'year' => 'Year',
			'section' => 'Section',
			'address' => 'Address',
			'student_id' => 'Student ID',
			'date' => 'Date'
		);
		return $columns;
	}

	public function prepare_items() {
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = array();
		$this->_column_headers = array($columns, $hidden, $sortable);
		$this->student_data = array();

		$args = array('post_type' => 'student', 'post_status' => 'publish', 'posts_per_page' => -1);
		$loop = new WP_Query( $args );
		while ( $loop->have_posts() ) : $loop->the_post();
			$this->student_data[] = array(
				'ID' => get_the_ID(),
				'title' => get_the_title(),
				'description' => get_the_content(),
				'year' => get_post_meta(get_the_ID(), 'student_year', true),
				'section' => get_post_meta(get_the_ID(), 'student_section', true),
				'address' => get_post_meta(get_the_ID(), 'student_address', true),
				'student_id' => get_post_meta(get_the_ID(), 'student_id', true),
				'date' => get_the_date()
			);
		endwhile;
		$this->items = $this->student_data;
	}

	function column_default( $item, $column_name ) {
		switch( $column_name ) { 
			case 'title':
			case 'description':
			case 'year':
			case 'section':
			case 'address':
			case 'student_id':
			case 'date':
				return $item[ $column_name ];
			break;
			default:
				return print_r( $item, true );
			break;
		}
	}
}

function student_menu_items() {
    add_menu_page( 'Student List', 'Student List', 'activate_plugins', 'student_list_page', 'render_student_list_page' );
}
add_action( 'admin_menu', 'student_menu_items' );

function render_student_list_page() {
	$myListTable = new Student_List_Table();
	echo '<div class="wrap"><h2>Student List</h2>'; 
	$myListTable->prepare_items(); 
	$myListTable->display();
	echo '</div>'; 
}