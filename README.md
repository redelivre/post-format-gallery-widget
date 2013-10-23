# Post Format Gallery Widget #
**Contributors:** eduardozulian  
**Donate link:** https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=H28V8F5PHSZHA  
**Tags:** widget, sidebar, gallery, post format, post format gallery, widget, images  
**Requires at least:** 3.6  
**Tested up to:** 3.6  
**Stable tag:** 1.1  
**License:** GPLv2 or later  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html  

Display in a widget images from your galleries saved under the post format Gallery.

## Description ##

The Post Format Gallery Widget plugin creates the possibility for you to display images from your galleries inside posts saved under the format 'Gallery'. You can select the number of images to display, the size of these images, shuffle their orders, the target of links (image file, permalink to attachment, permalink to post or maybe no linking), show captions and use one copy of the basic WordPress style for galleries, which will allow you to set up easily a number of columns.

**Important**: your theme must support the [Gallery post format](http://codex.wordpress.org/Post_Formats#Adding_Theme_Support), otherwise it won't work.  

## Installation ##

1. Upload `post-format-gallery-widget` directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to 'Widgets' menu and drag it to your sidebar

## Frequently Asked Questions ##

### The widget only shows this message: "Your theme does not support the Gallery post format. Please add this support so you can choose your posts." ###

Yes, that's it. You have to [enable support for the gallery post format](http://codex.wordpress.org/Post_Formats#Adding_Theme_Support) on your theme. This plugin will only search for galleries inside a post saved under the post format Gallery.


### Can I add / remove / change gallery classes? ###

Yes. Just use the `pfgw_gallery_classes` filter in your `functions.php` file:

```
/**
 * Add a new class to the gallery container
 *
 * @param array $classes 
 */
function mytheme_change_pfgw_classes( $classes ) {
	$classes[] = 'my-class';
	return $classes;
}
add_filter( 'pfgw_gallery_classes', 'mytheme_change_pfgw_classes' );
```

### Does it only work with posts? What about other post types? ###

The default type is <code>post</code>. However, you can use `pfgw_post_types` filter in your `functions.php` file and then add or remove how many post types you wish. Just remember that [your theme must support](http://codex.wordpress.org/Post_Formats#Adding_Post_Type_Support) these post types as well.
```
/**
 * Add another post type to our gallery query
 *
 * @param array $post_types The post types for the query 
 */
function mytheme_add_post_types( $post_types ) {
	$post_types[] = 'page';
	return $post_types;
}
add_filter( 'pfgw_post_types', 'mytheme_add_post_types' );
```

## Changelog ##

### 1.1 ###
* Make style unique for our widget using class `.widget_post_format_gallery`

### 1.0 ###
* First version.