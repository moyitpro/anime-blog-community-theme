<?php
/** Start the engine */
require_once( get_template_directory() . '/lib/init.php' );

/** Child theme (do not remove) */
define( 'CHILD_THEME_NAME', 'Anime Blog Community Theme' );
define( 'CHILD_THEME_URL', 'https://github.com/chikorita157/anime-blog-community-theme' );
/** Remove Generator **/
remove_action('wp_head', 'wp_generator');
/** Add Viewport meta tag for mobile browsers */
add_action( 'genesis_meta', 'add_viewport_meta_tag' );
function add_viewport_meta_tag() {
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0"/>';
}

/** Add support for custom background */
add_custom_background();

/** Add support for custom header */
add_theme_support( 'genesis-custom-header', array( 'width' => 960, 'height' => 240 ) );

/** Add support for 3-column footer widgets */
add_theme_support( 'genesis-footer-widgets', 3 );
remove_action( 'genesis_doctype', 'genesis_do_doctype' );
add_action( 'genesis_doctype', 'child_do_doctype' );

/**
 * HTML 5 Support.
 *
 * @author Gary Jones
 * @link http://dev.studiopress.com/modify-doctype.htm
 */
function child_do_doctype() {
?>
<!DOCTYPE HTML>
<html <?php language_attributes(); ?>>
<head>
<meta http-equiv="Content-Type" content="<?php bloginfo( 'html_type' ); ?>; charset=<?php bloginfo( 'charset' ); ?>" />
<?php
}
/**
 * Adds separate comment and trackback counts
 */
function commentCount($type = 'comments'){

	if($type == 'comments'):

		$typeSql = 'comment_type = ""';
		$oneText = '1 Comment';
		$moreText = '% Comments';
		$noneText = 'No Comments';

	elseif($type == 'pings'):

		$typeSql = 'comment_type != ""';
		$oneText = '1 Trackback';
		$moreText = '% Trackbacks';
		$noneText = 'No Trackbacks';

	elseif($type == 'trackbacks'):

		$typeSql = 'comment_type = "trackback"';
		$oneText = '1 Trackback';
		$moreText = '% Trackbacks';
		$noneText = 'No Trackbacks';

	elseif($type == 'pingbacks'):

		$typeSql = 'comment_type = "pingback"';
		$oneText = '1 Trackback';
		$moreText = '% Trackbacks';
		$noneText = 'No Trackbacks';

	endif;

	global $wpdb;

    $result = $wpdb->get_var('
        SELECT
            COUNT(comment_ID)
        FROM
            '.$wpdb->comments.'
        WHERE
            '.$typeSql.' AND
            comment_approved="1" AND
            comment_post_ID= '.get_the_ID()
    );

	if($result == 0):

		echo str_replace('%', $result, $noneText);

	elseif($result == 1): 

		echo str_replace('%', $result, $oneText);

	elseif($result > 1): 

		echo str_replace('%', $result, $moreText);

	endif;

}
/** Modify the author box title */
add_filter( 'genesis_author_box_title', 'child_author_box_title' );
function child_author_box_title() {
ob_start();
	the_author_posts_link();
$title = ob_get_clean();
$title = sprintf( '<strong>This post was handcrafted by…</strong><br /> %s - who has written <i><b>%s</b></i> posts.', $title, number_format_i18n( get_the_author_posts() ) );;
return $title;
}

add_filter('genesis_comment_form_args', 'custom_comment_form_args');
/**
 * Modify speak your mind text in comments
 *
 * @author Brian Gardner
 * @link http://dev.studiopress.com/modify-speak-your-mind.htm
 */
function custom_comment_form_args($args) {
    $args['title_reply'] = 'Leave a Comment';
    return $args;
}


add_filter( 'genesis_comment_list_args', 'child_comment_list_args' );
/**
 * Take the existing arguments, and one that specifies a custom comments.
 *
 * @author James
 * @link http://chikorita157.com
 *
 * @param array $args
 * @return type
 */
function child_comment_list_args( $args ) {
    $args['callback'] = 'child_list_comments';
    return $args;
}

/**
 * Build how the comments will look.
 *
 * @author James
 * @link http://chikorita157.com
 *
 * @param mixed $comment
 * @param array $args
 * @param integer $depth
 */
