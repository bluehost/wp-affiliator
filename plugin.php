<?php
/*
Plugin Name: Bluehost Affiliator
Description: This plugin makes it easy for you to add Bluehost affiliate banners to posts using a Bluehost icon above the editor.  You can also add static banners to the sidebar with the widget.  To get started insert your Bluehost Affiliate Username under Settings -> General or <a href="#bha-fancy-content">click here</a>.
Version: 1.0.5
Author: Mike Hansen
Author URI: http://mikehansen.me?utm_source=bha_wp_plugin
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
GitHub Plugin URI: bluehost/wp-affiliator
GitHub Branch: master
*/

function bha_plugin_action_links( $links, $file ) {
	if ( $file == plugin_basename( dirname(__FILE__).'/plugin.php' ) ) {
		$links[] = '<a href="' . admin_url( 'options-general.php' ) . '">' . __( 'Settings' ) . '</a>';
	}
	return $links;
}

add_filter( 'plugin_action_links', 'bha_plugin_action_links', 10, 2 );

function bha_add_button( $icons ) {
	$img = plugins_url( 'bh-icon-24.png' , __FILE__ );
	$id = 'bha_popup_container';
	$title = 'Bluehost Affiliator Library';
	$icons .= "<a style='position:relative;bottom:1px;' class='thickbox' title='" . $title . "'
	href='#TB_inline?width=640&inlineId=" . $id . "'>
	<img src='". $img . "' /></a>";
	return $icons;
}
add_action( 'media_buttons_context', 'bha_add_button' );

function bha_add_inline_popup_content() {
?>
<div id="bha_popup_container" style="display:none;">
	<form id="bha-form">
	<div class="bha-preview"><img src="<?php echo bha_get_image_link(); ?>" /></div>
	<label for="bha-img-size">Size : </label>
	<select name="bha-img-size" class="bha-img-size">
		<?php
		foreach ( bha_image_sizes() as $size => $val ) {
			echo "<option value='" .$size. "'>" . $size . "</option>";
		}
		?>
	</select>
	<br/>
	<label for="bha-img-variation">Variation : </label>
	<select name="bha-img-variation" class="bha-img-variation">
		<?php
		for ( $i=1; $i < 6; $i++ ) { 
			echo "<option value='0" .$i. "'>" . $i . "</option>";
		}
		?>
	</select><br/>
	<label for="bha-img-align">Align : </label>
	<select name="bha-img-align" class="bha-img-align">
			<option value="none" selected="selected">None</option>
			<option value="alignleft">Left</option>
			<option value="alignright">Right</option>
			<option value="aligncenter">Center</option>
	</select><br/>
	<label for="bha-img-id">Tracking Code<small>(optional)</small> : </label>
	<input type="text" name="bha-img-id" class="bha-img-id" />
	<br/><br/>
	<input type="submit" value="Insert Into Content" />
	</form>
</div>
<?php
}
add_action( 'admin_footer-post-new.php', 'bha_add_inline_popup_content' );
add_action( 'admin_footer-page-new.php', 'bha_add_inline_popup_content' );
add_action( 'admin_footer-post.php', 'bha_add_inline_popup_content' );
add_action( 'admin_footer-page.php', 'bha_add_inline_popup_content' );
add_action( 'admin_footer-widgets.php', 'bha_add_inline_popup_content' );
add_action( 'admin_footer-index.php', 'bha_add_inline_popup_content' );


