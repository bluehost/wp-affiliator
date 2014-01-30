<?php
/*
Plugin Name: Bluehost Affiliator
Description: This plugin makes it easy for you to add Bluehost affiliate banners to posts using a Bluehost icon above the editor.  You can also add static banners to the sidebar with the widget.  To get started insert your Bluehost Affiliate Username under <a href="options-general.php">Settings -> General</a>
Version: 1.0.2
Author: Mike Hansen
Author URI: http://mikehansen.me?utm_source=bha_wp_plugin
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
GitHub Plugin URI: bluehost/wp-affiliator
GitHub Branch: master
*/

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
	<div class="bha-preview"><img src="http://img.bluehost.com/100x100/bh_100x100_01.gif" /></div>
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
		jQuery( '.bha-preview' ).html( '<img src="http://img.bluehost.com' + bha_links[size][variation] + '" />' );
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
		jQuery( this ).parent().siblings( '.bha-wid-preview' ).html( '<img src="http://img.bluehost.com' + bha_links[size][variation] + '" /><br />' + message );
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
		'size'		=> '100x100',
		'variation' => '1',
		'align'		=> 'none',
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
	return "<a href='" . $aff_link . "' target='_blank'><img class='" . $atts['align'] . "' src='http://img.bluehost.com/" . $img_srcs[ $atts['size'] ][ $atts['variation'] ] . "' /></a>";
}
add_shortcode( 'bha', 'bha_shortcode' );

function bha_user_field_callback() {
	$value = get_option( 'bha_username', bha_guess_username() );
	echo '<input type="text" name="bha_username" value="' . esc_attr( $value ) . '" />';
}

