<?php
/*
Plugin Name: Insert This! Content Inserts
Plugin URI: 
Description: Intelligent Content Inserts.  Intelligently Manage and Organize Text Widget Type Content
Version: 1.3.3
Author: Karla Leibowitz
Author URI: http://www.karlakarla.com
License: GPL2
*/

/*  Copyright 2012  Karla Leibowitz  (email : http://www.karlakarla.com/contact/)

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
*/

	// Start class inserts_widget //

	class ici_inserts_widget extends WP_Widget {

	// Constructor //

		function ici_inserts_widget() {
		
			$widget_ops = array( 'classname' => 'ici_inserts_widget', 'description' => 'Displays Content Inserts Content' ); 
			// Widget Settings
			$control_ops = array( 'id_base' => 'ici_inserts_widget' ); 
			// Widget Control Settings
			$this->WP_Widget( 'ici_inserts_widget', 'Insert This', $widget_ops, $control_ops ); 
			// Create the widget
		}

		// Widget output to sidebar //
		function widget($args, $instance) {
			extract( $args );
			$title 		= apply_filters('widget_title', $instance['title']); // the widget title
			$inserts_class 	= $instance['inserts_class']; // the css class
			$inserts_id 	= $instance['inserts_id']; // the inserts to show
			$inserts_title_use = $instance['inserts_title_use'];//use the inserts title or the widget title

 			echo $before_widget;
			$front=false; /* for now */
			ici_output_inserts_content($inserts_class,$inserts_id,$inserts_title_use,$title,$front,$before_title,$after_title);
			echo $after_widget;
		}
		

		// Update Widget Settings //
		function update($new_instance, $old_instance) {
			$instance['title'] = strip_tags($new_instance['title']);
			$instance['inserts_class'] = strip_tags($new_instance['inserts_class']);
			$instance['inserts_id'] = $new_instance['inserts_id'];
			$instance['inserts_title_use']=$new_instance['inserts_title_use'];

			return $instance;
		}

		// Widget Control Panel to Enter the Widget Settings //
		function form($instance) {

		$defaults = array( 'title' => 'Inserts Display', 'inserts_class' => '', 'inserts_id' => '');
		$instance = wp_parse_args( (array) $instance, $defaults ); 
		?>
		<?/*var_dump($instance);*/?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>">Widget Title:</label>
		
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>'" type="text" value="<?php echo $instance['title']; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('inserts_class'); ?>"><?php _e('CSS class'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('inserts_class'); ?>" name="<?php echo $this->get_field_name('inserts_class'); ?>" type="text" value="<?php echo $instance['inserts_class']; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('inserts_id'); ?>">inserts to display:</label>
			<select id="<?php echo $this->get_field_id('inserts_id'); ?>" name="<?php echo $this->get_field_name('inserts_id'); ?>" class="widefat" style="width:100%;">
				<!-- get listing of inserts -->
		
				 <option value=""><?php echo esc_attr( __( 'Select inserts' ) ); ?></option> 
				 <?php 
				  global $post;
				  $args = array( 'post_type' => 'inserts' , 'numberposts'     => 0,);
				  $inserts = get_posts($args); /* could get large */
				  foreach ( $inserts as $blok ) {
				  	if ($blok->ID==$instance['inserts_id']) $selected=' selected="selected" ';else $selected="";
					$option = '<option '.$selected.' value="' . $blok->ID . '">';
					$option .= $blok->post_title;
					$option .= '</option>';
					echo $option;
				  }
					?>
			</select>
		</p>
		<?php $title_in_use=$instance['inserts_title_use']; ?>
		<p>
			<label for="<?php echo $this->get_field_id('inserts_title_use'); ?>"><?php _e('Show title?'); ?></label>
			<select id="<?php echo $this->get_field_id('inserts_title_use'); ?>" 
				name="<?php echo $this->get_field_name('inserts_title_use'); ?>" class="widefat" 
					style="width:100%;">
				<option value="n"  <? ici_select_this_title("n",$title_in_use) ?> >No Title</option>
				<option value="w"  <? ici_select_this_title("w",$title_in_use) ?> >Widget Title</option>
				<option value="b"  <? ici_select_this_title("b",$title_in_use) ?> >Inserts Title</option>	
			</select>
		</p>
		<?php }

}
// End class inserts_widget
// Now add it in
add_action('widgets_init', create_function('', 'return register_widget("ici_inserts_widget");'));
        
        
// some extra functions for the inserts widget
function ici_select_this_title($i,$u){
	if ($i == $u) echo ' selected="selected" ';
}