function bha_img_js() {
	?>
<script type="text/javascript">
jQuery( document ).ready( function() {
	var bha_links = <?php echo json_encode( bha_image_sizes() ); ?>;
	function bha_image_links( size, variant ) {
		if( typeof bha_links[ size ][ variant ] == 'undefined' ) {
			return bha_links[ size ]['03'];
		} else {
			return bha_links[ size ][ variant ];
		}
	}
	function bha_build_shortcode() {
		var size = jQuery( '.bha-img-size' ).val();
		var variation = jQuery( '.bha-img-variation' ).val();
		var align = jQuery( '.bha-img-align' ).val();
		var id = jQuery( '.bha-img-id' ).val();
		if( id.length > 1 ) {
			id = "id='" + id + "' ";
		} else {
			id = "";
		}
		jQuery( '.bha-img-id' ).val( '' );
		return "[bha " + id + "size='" + size + "' variation='" + variation + "' align='" + align + "']";
	}
	//this is for the post/page form
	jQuery( '.bha-img-size, .bha-img-variation' ).change( function() {
		var size = jQuery( '.bha-img-size' ).val();
		var variation = jQuery( '.bha-img-variation' ).val();
		jQuery( '.bha-preview' ).html( '<img src="https://bluehost-cdn.com/media/partner/images/house' + bha_image_links( size, variation ) + '" />' );
	} );
	//this is for the widget
	jQuery( document ).on( 'change', '.bha-wid-img-size, .bha-wid-img-variation', function() {	
		var size = jQuery( this ).parent().children( '.bha-wid-img-size' ).val();
		var variation = jQuery( this ).parent().children( '.bha-wid-img-variation' ).val();
		if( size.substring( 0, 3 ) > '225' || size.substring( 4, 7 ) > '220' ) {
			var message = "<small>(resized for preview)</small>";
		} else {
			var message = "<small>(preview)</small>";
		}
		jQuery( this ).parent().siblings( '.bha-wid-preview' ).html( '<img src="https://bluehost-cdn.com/media/partner/images/house' + bha_image_links( size, variation ) + '" /><br />' + message );
	} );
	jQuery( '#bha-form' ).submit( function( e ) {       
		e.preventDefault();
		var shortcode = bha_build_shortcode();
		var position = jQuery( '.bha-img-position' ).val();
		window.parent.send_to_editor( shortcode );
		//Close window
		parent.tb_remove();
	} );
} );
</script>
	<?php
}
add_action( 'admin_footer-post-new.php', 'bha_img_js' );
add_action( 'admin_footer-page-new.php', 'bha_img_js' );
add_action( 'admin_footer-post.php', 'bha_img_js' );
add_action( 'admin_footer-page.php', 'bha_img_js' );
add_action( 'admin_footer-widgets.php', 'bha_img_js' );
add_action( 'admin_footer-index.php', 'bha_img_js' );

function bha_shortcode( $atts ) {
	$defaults = array(
		'size'		=> '120x120',
		'variation' => '3',
		'align'		=> 'none',
		'position'	=> 'before',
		'id'		=> ''
	);
	$atts = wp_parse_args( $atts, $defaults );
	if( strlen( $atts['id'] ) ) {
		$id = "shortcode/" . $atts['id'];
	} else {
		$id = "shortcode/";
	}
	$aff_link = bha_get_link( $id  );
	$img_srcs = bha_image_sizes();
	return "<a href='" . $aff_link . "' target='_blank'><img class='" . $atts['align'] . "' src='" . bha_get_image_link( $atts['size'], $atts['variation'] ) . "' /></a>";
}
add_shortcode( 'bha', 'bha_shortcode' );

function bha_user_field_callback() {
	$value = get_option( 'bha_username' );
	echo '<input type="text" name="bha_username" value="' . esc_attr( $value ) . '" />';
}

function bha_create_settings() {
	add_settings_field(
		'bha_username',
		'Bluehost Affiliate Username',
		'bha_user_field_callback',
		'general'
		);
	register_setting( 'general', 'bha_username' );
}
add_action( 'admin_init', 'bha_create_settings' );

function bha_get_link( $id = null ) {
	$user = get_option( 'bha_username' );
	$url = get_site_url() . "?bha=true&user=" . $user;
	if( ! is_null( $id ) ) {
		$url .= "&id=" . $id;
	}
	$url = str_replace( ' ', '-', $url );
	return esc_url( $url );
}

function bha_rewrite_redirect() {
	if( isset( $_GET['bha'] ) AND isset( $_GET['user'] ) ) {
		$user = esc_attr( $_GET['user'] );
		$url = "http://bluehost.com/track/" . $user;
		if( isset( $_GET['id'] ) ) {
			$url .= "/affiliator/" . $_GET['id'] . "/";
		} else {
			$url .= "/affiliator/";
		}
		wp_redirect( $url, 301 );
		exit;
	}
}
add_action( 'init', 'bha_rewrite_redirect' );

function bha_admin_styles() {
	?>
<style type="text/css">
#bha-form label{
	width: 200px;
	display: inline-block;
	margin: 10px 0;
	font-size: 14px;
}
#bha-form select{
	width: 160px;
	display: inline-block;
}
#bha-form .bha-preview{float:right;max-width: 100%;}
.widget-content .bha-wid-preview{text-align:center;max-width: 100%;}
.widget-content .bha-wid-preview img{max-height: 220px;}
.bha-preview img, .bha-wid-preview img{max-width:100%; }
#TB_ajaxContent{width:640px !important;}
#bha-form input[type="submit"]{cursor: pointer;}
#TB_window{height: auto !important;}
</style>
	<?php
}
add_action( 'admin_head-post-new.php', 'bha_admin_styles' );
add_action( 'admin_head-page-new.php', 'bha_admin_styles' );
add_action( 'admin_head-post.php', 'bha_admin_styles' );
add_action( 'admin_head-page.php', 'bha_admin_styles' );
add_action( 'admin_head-widgets.php', 'bha_admin_styles' );
add_action( 'admin_head-index.php', 'bha_admin_styles' );