function child_list_comments( $comment, $args, $depth ) {

	$GLOBALS['comment'] = $comment; ?>

	<li <?php comment_class(); ?> id="comment-<?php comment_ID() ?>">

		<?php do_action( 'genesis_before_comment' ); ?>

		<div class="comment-author vcard">
			<?php echo get_avatar( $comment, $size = $args['avatar_size'] ); ?>
			<?php printf( __( '<cite class="fn">%s</cite> <span class="says">%s:</span>', 'genesis' ), get_comment_author_link(), apply_filters( 'comment_author_says_text', __( 'says', 'genesis' ) ) ); ?>
	 	</div><!-- end .comment-author -->

		<div class="comment-meta commentmetadata">
			<a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>"><?php printf( __( '%1$s at %2$s', 'genesis' ), get_comment_date(), get_comment_time() ); ?></a>
			<?php if (function_exists('comment_counter'))  {comment_counter('email','&bull; <span class="comment-counter">Comments: ','</span> ');} ?>
			<?php edit_comment_link( __( 'Edit', 'genesis' ), g_ent( '&bull; ' ), '' ); ?>
		</div><!-- end .comment-meta -->

		<div class="comment-content">
			<?php if ($comment->comment_approved == '0') : ?>
				<p class="alert"><?php echo apply_filters( 'genesis_comment_awaiting_moderation', __( 'Your comment is awaiting moderation.', 'genesis' ) ); ?></p>
			<?php endif; ?>

			<?php comment_text(); ?>
		</div><!-- end .comment-content -->

		<div class="reply">
			<?php comment_reply_link( array_merge( $args, array( 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
		</div>

		<?php do_action( 'genesis_after_comment' );

	/** No ending </li> tag because of comment threading */

}

add_filter( 'genesis_ping_list_args', 'child_ping_list_args' );
/**
 * Take the existing arguments, and one that specifies a custom callback.
 *
 * @author Gary Jones
 * @link http://dev.studiopress.com/change-trackback-format.htm
 *
 * Tap into the list of arguments applied at genesis/lib/functions/comments.php:136
 * @param array $args
 * @return type
 */
function child_ping_list_args( $args ) {
    $args['callback'] = 'child_list_pings';
    return $args;
}

/**
 * Build how the trackbacks / pings will look.
 *
 * @author Gary Jones
 * @link http://dev.studiopress.com/change-trackback-format.htm
 *
 * @param mixed $comment
 * @param array $args
 * @param integer $depth
 */
function child_list_pings( $comment, $args, $depth ) {
    $GLOBALS['comment'] = $comment;
    ?>
    <li <?php comment_class(); ?> id="li-comment-<?php comment_ID() ?>">
        <div id="comment-<?php comment_ID(); ?>">
            <div class="comment-author vcard">
                <?php echo get_avatar( $comment, $size = '48', $default = '<path_to_url>' ); ?>
                <?php printf( __( '<cite class="fn">%s</cite>' ), get_comment_author_link() ) ?>
            </div>
        </div>
    </li>
    <?php
}

/** Modify comments header text in comments */
add_filter('genesis_title_comments', 'custom_genesis_title_comments');
function custom_genesis_title_comments() {
	// Correctly present the respond link if comments are allowed
	if ( ( ! genesis_get_option( 'comments_posts' ) || ! comments_open() )) {
	$respond = '. Comments for this entry are closed.';
	}
	else {
	$respond = ' or <a href="#respond">add your own</a>.';
	}
    ob_start();
    	commentCount('comments');
    $title = ob_get_clean();
    $title = sprintf('<h3 class="headlinetext">%s… <span style="font-size:14px;"> read them%s</span></h3>', $title, $respond);
    return $title;
}
/** Modify trackbacks header text in comments */
add_filter( 'genesis_title_pings', 'custom_title_pings' );
function custom_title_pings() {
	ob_start();
	commentCount('pings'); 
	$title = ob_get_clean();
	$title = sprintf('<h3 class="headlinetext">%s</h3>', $title);
	return $title;
}
add_action('genesis_after_comment_form', 'custom_post_nav');
function custom_post_nav(){?>
    <div class="post-nav">
    <div class="prev-post-nav">
     <?php previous_post_link('<span class="prev">Previous Post:</span> %link', '%title'); ?>
    </div>
    <div class="next-post-nav">
 <?php next_post_link('<span class="next">Next Post:</span> %link', '%title'); ?>
    </div>
    </div>
<?php }
add_filter('genesis_search_text', 'custom_search_text');

/**
 * Customize search form text
 *
 * @author Brian Gardner
 * @link http://dev.studiopress.com/customize-search-form.htm
 */
function custom_search_text($text) {
    return esc_attr('To search, type and press enter;');
}
/** Customize the post meta function */
add_filter('genesis_post_meta', 'post_meta_filter');
function post_meta_filter($post_meta) {
if (!is_page()) {
    $post_meta = '[post_categories]<br />[post_tags sep="," before="Tagged With: "]';
    return $post_meta;
}}
add_action( 'genesis_after_loop', 'homepage_tags_area' );