function bha_guess_username() {
	$current_dir = dirname( __FILE__ );
	$piece = explode( '/', $current_dir );
	if( count( $piece ) > 2 ) {
		return $piece[2];
	} else {
		return "";
	}
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
	$user = get_option( 'bha_username', bha_guess_username() );
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

function bha_image_sizes() {
	$images = array(
		'100x100' => array(
			'01' => '/100x100/bh_100x100_01.gif',
			'02' => '/100x100/bh_100x100_02.gif',
			'03' => '/100x100/bh_100x100_03.gif',
			'04' => '/100x100/bh_100x100_04.gif',
			'05' => '/100x100/bh_100x100_05.gif'
			),
		'120x120' => array(
			'01' => '/120x120/bh_120x120_01.gif',
			'02' => '/120x120/bh_120x120_02.gif',
			'03' => '/120x120/bh_120x120_03.gif',
			'04' => '/120x120/bh_120x120_04.gif',
			'05' => '/120x120/bh_120x120_05.gif'
			),
		'120x240' => array(
			'01' => '/120x240/bh_120x240_01.gif',
			'02' => '/120x240/bh_120x240_02.gif',
			'03' => '/120x240/bh_120x240_03.gif',
			'04' => '/120x240/bh_120x240_04.gif',
			'05' => '/120x240/bh_120x240_05.gif'
			),
		'120x60' => array(
			'01' => '/120x60/bh_120x60_01.gif',
			'02' => '/120x60/bh_120x60_02.gif',
			'03' => '/120x60/bh_120x60_03.gif',
			'04' => '/120x60/bh_120x60_04.gif',
			'05' => '/120x60/bh_120x60_05.gif'
			),
		'120x600' => array(
			'01' => '/120x600/bh_120x600_01.gif',
			'02' => '/120x600/bh_120x600_02.gif',
			'03' => '/120x600/bh_120x600_03.gif',
			'04' => '/120x600/bh_120x600_04.gif',
			'05' => '/120x600/bh_120x600_05.gif'
			),
		'120x90' => array(
			'01' => '/120x90/bh_120x90_01.gif',
			'02' => '/120x90/bh_120x90_02.gif',
			'03' => '/120x90/bh_120x90_03.gif',
			'04' => '/120x90/bh_120x90_04.gif',
			'05' => '/120x90/bh_120x90_05.gif'
			),
		'125x125' => array(
			'01' => '/125x125/bh_125x125_01.gif',
			'02' => '/125x125/bh_125x125_02.gif',
			'03' => '/125x125/bh_125x125_03.gif',
			'04' => '/125x125/bh_125x125_04.gif',
			'05' => '/125x125/bh_125x125_05.gif'
			),
		'125x240' => array(
			'01' => '/125x240/bh_125x240_01.gif',
			'02' => '/125x240/bh_125x240_02.gif',
			'03' => '/125x240/bh_125x240_03.gif',
			'04' => '/125x240/bh_125x240_04.gif',
			'05' => '/125x240/bh_125x240_05.gif'
			),
		'125x400' => array(
			'01' => '/125x400/bh_125x400_01.gif',
			'02' => '/125x400/bh_125x400_02.gif',
			'03' => '/125x400/bh_125x400_03.gif',
			'04' => '/125x400/bh_125x400_04.gif',
			'05' => '/125x400/bh_125x400_05.gif'
			),
		'160x40' => array(
			'01' => '/160x40/bh_160x40_01.gif',
			'02' => '/160x40/bh_160x40_02.gif',
			'03' => '/160x40/bh_160x40_03.gif',
			'04' => '/160x40/bh_160x40_04.gif',
			'05' => '/160x40/bh_160x40_05.gif'
			),
		'160x600' => array(
			'01' => '/160x600/bh_160x600_01.gif',
			'02' => '/160x600/bh_160x600_02.gif',
			'03' => '/160x600/bh_160x600_03.gif',
			'04' => '/160x600/bh_160x600_04.gif',
			'05' => '/160x600/bh_160x600_05.gif'
			),
		'175x25' => array(
			'01' => '/175x25/bh_175x25_01.gif',
			'02' => '/175x25/bh_175x25_02.gif',
			'03' => '/175x25/bh_175x25_03.gif',
			'04' => '/175x25/bh_175x25_04.gif',
			'05' => '/175x25/bh_175x25_05.gif'
			),
		'175x30' => array(
			'01' => '/175x30/bh_175x30_01.gif',
			'02' => '/175x30/bh_175x30_02.gif',
			'03' => '/175x30/bh_175x30_03.gif',
			'04' => '/175x30/bh_175x30_04.gif',
			'05' => '/175x30/bh_175x30_05.gif'
			),
		'180x150' => array(
			'01' => '/180x150/bh_180x150_01.gif',
			'02' => '/180x150/bh_180x150_02.gif',
			'03' => '/180x150/bh_180x150_03.gif',
			'04' => '/180x150/bh_180x150_04.gif',
			'05' => '/180x150/bh_180x150_05.gif'
			),
		'189x116' => array(
			'01' => '/189x116/bh_189x116_01.gif',
			'02' => '/189x116/bh_189x116_02.gif',
			'03' => '/189x116/bh_189x116_03.gif',
			'04' => '/189x116/bh_189x116_04.gif',
			'05' => '/189x116/bh_189x116_05.gif'
			),
		'190x60' => array(
			'01' => '/190x60/bh_190x60_01.gif',
			'02' => '/190x60/bh_190x60_02.gif',
			'03' => '/190x60/bh_190x60_03.gif',
			'04' => '/190x60/bh_190x60_04.gif',
			'05' => '/190x60/bh_190x60_05.gif'
			),
		'234x60' => array(
			'01' => '/234x60/bh_234x60_01.gif',
			'02' => '/234x60/bh_234x60_02.gif',
			'03' => '/234x60/bh_234x60_03.gif',
			'04' => '/234x60/bh_234x60_04.gif',
			'05' => '/234x60/bh_234x60_05.gif'
			),
		'300x250' => array(
			'01' => '/300x250/bh_300x250_01.gif',
			'02' => '/300x250/bh_300x250_02.gif',
			'03' => '/300x250/bh_300x250_03.gif',
			'04' => '/300x250/bh_300x250_04.jpg',
			'05' => '/300x250/bh_300x250_05.gif'
			),
		'300x50' => array(
			'01' => '/300x50/bh_300x50_01.gif',
			'02' => '/300x50/bh_300x50_02.gif',
			'03' => '/300x50/bh_300x50_03.gif',
			'04' => '/300x50/bh_300x50_04.gif',
			'05' => '/300x50/bh_300x50_05.gif'
			),
		'430x288' => array(
			'01' => '/430x288/bh_430x288_01.gif',
			'02' => '/430x288/bh_430x288_02.gif',
			'03' => '/430x288/bh_430x288_03.gif',
			'04' => '/430x288/bh_430x288_04.jpg',
			'05' => '/430x288/bh_430x288_05.gif'
			),
		'468x60' => array(
			'01' => '/468x60/bh_468x60_01.gif',
			'02' => '/468x60/bh_468x60_02.gif',
			'03' => '/468x60/bh_468x60_03.gif',
			'04' => '/468x60/bh_468x60_04.gif',
			'05' => '/468x60/bh_468x60_05.gif'
			),
		'488x160' => array(
			'01' => '/488x160/bh_488x160_01.gif',
			'02' => '/488x160/bh_488x160_02.gif',
			'03' => '/488x160/bh_488x160_03.gif',
			'04' => '/488x160/bh_488x160_04.gif',
			'05' => '/488x160/bh_488x160_05.gif'
			),
		'620x203' => array(
			'01' => '/620x203/bh_620x203_01.jpg',
			'02' => '/620x203/bh_620x203_02.jpg',
			'03' => '/620x203/bh_620x203_03.jpg',
			'04' => '/620x203/bh_620x203_04.jpg',
			'05' => '/620x203/bh_620x203_05.jpg'
			),
		'728x90' => array(
			'01' => '/728x90/bh_728x90_01.gif',
			'02' => '/728x90/bh_728x90_02.gif',
			'03' => '/728x90/bh_728x90_03.gif',
			'04' => '/728x90/bh_728x90_04.gif',
			'05' => '/728x90/bh_728x90_05.gif'
			),
		'760x80' => array(
			'01' => '/760x80/bh_760x80_01.gif',
			'02' => '/760x80/bh_760x80_02.gif',
			'03' => '/760x80/bh_760x80_03.gif',
			'04' => '/760x80/bh_760x80_04.gif',
			'05' => '/760x80/bh_760x80_05.gif'
			),
		'88x31' => array(
			'01' => '/88x31/bh_88x31_01.gif',
			'02' => '/88x31/bh_88x31_02.gif',
			'03' => '/88x31/bh_88x31_03.gif',
			'04' => '/88x31/bh_88x31_04.gif',
			'05' => '/88x31/bh_88x31_05.gif'
			)
		);
	return $images;
}

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
			$bha_size = '100x100';
		}
		if( isset( $instance[ 'bha-img-variation' ] ) ) {
			$bha_variation = $instance[ 'bha-img-variation' ];
		} else {
			$bha_variation = '01';
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
		<div class="bha-wid-preview"><img src="http://img.bluehost.com<?php echo $bha_image_sizes[ $bha_size ][ $bha_variation ]; ?>" /><br/><?php echo $bha_message; ?></div>
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
		echo "<a href='" . bha_get_link( $id ) . "' target='_blank'><img " . $align . "src='http://img.bluehost.com" . $bha_img_links[$size][$variation] . "' style='max-width:100%;'/></a>";
		echo $args['after_widget'];
	}
}

function register_bha_widget() {
	register_widget( 'BHA_Widget' );
}
add_action( 'widgets_init', 'register_bha_widget' );

// Load base classes for github updater
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