function bha_image_base() {
	$base = "http://bluehost-cdn.com/media/partner/images/";
	$base .= get_option( 'bha_username', 'house' ) . "/";
	return $base;
}

function bha_get_image_link( $size = "120x120", $variant = '03' ) {
	$base = bha_image_base();
	$url = "https://bluehost-cdn.com/media/partner/images/";
	$user = get_option( 'bha_username', 'house' );
	$images = bha_image_sizes();
	if( isset( $images[ $size ][ $variant ] ) ) {
		$image = $images[ $size ][ $variant ];
	} elseif( isset( $images[ $size ] ) ) {
		$image = $images[ $size ]['03'];
	} else {
		$image = $images['120x120']['03'];
	}
	return $url . $user . $image;
}

function bha_image_sizes() {
	$sizes = array(
		'120x120' 	=> array( 3, 4 ),
		'120x240' 	=> array( 3, 4 ),
		'120x600' 	=> array( 2, 3 ),
		'125x125' 	=> array( 3, 4 ),
		'125x240' 	=> array( 3, 4 ),
		'125x400' 	=> array( 3, 4 ),
		'160x40' 	=> array( 3, 4 ),
		'160x600' 	=> array( 1, 3),
		'190x60' 	=> array( 3, 4 ),
		'300x250' 	=> array( 1, 2, 3, 4 ),
		'430x288' 	=> array( 1, 2, 3, 4 ),
		'468x60' 	=> array( 3, 4, 5 ),
		'488x160' 	=> array( 1, 2, 3, 4 ),
		'620x203' 	=> array( 1, 2, 3, 4 ),
		'760x80' 	=> array( 1, 2, 3, 4 )
	);
	$images = array();
	foreach ( $sizes as $size => $variation ) {
		foreach ( $variation as $value ) {
			$images[ $size ][ "0" . $value ] = "/" . $size . "/bh-" . $size . "-0" . $value . "-dy.png";
		}
	}
	return $images;
}

function bha_fancy_enqueue() {
	wp_enqueue_style( 'bha-fancy-styles', plugins_url( '/fancy/jquery.fancybox.css' , __FILE__ ) );
	wp_enqueue_script( 'bha-fancy-script', plugins_url( '/fancy/jquery.fancybox.js' , __FILE__ ), array( 'jquery' ) );
}
add_action( 'admin_enqueue_scripts', 'bha_fancy_enqueue' );

function bha_activation() {
	set_transient( 'bha_show_popup', true, 30 );
}
register_activation_hook( __FILE__, 'bha_activation' );

function bha_fancy_script() {
	?>
	<style type="text/css">
	#bha-fancy-content form {
		margin: 0 3%;
		width: 93%;
	}
	#bha-fancy-content input[type="text"] {
		width: 100%;
		font-size: 1.8rem;
		padding: 8px 18px;
		text-align: center;
		width: 100%;
	}
	#bha-fancy-content input[type="submit"] {
		width: 100%;
		background: linear-gradient(to right, #62bc33 0%, #8bd331 100%) repeat scroll 0 0 rgba(0, 0, 0, 0);
		border: medium none;
		border-radius: 5px;
		box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
		color: #fff;
		font-size: 1.8em;
		line-height: 1.5em;
		margin: 10px 0;
		padding: 0.25em 1em;
		cursor: pointer;
	}
	#bha-fancy-content input[type="submit"]:hover {
		background: none repeat scroll 0 0 #8bd331;
	}
	</style>
	<script type="text/javascript">
		jQuery( document ).ready( function( $ ) {
			$( '#bluehost-affiliator .plugin-description a' ).fancybox();
			$( 'a.bha-set-aff' ).fancybox();

			<?php 
			if( get_transient( 'bha_show_popup' ) ) {
				?>
			$( '#bluehost-affiliator .plugin-description a' ).click();
				<?php
			}
			?>

			$( '#bha-fancy-content form' ).submit( function ( e ) {
				e.preventDefault();
				
				$( '#bha-ajax-message' ).removeClass( 'error updated' );
				$( '#bha-ajax-message' ).html( '' );

				var data = {
					'action': 'bha_set_aff_id_ajax',
					'aff_id': $( '#bha-aff-id' ).val()
				}

				var url = '<?php echo admin_url( 'admin-ajax.php' );?>';

				$.post( url, data, function( result ) {
					result = $.parseJSON( result );
					
					$( '#bha-ajax-message' ).addClass( result.response );
					$( '#bha-ajax-message' ).html( "<p>" + result.message + "</p>" );
					$( '#bha-ajax-message' ).show( 'slow' );
					if( result.response == 'updated' ) {
						$( '#bha-aff-id' ).val( result.username );
						setTimeout( function () {
							$( '#ajax-message' ).hide();
							$( '.fancybox-close' ).click();
							$( '#bha-ajax-message' ).removeClass( 'error updated' );
							$( '#bha-ajax-message' ).html( '' );
						}, 5000 );
					}
				} );

			} );
		} );
	</script>
	<?php
}
add_action( 'admin_head-plugins.php', 'bha_fancy_script' );