function ici_output_inserts_content ($inserts_class,$inserts_id,$inserts_title_use,$title,$front,
	$before_title,$after_title) {
	//echo "IS FRONT ", $inserts_id;
	if (!$front || is_front_page() ){
	
?>
	<div class="ici_insert <? echo $inserts_class; ?>" >
	<?php 
		$content_post = get_post($inserts_id);
		$content = $content_post->post_content;
		$content = apply_filters('the_content', $content);
		$content = str_replace(']]>', ']]&gt;', $content);
    	if ($content) {
					
			switch ($inserts_title_use) {
				case "w": echo $before_title . $title . $after_title; break;
				case "b": echo $before_title; the_title(); echo $after_title; break;
			}
		 echo $content; 
				
		} ?>
	 </div>
<?php
	}
}

function ici_short_inserts_func( $atts ) {
	extract( shortcode_atts( array(
		'css' => '',
		'id' => '',
		'use_title' => 'n', /* use no inserts title by default, can be "n", "w" or "b" */
		'title' => '',
		'front'=>false,
		'before_title' => '<h4>',
		'after_title' => '</h4>',
	), $atts ) );
	
	ob_start();
	ici_output_inserts_content ($css,$id,$use_title,$title,$front,
		$before_title,$after_title);
	$bc=ob_get_clean();
	return $bc;
}
add_shortcode( 'insert', 'ici_short_inserts_func' );      
add_shortcode( 'InsertThis', 'ici_short_inserts_func' );      



/* SET UP THE inserts */
// Add Taxonomy to use with inserts
function ici_insertstax_init() {
    // create a new taxonomy
    $labels = array(
    'name' => _x( 'Inserts Types', 'taxonomy general name' ),
    'singular_name' => _x( 'Inserts Type', 'taxonomy singular name' ),
    'search_items' =>  __( 'Search Types' ),
    'all_items' => __( 'All Types' ),
    'parent_item' => __( 'Parent Type' ),
    'parent_item_colon' => __( 'Parent Type:' ),
    'edit_item' => __( 'Edit Type' ), 
    'update_item' => __( 'Update Type' ),
    'add_new_item' => __( 'Add New Inserts Type' ),
    'new_item_name' => __( 'New Inserts Type Name' ),
    'menu_name' => __( 'Types' ),
  ); 	

    register_taxonomy(
    	'inserts_type',
        array(''),  /* post types that can use this */
         array(
		'hierarchical' => true,
            	'labels' => $labels,
        )
    );
}
add_action( 'init', 'ici_insertstax_init' );

// Add Another Taxonomy to use with inserts
function ici_insertstax_init_tags() {
    // create a new taxonomy
    $labels = array(
    'name' => _x( 'Insets Tags', 'taxonomy general name' ),
    'singular_name' => _x( 'Inserts Tag', 'taxonomy singular name' ),
    'search_items' =>  __( 'Search Tags' ),
    'all_items' => __( 'All Tags' ),
    'parent_item' => __( 'Parent Tag' ),
    'parent_item_colon' => __( 'Parent Tag:' ),
    'edit_item' => __( 'Edit Tag' ), 
    'update_item' => __( 'Update Tag' ),
    'add_new_item' => __( 'Add New Inserts Tag' ),
    'new_item_name' => __( 'New Inserts Tag Name' ),
    'menu_name' => __( 'Tags' ),
  ); 	

    register_taxonomy(
    	'inserts_tag',
        array(''),  /* post types that can use this */
         array(
		'hierarchical' => false,
            	'labels' => $labels,
        )
    );
}
add_action( 'init', 'ici_insertstax_init_tags' );

// Create custom type inserts
function ici_create_inserts_type() {
	register_post_type( 
		'inserts',
		array(
			'labels' => array(
				'name' => __( 'Inserts' ),
				'singular_name' => __( 'Insert' ),
				'add_new_item' => __('Add New Insert'),
				 'edit_item' => __('Edit Insert'),
				 'new_item' => __('New Insert'),
				 'view_item' => __('View Insert'),
				 'search_items' => __('Search Inserts'),	
				 'not_found' => __('No Inserts Found'),
				 'not_found_in_trash' => __('No Inserts Found'),	
					
			),
		'public' => true,
		'has_archive' => true,
		'map_meta_cap' => true,
		'menu_position' => 20,	
		'menu_icon' => plugins_url( 'images/light-bulb.png' , __FILE__ ),  
		'supports' => array( 'title','editor', 'revisions', 'author' ),
		'taxonomies' => array( 'inserts_type','inserts_tag'),
		'exclude_from_search'=>true,
		)
	);
}
add_action( 'init', 'ici_create_inserts_type' );

/* don't show inserts to anyone not an editor or admin */
function ici_remove_menu_items() {
    if( !current_user_can( 'edit_others_posts' )):
        remove_menu_page( 'edit.php?post_type=inserts' );
    endif;
}
add_action( 'admin_menu', 'ici_remove_menu_items' );

