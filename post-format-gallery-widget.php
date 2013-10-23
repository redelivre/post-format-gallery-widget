<?php
/*
Plugin Name: Post Format Gallery Widget
Plugin URI: http://github.com/eduardozulian/post-format-gallery-widget
Description: Display images from your galleries saved under the post format Gallery.
Version: 1.1
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
 * Enqueue scripts
 */ 
function pfgw_enqueue_scripts( $hook ) {
	
	if ( $hook === 'widgets.php' )
		wp_enqueue_script( 'post-format-gallery-widget', plugins_url( 'js/post-format-gallery-widget.js', __FILE__ ), array( 'jquery' ) );
		
}

add_action( 'admin_enqueue_scripts', 'pfgw_enqueue_scripts' );

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
				'description' => __( 'Display images from your galleries saved under the Gallery post format.', 'post-format-gallery-widget' )
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
			$use_gallery_style = $instance['use-gallery-style'] ? true : false;
			$image_size = isset( $instance['image-size'] ) ? strip_tags( $instance['image-size'] ) : 'thumbnail';
			$image_link = isset( $instance['image-link'] ) ? $instance['image-link'] : 'file';
			$number_images = $instance['number-images'];
			$random_images = $instance['random-images'] ? true : false;
			$show_captions = $instance['show-captions'] ? true : false;
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
				
				// Shuffle images from all the galleries inside the post
				if ( $random_images === true )
					shuffle( $gallery_post_ids );
				
				// Limit the array when a number of images is set
				if ( $number_images > 0 )
					$gallery_post_ids = array_slice( $gallery_post_ids, 0, $number_images );
					
				if ( ! empty ( $gallery_post_ids ) ) {					
					
					static $instance = 0;
					$instance++;
					
					$_attachments = get_posts( array(
						'include' 			=> implode( ',', $gallery_post_ids ),
						'post_status' 		=> 'inherit',
						'post_type' 		=> 'attachment',
						'post_mime_type'	=> 'image'
					) );

					$attachments = array();
					
					foreach ( $_attachments as $key => $val ) {
						$attachments[$val->ID] = $_attachments[$key];
					}
				
	
					if ( empty( $attachments ) )
						return '';
					
					/* Using WP column function */ 
					$number_columns = intval( $number_columns );
					$itemwidth = $number_columns > 0 ? floor( 100 / $number_columns ) : 100;
					$float = is_rtl() ? 'right' : 'left';
				
					$selector = "gallery-{$instance}";
					
					$gallery_style = $gallery_div = '';
					
					if ( $use_gallery_style === true ) {
						
						$gallery_style = "
						<style type='text/css'>
							.widget_post_format_gallery #{$selector} {
								margin: auto;
							}
							
							.widget_post_format_gallery #{$selector} .gallery-item {
								float: {$float};
								margin-top: 10px;
								text-align: center;
								width: {$itemwidth}%;
							}
							
							.widget_post_format_gallery #{$selector} .gallery-caption {
								margin-left: 0;
							}
						</style>";
					}
					
					$gallery_classes = apply_filters( 'pfgw_gallery_classes', array( 'pfgw-gallery', 'gallery', 'gallery-columns-' . $number_columns, 'gallery-size-' . $image_size ) );
					
					$gallery_div = '<div id="' . $selector . '"' . ( ! empty( $gallery_classes ) ? ' class="'. implode( ' ', $gallery_classes ) . '"' : '' ) . '>';
					
					$output = $gallery_style . "\n\t\t" . $gallery_div;
				
					$i = 0;
					
					foreach ( $attachments as $id => $attachment ) {
					
						// Link target
						switch ( $image_link ) {
							
							case 'file' :
								$image_output = wp_get_attachment_link( $id, $image_size, false, false );
							break;
							
							case 'attachment' :
								$image_output = wp_get_attachment_link( $id, $image_size, true, false );
							break;
							
							case 'post' :
								$image_output = '<a href="' . get_permalink( $post_id ) . '" title="' . get_the_title( $id ) . '">' . wp_get_attachment_image( $id, $image_size, false ) . '</a>';
							break;
							
							case 'none' :
								$image_output = wp_get_attachment_image( $id, $image_size, false );
							break;
							
						}
				
						$image_meta  = wp_get_attachment_metadata( $id );
				
						$orientation = '';
						if ( isset( $image_meta['height'], $image_meta['width'] ) )
							$orientation = ( $image_meta['height'] > $image_meta['width'] ) ? 'portrait' : 'landscape';
				
						$output .= "<dl class='gallery-item'>";
						$output .= "
							<dt class='gallery-icon {$orientation}'>
								$image_output
							</dt>";
						if ( $show_captions === true && trim( $attachment->post_excerpt ) ) {
							$output .= "
								<dd class='wp-caption-text gallery-caption'>
								" . wptexturize( $attachment->post_excerpt ) . "
								</dd>";
						}
						$output .= "</dl>";
						if ( $number_columns > 0 && ++$i % $number_columns == 0 )
							$output .= '<br style="clear: both" />';
					}
				
					$output .= "
							<br style='clear: both;' />
						</div>\n";
						
					echo $output;
					
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
		$instance['image-size'] = $new_instance['image-size'];
		$instance['image-link'] = $new_instance['image-link'];
		$instance['number-images'] = absint ( $new_instance['number-images'] );
		$instance['random-images'] = $new_instance['random-images'] ? true : false;
		$instance['show-captions'] = $new_instance['show-captions'] ? true : false;
		$instance['use-gallery-style'] = $new_instance['use-gallery-style'] ? true : false;
		$instance['number-columns'] = (int)( $new_instance['number-columns'] );
		
		return $instance;
	}

	function form( $instance ) {
	
		// Check if theme supports post format gallery
		if ( current_theme_supports( 'post-formats' ) ) {  
		    $post_formats = get_theme_support( 'post-formats' );  
		    
		    if ( is_array( $post_formats[0] ) && ! in_array( 'gallery', $post_formats[0] ) ) {
				_e( 'Your theme does not support the Gallery post format. Please change your theme or <a href="http://codex.wordpress.org/Post_Formats#Adding_Theme_Support">add this support</a> to your current one so you can choose your posts.', 'post-format-gallery-widget' );
				return;
		    }  
		} 
		
		$title = isset( $instance['title'] ) ? strip_tags( $instance['title'] ) : '';
		$p = isset( $instance['post'] ) ? (int) $instance['post'] : 0;
		$image_size = isset( $instance['image-size'] ) ? strip_tags( $instance['image-size'] ) : 'thumbnail';
		$image_link = isset( $instance['image-link'] ) ? $instance['image-link'] : 'file';
		$number_images = isset( $instance['number-images'] ) ? absint( $instance['number-images'] ) : 0;
		$random_images = ( isset( $instance['random-images'] ) && ( $instance['random-images'] ) ) ? true : false;
		$show_captions = ( isset( $instance['show-captions'] ) && ( $instance['show-captions'] ) ) ? true : false;
		$use_gallery_style = ( isset( $instance['use-gallery-style'] ) && ( $instance['use-gallery-style'] ) ) ? true : false;
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
	        	'post_status'	=> 'publish',
	        	'post_type'		=> apply_filters( 'pfgw_post_types', array( 'post' ) ),
	        	'post_format' 	=> 'post-format-gallery',
	        );
	
	        $galleries = new WP_Query( $args );
	        
	        $output = '<select class="widefat" name="' . $this->get_field_name( 'post' ) . '" id="' . $this->get_field_id( 'post' ) . '">';
	        $output .= '<option value="-1">' . __( 'Select a post', 'post-format-gallery-widget' ) . '</option>';
	        
	        if ( $galleries->have_posts() ) :
	        	while ( $galleries->have_posts() ) : $galleries->the_post();
	        		$output .= '<option value="' . get_the_ID() . '"' . selected( $p, get_the_ID(), false ) . '>'. get_the_title() . '</option>';
				endwhile;
	            $output .= '</select>';
	        else :
	        	$output .= '</select>';
	        	$output .= '<br/><small class="description">' . __( 'You don\'t have any posts under the Gallery post format. Please add one.', 'post-format-gallery-widget') . '</small>';
	        endif;
	        
	        echo $output;
	        ?>
        </p>
		<p>
			<label for="<?php echo $this->get_field_id( 'image-size' ); ?>"><?php _e( 'Image size:', 'post-format-gallery-widget' ); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'image-size' ); ?>" name="<?php echo $this->get_field_name( 'image-size' ); ?>">
				<?php
				$all_image_sizes = $this->pfgw_get_all_image_sizes();
				foreach ( $all_image_sizes as $key => $value ) :
					$image_dimensions = $value['width'] . 'x' . $value['height']; ?>
					<option value="<?php echo $key; ?>" <?php selected( $image_size, $key ); ?>><?php echo $key; ?> (<?php echo $image_dimensions; ?>)</option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'number-images' ); ?>"><?php _e( 'Number of images to show:', 'post-format-gallery-widget' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'number-images' ); ?>" name="<?php echo $this->get_field_name( 'number-images' ); ?>" type="text" size="1" value="<?php echo esc_attr( $number_images ); ?>" />
			<br />
			<small class="description"><?php _e( 'Enter 0 for all images', 'post-format-gallery-widget' ); ?></small>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'image-link' ); ?>"><?php _e( 'Image links to:', 'post-format-gallery-widget' ); ?></label>
			<?php
			$image_link_options = apply_filters( 'pfgw_image_link_options', array (
				__( 'File', 'post-format-gallery-widget' ) 			=> 'file',
				__( 'Attachment', 'post-format-gallery-widget' ) 	=> 'attachment',
				__( 'Post', 'post-format-gallery-widget' ) 			=> 'post',
				__( 'None', 'post-format-gallery-widget' ) 			=> 'none'
			) );
			?>
			<select class="widefat" id="<?php echo $this->get_field_id( 'image-link' ); ?>" name="<?php echo $this->get_field_name( 'image-link' ); ?>">
				<?php foreach ( $image_link_options as $image_link_name => $image_link_value ) : ?>
					<option value="<?php echo $image_link_value; ?>" <?php selected( $image_link, $image_link_value ); ?>><?php echo $image_link_name; ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id( 'random-images' ); ?>" name="<?php echo $this->get_field_name( 'random-images' ); ?>"<?php checked( $random_images ) ?> />
			<label for="<?php echo $this->get_field_id( 'random-images' ); ?>"><?php _e( 'Randomize images', 'post-format-gallery-widget' ); ?></label>
		</p>
		<p>
			<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id( 'show-captions' ); ?>" name="<?php echo $this->get_field_name( 'show-captions' ); ?>"<?php checked( $show_captions ) ?> />
			<label for="<?php echo $this->get_field_id( 'show-captions' ); ?>"><?php _e( 'Show captions', 'post-format-gallery-widget' ); ?></label>
		</p>
		<p>
			<input class="wp-use-gallery-style-checkbox" type="checkbox" id="<?php echo $this->get_field_id( 'use-gallery-style' ); ?>" name="<?php echo $this->get_field_name( 'use-gallery-style' ); ?>"<?php checked( $use_gallery_style ) ?> />
			<label for="<?php echo $this->get_field_id( 'use-gallery-style' ); ?>"><?php _e( 'Use WordPress default gallery style', 'post-format-gallery-widget' ); ?></label>
		</p>
		<div class="wp-use-gallery-style-options">
		<p>
			<label for="<?php echo $this->get_field_id( 'number-columns' ); ?>"><?php _e( 'Number of columns:', 'post-format-gallery-widget' ); ?></label>
			<select id="<?php echo $this->get_field_id( 'number-columns' ); ?>" name="<?php echo $this->get_field_name( 'number-columns' ); ?>">
				<?php for ( $i = 1; $i < 10; $i++ ) : ?>
				<option value="<?php echo $i; ?>" <?php selected( $number_columns, $i ); ?>><?php echo $i; ?></option>
				<?php endfor; ?>
			</select>
		</p>
		</div>
		
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
	function pfgw_get_all_image_sizes() {
		global $_wp_additional_image_sizes;

		$default_image_sizes = array( 'thumbnail', 'medium', 'large' );
		 
		foreach ( $default_image_sizes as $image_size ) {
			$image_sizes[$image_size]['width']	= intval( get_option( "{$image_size}_size_w") );
			$image_sizes[$image_size]['height'] = intval( get_option( "{$image_size}_size_h") );
			$image_sizes[$image_size]['crop']	= get_option( "{$image_size}_crop" ) ? get_option( "{$image_size}_crop" ) : false;
		}
		
		if ( isset( $_wp_additional_image_sizes ) && count( $_wp_additional_image_sizes ) )
			$image_sizes = array_merge( $image_sizes, $_wp_additional_image_sizes );
			
		return $image_sizes;
	}
}
?>