function bha_set_aff_id_ajax() {
	$result = array( 'response' => 'error' );
	if( isset( $_POST['aff_id'] ) && strlen( $_POST['aff_id'] ) > 1 ) {
		$aff_id = wp_kses( $_POST['aff_id'], array() );
		if( update_option( 'bha_username', $aff_id ) ) {
			$result['response'] = 'updated';
			$result['message'] = 'Affiliate ID Updated';
			$result['username'] = $aff_id;
		} else {
			$result['message'] = 'Affiliate ID was same or unable to update';
		}
	} else {
		 $result['message'] = 'Affiliate ID was not set';
	}
	$result = json_encode( $result );
	echo $result;
	die();
}
add_action( 'wp_ajax_bha_set_aff_id_ajax', 'bha_set_aff_id_ajax' );

function bha_fancy_content() {
	?>
	<div id="bha-fancy-content" style="display:none; min-height: 300px;">
		<br/><br/>
		<div id="bha-ajax-message" style="display: none;"></div>
		<form>
			<input type="text" name="bha-aff-id" id="bha-aff-id" value="<?php echo get_option( 'bha_username' ); ?>" placeholder="Affiliate Username"/>
			<input type="submit" value="Set Affiliate ID" />
		</form>
	</div>
	<?php
}
add_action( 'admin_footer', 'bha_fancy_content' );

function bha_guess_username() {
	//notice do not use this.. it is only used to notify previous users
	$current_dir = dirname( __FILE__ );
	$piece = explode( '/', $current_dir );
	if( count( $piece ) > 2 ) {
		return $piece[2];
	} else {
		return "";
	}
}

function bha_notify_cpanel_user() {
	$bh_aff = get_option( 'bha_username' );
	$guess_aff = bha_guess_username();
	if( $bh_aff == $guess_aff || $bh_aff == "" ) {
		echo "<div class='updated' style='border-left: 4px solid #3575B9;'><p>Your Bluehost affiliate ID is invalid or not set. <a class='bha-set-aff' href='#bha-fancy-content'>Set Affiliate Username</a></p></div>";
	}
}

function bha_notify_scripts() {
	$bh_aff = get_option( 'bha_username' );
	$guess_aff = bha_guess_username();
	if( $bh_aff == $guess_aff || $bh_aff == "" ) {
		add_action( 'admin_notices', 'bha_notify_cpanel_user' );
		add_action( 'admin_head', 'bha_fancy_script' );
	}
}
add_action( 'admin_init', 'bha_notify_scripts' );