// custom columns (thanks to Justin Tadlock) on Inserts toc page
add_filter("manage_edit-inserts_columns", "ici_inserts_columns");
add_action("manage_inserts_posts_custom_column", "ici_inserts_custom_columns",10,2);

function ici_inserts_columns($columns){
    $columns = array(
        "cb" => "<input type=\"checkbox\" />",
        "title" => __("Title"),
		"id" => __("Inserts Id"),
        "inserts_type" => __("Inserts Type"),
		"inserts_tag" => __("Inserts Tags"),
		"author" => __("Author"),
		"date" => __("Date"),
    );
    return $columns;
}

function ici_inserts_custom_columns($column,$post_id) {
    global $post;
        switch ($column) {
        case 'inserts_type':
			$terms =  get_the_terms($post_id, 'inserts_type');

			if (!empty($terms)){
				$out=array();
				foreach ($terms as $term) {
					$out[]=sprintf('<a href="%s">%s</a>',
						esc_url(add_query_arg( 
							array('post_type'=>$post->post_type,'inserts_type'=>$term->slug),
							'edit.php')),
						esc_html(sanitize_term_field(
							'name',$term->name,$term->term_id,'inserts_type','display')
							)
						);
					}
				//var_dump($out);
				echo join(',',$out);
				}
			else {
				_e('-----');
				}
			break;
		case 'inserts_tag':
			$terms =  get_the_terms($post_id, 'inserts_tag');

			if (!empty($terms)){
				$out=array();
				foreach ($terms as $term) {
					$out[]=sprintf('<a href="%s">%s</a>',
						esc_url(add_query_arg( 
							array('post_type'=>$post->post_type,'inserts_tag'=>$term->slug),
							'edit.php')),
						esc_html(sanitize_term_field(
							'name',$term->name,$term->term_id,'inserts_tag','display')
							)
						);
					}
				//var_dump($out);
				echo join(',',$out);
				}
			else {
				_e('-----');
				}
			break;
		 case 'id' :

			printf( __( 'Id = %s' ), $post_id );

			break;
		default:
			break;
		}
}


/* add filters for class and tag (from stack exchange and Charles Alexander Laci and me) */
/* http://wordpress.stackexchange.com/questions/578/adding-a-taxonomy-filter-to-admin-list-for-a-custom-post-type */

function ici_convert_id_to_term_name($query,$post_type,$taxonomy){
	global $pagenow;
    $q_vars = &$query->query_vars;
    if ($pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == $post_type && isset($q_vars[$taxonomy]) && is_numeric($q_vars[$taxonomy]) && $q_vars[$taxonomy] != 0) {
        $term = get_term_by('id', $q_vars[$taxonomy], $taxonomy);
        $q_vars[$taxonomy] = $term->slug;
    }
}

function ici_set_type_restriction($post_type,$taxonomy) {
    global $typenow;
    if ($typenow == $post_type) {
        $selected = isset($_GET[$taxonomy]) ? $_GET[$taxonomy] : '';
        $info_taxonomy = get_taxonomy($taxonomy);
        wp_dropdown_categories(array(
            'show_option_all' => __("Show All {$info_taxonomy->label}"),
            'taxonomy' => $taxonomy,
            'name' => $taxonomy,
            'orderby' => 'name',
            'selected' => $selected,
            'show_count' => true,
            'hide_empty' => true,
        ));
    };
}

function ici_restrict_inserts_by_class() {
    $post_type = 'inserts'; // change HERE
    $taxonomy = 'inserts_class'; // change HERE
	ici_set_type_restriction($post_type,$taxonomy);
}
add_action('restrict_manage_posts', 'ici_restrict_inserts_by_class');

function ici_convert_id_to_inserts_class_name($query) {
    $post_type = 'inserts'; // change HERE
    $taxonomy = 'inserts_class'; // change HERE
	ici_convert_id_to_term_name($query,$post_type,$taxonomy);
}
add_filter('parse_query', 'ici_convert_id_to_inserts_class_name');

function ici_restrict_inserts_by_tags() {
    $post_type = 'inserts'; // change HERE
    $taxonomy = 'inserts_tag'; // change HERE
	ici_set_type_restriction($post_type,$taxonomy);
}
add_action('restrict_manage_posts', 'ici_restrict_inserts_by_tags');

function ici_convert_id_to_inserts_tag_name($query) {
    $post_type = 'inserts'; // change HERE
    $taxonomy = 'inserts_tag'; // change HERE
	ici_convert_id_to_term_name($query,$post_type,$taxonomy);
}
add_filter('parse_query', 'ici_convert_id_to_inserts_tag_name');


?>