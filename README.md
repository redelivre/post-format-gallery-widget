# Post Format Gallery Widget #
*A WordPress widget to display images from your galleries that are saved under the Gallery post format.*  

## Description ##
This plugin allows you to select posts from the post format Gallery and display their galleries in a widget. Here's some of its options:
* Number of images to show
* Image size
* Randomize images or leave the default order
* Use or not the default WordPress gallery style. If selected, you'll be able to choose a number of columns for you gallery.

**Important**: your theme must support the [Gallery post format](http://codex.wordpress.org/Post_Formats#Adding_Theme_Support) for posts, otherwise it won't work.

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