class BHA_Widget extends WP_Widget {
	public function __construct() {
		parent::__construct(
			'bha_widget',
			'Bluehost Affiliator',
			array( 'description' => __( 'Add your affiliate banner.', 'bha-widget' ), )
		);
	}
	public function form( $instance ) {
		if( isset( $instance[ 'bha-img-title' ] ) ) {
			$title = $instance[ 'bha-img-title' ];
		} else {
			$title = __( 'Recommended Host' );
		}
		if( isset( $instance[ 'bha-img-size' ] ) ) {
			$bha_size = $instance[ 'bha-img-size' ];
		} else {
			$bha_size = '120x120';
		}
		if( isset( $instance[ 'bha-img-variation' ] ) ) {
			$bha_variation = $instance[ 'bha-img-variation' ];
		} else {
			$bha_variation = '03';
		}
		if( isset( $instance[ 'bha-img-align' ] ) ) {
			$bha_align = $instance[ 'bha-img-align' ];
		} else {
			$bha_align = 'none';
		}
		if( isset( $instance[ 'bha-img-id' ] ) ) {
			$bha_id = $instance[ 'bha-img-id' ];
		} else {
			$bha_id = '';
		}

		if( substr( $bha_size, 0, 3 ) > '225' || substr( $bha_size, 4, 3 ) > '220' ) {
			$bha_message = "<small>(resized for preview)</small>";
		} else {
			$bha_message = "<small>(preview)</small>";
		}
		$bha_image_sizes = bha_image_sizes();
		?>
		<div class="bha-wid-preview"><img src="<?php echo bha_get_image_link( $bha_size, $bha_variation ); ?>" /><br/><?php echo $bha_message; ?></div>
		<p class="clear">
		<label for="<?php echo $this->get_field_name( 'bha-img-title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'bha-img-title' ); ?>" name="<?php echo $this->get_field_name( 'bha-img-title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		<label for="<?php echo $this->get_field_name( 'bha-img-size' ); ?>"><?php _e( 'Size:' ); ?></label> 
		<select  class="widefat bha-wid-img-size" id="<?php echo $this->get_field_id( 'bha-img-size' ); ?>" name="<?php echo $this->get_field_name( 'bha-img-size' ); ?>">
			<?php
			foreach ( $bha_image_sizes as $k => $v ) {
					echo "<option value='" . $k . "' " . selected( $bha_size, $k, true ) . ">" . $k . "</option>";
			}
			?>
		</select>
		<label for="<?php echo $this->get_field_name( 'bha-img-variation' ); ?>"><?php _e( 'Variation:' ); ?></label> 
		<select  class="widefat bha-wid-img-variation" id="<?php echo $this->get_field_id( 'bha-img-variation' ); ?>" name="<?php echo $this->get_field_name( 'bha-img-variation' ); ?>">
			<?php
			for ( $i = 1; $i < 6; $i++ ) { 
				echo "<option value='0" . $i . "' " . selected( $bha_variation, "0".$i, true ) . ">" . $i . "</option>";
			}
			?>
		</select>
		<label for="<?php echo $this->get_field_name( 'bha-img-align' ); ?>"><?php _e( 'Align:' ); ?></label> 
		<select  class="widefat bha-img-align" id="<?php echo $this->get_field_id( 'bha-img-align' ); ?>" name="<?php echo $this->get_field_name( 'bha-img-align' ); ?>">
			<?php
			$align_options = array( 
				'none' => 'None', 
				'alignleft' => 'Left', 
				'alignright' => 'Right', 
				'aligncenter' => 'Center' 
			);
			foreach ( $align_options as $k => $v ) {
				echo "<option value='" . $k . "' " . selected( $bha_align, $k, true ) . ">" . $v . "</option>";
			}
			?>
		</select>
		<label for="<?php echo $this->get_field_name( 'bha-img-id' ); ?>"><?php _e( 'Tracking Code<small>(optional)</small>' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'bha-img-id' ); ?>" name="<?php echo $this->get_field_name( 'bha-img-id' ); ?>" type="text" value="<?php echo esc_attr( $bha_id ); ?>" />
		</p>
		<?php
	}

	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['bha-img-title'] );
		echo $args['before_widget'];
		if( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		$size = $instance['bha-img-size'];
		$variation = $instance['bha-img-variation'];
		$id = $instance['bha-img-id'];
		if( strlen( $id ) > 1 ) {
			$id = $args['id'] . "/" . $id;
		} else {
			$id = $args['id'];
		}
		if( $instance['bha-img-align'] != 'none' ) {
			$align = "class='" . $instance['bha-img-align'] . "' ";
		} else {
			$align = "";
		}
		$bha_img_links = bha_image_sizes();
		echo "<a href='" . bha_get_link( $id ) . "' target='_blank'><img " . $align . "src='" . bha_get_image_link( $size, $variation ) . "' style='max-width:100%;'/></a>";
		echo $args['after_widget'];
	}
}

function register_bha_widget() {
	register_widget( 'BHA_Widget' );
}
add_action( 'widgets_init', 'register_bha_widget' );

function bha_load_updater() {
	if ( is_admin() ) {
		/*
		Check class_exist because this could be loaded in a different plugin
		*/
		if( ! class_exists( 'GitHub_Updater' ) ) { 
			require_once( plugin_dir_path( __FILE__ ) . 'updater/class-github-updater.php' );
		}
		if( ! class_exists( 'GitHub_Updater_GitHub_API' ) ) {
			require_once( plugin_dir_path( __FILE__ ) . 'updater/class-github-api.php' );
		}
		if( ! class_exists( 'GitHub_Plugin_Updater' ) ) {
			require_once( plugin_dir_path( __FILE__ ) . 'updater/class-plugin-updater.php' );
		}
		new GitHub_Plugin_Updater;
	}
}
add_action( 'admin_init', 'bha_load_updater' );