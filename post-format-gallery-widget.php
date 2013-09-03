<?php
/*
Plugin Name: Post Format Gallery Widget
Plugin URI: http://github.com/eduardozulian/post-format-gallery-widget
Description: Display images from your galleries that are saved under the Gallery post format.
Version: 0.1
Author: Eduardo Zulian
Author URI: http://flutuante.com.br
License: GPL2

Copyright 2013 Eduardo Zulian

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

/**
 * Load translated strings
 */
function pfgw_load_textdomain() {

	load_plugin_textdomain( 'post-format-gallery-widget', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	
}

add_action( 'plugins_loaded', 'pfgw_load_textdomain' );

/**
 * Register the widget
 */
function pfgw_register_widget() {

	register_widget( 'Post_Format_Gallery_Widget' );
	
}

add_action( 'widgets_init', 'pfgw_register_widget' );

/**
 * Post Format Gallery Widget
 * Display images from your galleries that are saved under the Gallery post format.
 *
 */
class Post_Format_Gallery_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct(
	 		'post-format-gallery-widget',
			__( 'Post Format Gallery Widget', 'post-format-gallery-widget' ),
			array(
				'classname' => 'widget_post_format_gallery',
				'description' => __( 'Show images of your posts inside the post format "Gallery" in your widget area.', 'post-format-gallery-widget' )
			)
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	function widget( $args, $instance ) {	 
		
		extract($args);
		
		if ( isset( $instance['post'] ) && $instance['post'] != -1 ) {
		
			$post_id = (int) $instance['post'];
			$gallery_style = $instance['gallery-style'] ? true : false;
			$image_size = isset( $instance['image-size'] ) ? strip_tags( $instance['image-size'] ) : 'thumbnail';
			$number_images = $instance['number-images'];
			$random_images = $instance['random-images'] ? true : false;
			$number_columns = (int) $instance['number-columns'];
		
			// Retrieve all galleries of this post as arrays
			$galleries = get_post_galleries( $post_id, false );
			
			if ( ! empty ( $galleries ) ) {
				
				$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
				
				echo $before_widget;
				
				if ( ! empty ( $title ) )
					echo $before_title . $title . $after_title;
			
				// The IDs from attachments whitin a gallery
				$gallery_post_ids = array();
				
				// Loop through all the galleries and store its IDs
				foreach( $galleries as $gallery ) {
					if ( array_key_exists( 'ids', $gallery ) ) {
						$gallery_ids = explode( ',', $gallery['ids'] );			
						$gallery_post_ids = array_merge( $gallery_post_ids, $gallery_ids );
					}
				}
				
				// Remove repeated IDs
				$gallery_post_ids = array_unique( $gallery_post_ids );
				
				// Limit the array when a number of images is set
				if ( $number_images > 0 )
					$gallery_post_ids = array_slice( $gallery_post_ids, 0, $number_images );

				// Randomize images
				if ( $random_images === true )
					shuffle( $gallery_post_ids );
					
				if ( ! empty ( $gallery_post_ids ) ) {
				
					// Will it use the default WordPress gallery style or not?
					if ( $gallery_style === true ) {
						echo do_shortcode( '[gallery ids="'. implode( ',', $gallery_post_ids) . '" columns="' . $number_columns . '" size="' . $image_size . '" captiontag="none"]' );
					}
					else {
						// Search for attachments that are part of a gallery
						$query_images_args = array( 
							'post_type' 	=> 'attachment',
							'post_status' 	=> 'inherit',
							'post__in' 		=> $gallery_post_ids,
							'orderby'		=> 'post__in'
						);
						
						/*
						$query_images_args = array(
							'post_type' => 'attachment',
							'post_mime_type' => 'image',
							'post_status' => 'inherit',
							'orderby' => $args['orderby'],
							'order' => $args['order'],
							'posts_per_page' => -1,
						); */
				
						
						$query_images = new WP_Query( $query_images_args );
		
						if ( $query_images->have_posts() ) : while( $query_images->have_posts() ) : $query_images->the_post();
								
							global $post;
							
							$image = wp_get_attachment_image_src( get_the_ID(), $image_size );
							$image['ID'] = get_the_ID();
							$image['title'] = get_the_title();
							$image['caption'] = $post->post_excerpt;
							$image['parent'] = $post->post_parent;
							$images[] = $image;
							
							echo '<img src="'. $image[0] . '" />';
								
						endwhile; endif;
					}
				}
				
				echo $after_widget;
	
				wp_reset_postdata();
			
			}
		}
	}
	

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['post'] = (int)( $new_instance['post'] );
		$instance['image-size'] = strip_tags( $new_instance['image-size'] );
		$instance['number-images'] = absint ( $new_instance['number-images'] );
		$instance['random-images'] = $new_instance['random-images'] ? true : false;
		$instance['gallery-style'] = $new_instance['gallery-style'] ? true : false;
		$instance['number-columns'] = (int)( $new_instance['number-columns'] );
		
		return $instance;
	}

	function form( $instance ) {
	
		// Check if theme has support to post format gallery
		if ( current_theme_supports( 'post-formats' ) ) {  
		    $post_formats = get_theme_support( 'post-formats' );  
		    
		    if ( is_array( $post_formats[0] ) && ! in_array( 'gallery', $post_formats[0] ) ) {
				_e( 'Your theme does not support the Gallery post format. Please <a href="http://codex.wordpress.org/Post_Formats#Adding_Theme_Support">add this support</a> so you can choose your posts.', 'post-format-gallery-widget' );
				return;
		    }  
		} 
		
		$title = isset( $instance['title'] ) ? strip_tags( $instance['title'] ) : '';
		$p = isset( $instance['post'] ) ? (int) $instance['post'] : 0;
		$image_size = isset( $instance['image-size'] ) ? strip_tags( $instance['image-size'] ) : 'thumbnail';
		$number_images = isset( $instance['number-images'] ) ? absint( $instance['number-images'] ) : 0;
		$random_images = $instance['random-images'] ? true : false;
		$gallery_style = $instance['gallery-style'] ? true : false;
		$number_columns = isset( $instance['number-columns'] ) ? (int) $instance['number-columns'] : 1;
		?>
		
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', 'post-format-gallery-widget' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'post' ); ?>"><?php _e( 'Post:', 'post-format-gallery-widget' ); ?></label>
			<?php
	        $args = array( 
	        	'post_format' => 'post-format-gallery'
	        );
	
	        $galleries = get_posts( $args );

	        if ( ! empty( $galleries ) ) : ?>
	            <select class="widefat" name="<?php echo $this->get_field_name( 'post' ); ?>" id="<?php echo $this->get_field_id( 'post' ); ?>">
	            	<option value="-1"><?php _e( 'Select a post', 'post-format-gallery-widget' ); ?></option>
	            	<?php foreach ( $galleries as $gallery ) : ?>
	            	<option value="<?php echo $gallery->ID; ?>" <?php selected( $p, $gallery->ID ); ?>><?php echo $gallery->post_title; ?></option>
	            	<?php endforeach; ?>
	            </select>
	        <?php endif; ?>
        </p>
		<p>
			<label for="<?php echo $this->get_field_id( 'image-size' ); ?>"><?php _e( 'Image size:', 'post-format-gallery-widget' ); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'image-size' ); ?>" name="<?php echo $this->get_field_name( 'image-size' ); ?>">
				<?php
				$all_image_sizes = $this->_get_all_image_sizes();
				foreach ( $all_image_sizes as $key => $value ) :
					$image_dimensions = $value['width'] . 'x' . $value['height']; ?>
					<option value="<?php echo $key; ?>" <?php selected( $image_size, $key ); ?>><?php echo $key; ?> (<?php echo $image_dimensions; ?>)</option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'number-images' ); ?>"><?php _e( 'Number of images to show', 'post-format-gallery-widget' ); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'number-images' ); ?>" name="<?php echo $this->get_field_name( 'number-images' ); ?>" type="text" size="1" value="<?php echo esc_attr( $number_images ); ?>" /><br />
			<small class="description"><?php _e( 'Enter 0 for all images', 'post-format-gallery-widget' ); ?></small>
		</p>
		<p>
			<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id( 'random-images' ); ?>" name="<?php echo $this->get_field_name( 'random-images' ); ?>"<?php checked( $random_images ) ?> />
			<label for="<?php echo $this->get_field_id( 'random-images' ); ?>"><?php _e( 'Randomize images', 'post-format-gallery-widget' ); ?></label>
		</p>
		<p>
			<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id( 'gallery-style' ); ?>" name="<?php echo $this->get_field_name( 'gallery-style' ); ?>"<?php checked( $gallery_style ) ?> />
			<label for="<?php echo $this->get_field_id( 'gallery-style' ); ?>"><?php _e( 'Use the WordPress default gallery style', 'post-format-gallery-widget' ); ?></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'number-columns' ); ?>"><?php _e( 'Number of columns:', 'post-format-gallery-widget' ); ?></label>
			<select id="<?php echo $this->get_field_id( 'number-columns' ); ?>" name="<?php echo $this->get_field_name( 'number-columns' ); ?>">
				<?php
				for ( $columns = 1; $columns < 10; $columns++ ) :
				?>
					<option value="<?php echo $columns; ?>" <?php selected( $number_columns, $columns ); ?>><?php echo $columns; ?></option>
				<?php endfor; ?>
			</select>
		</p>
	<?php
	}
	
	/**
	 * Get all the registered image sizes along with their dimensions
	 *
	 * @global array $_wp_additional_image_sizes
	 *
	 * @link http://core.trac.wordpress.org/ticket/18947 Reference ticket
	 * @return array $image_sizes The image sizes
	 */
	function _get_all_image_sizes() {
		global $_wp_additional_image_sizes;

		$default_image_sizes = array( 'thumbnail', 'medium', 'large' );
		 
		foreach ( $default_image_sizes as $size ) {
			$image_sizes[$size]['width']	= intval( get_option( "{$size}_size_w") );
			$image_sizes[$size]['height'] = intval( get_option( "{$size}_size_h") );
			$image_sizes[$size]['crop']	= get_option( "{$size}_crop" ) ? get_option( "{$size}_crop" ) : false;
		}
		
		if ( isset( $_wp_additional_image_sizes ) && count( $_wp_additional_image_sizes ) )
			$image_sizes = array_merge( $image_sizes, $_wp_additional_image_sizes );
			
		return $image_sizes;
	}
}
?>