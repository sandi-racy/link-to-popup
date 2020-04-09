<?php

/*
   Plugin Name: Link to Popup
   description: Transform your regular link to popup automatically.
   Version: 1.0
   Author: Sandi Rosyandi
   Author URI: https://sandi-racy.github.io
   License: GPL3
*/

define('LINK_TO_POPUP_TYPES', serialize(array(
	'class' => 'Class',
	'id' => 'ID',
	'selector' => 'Selector'
)));

register_activation_hook( __FILE__, 'link_to_popup_activation' );
function link_to_popup_activation () {
	register_uninstall_hook( __FILE__, 'link_to_popup_uninstall' );

	$selectors = get_option( 'link_to_popup_selectors' );
	if ( !$selectors ) {
		$selectors = array( 'selectors' => array() );
		add_option( 'link_to_popup_selectors', $selectors );
	}
}

function link_to_popup_uninstall () {
	$selectors = get_option( 'link_to_popup_selectors' );
	if ( $selectors ) {
		delete_option( 'link_to_popup_selectors' );
	}
}

add_action( 'admin_enqueue_scripts', 'link_to_popup_admin_enqueue_scripts' );
function link_to_popup_admin_enqueue_scripts () {
	wp_enqueue_style( 'link-to-popup-admin-style', plugin_dir_url( __FILE__ ) . 'css/admin/link-to-popup.css', false, '1.0', 'all');
	wp_enqueue_script( 'link-to-popup-admin-script', plugin_dir_url( __FILE__ ) . 'js/admin/link-to-popup.js', array( 'jquery' ), '1.0' );
}

add_action( 'wp_enqueue_scripts', 'link_to_popup_enqueue_scripts' );
function link_to_popup_enqueue_scripts () {
	wp_enqueue_style( 'magnific-popup-style', 'https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/magnific-popup.min.css', false, '1.1.0', 'all');
	wp_enqueue_script( 'magnific-popup-script', 'https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/jquery.magnific-popup.min.js', array( 'jquery' ), '1.1.0' );
	wp_enqueue_script( 'link-to-popup-script', plugin_dir_url( __FILE__ ) . 'js/link-to-popup.js', array( 'jquery' ), '1.0' );
}

add_filter( 'plugin_action_links_link-to-popup/link-to-popup.php', 'link_to_popup_settings_link' );
function link_to_popup_settings_link ( $links ) {
	$settings_link = '<a href="' . get_admin_url() .'options-general.php?page=link-to-popup-setting">' . __('Settings') . '</a>';
	array_push( $links, $settings_link);
	return $links;
}

add_action( 'admin_menu', 'link_to_popup_admin_menu' );
function link_to_popup_admin_menu () {
	add_options_page( 'Link to Popup Settings', 'Link to Popup', 'manage_options', 'link-to-popup-setting', 'link_to_popup_settings_page' );
}

function link_to_popup_settings_page () {
	$admin_url = admin_url( 'admin-ajax.php' );
	$nonce = wp_create_nonce( 'link_to_popup_settings' );
	$types = unserialize( LINK_TO_POPUP_TYPES );
	$selectors = get_option( 'link_to_popup_selectors' );
?>
	<div class="wrap">
        <h1 class="ltp-admin-title"><?php echo __( 'Link to Popup Settings', 'link_to_popup' ) ?></h1>
        <div class="ltp-admin">
        	<div class="ltp-admin-message" id="ltp-admin-message"></div>
        	<div class="ltp-admin-left">
        		<table class="ltp-admin-table wp-list-table widefat fixed striped" id="ltp-admin-table">
	        		<thead>
	        			<tr>
	        				<th><?php echo __( 'Selector', 'link_to_popup' ) ?></th>
	        				<th><?php echo __( 'Type', 'link_to_popup' ) ?></th>
	        				<th><?php echo __( 'Option', 'link_to_popup' ) ?></th>
	        			</tr>
	        		</thead>
	        		<tbody>
	        			<?php
	        				if ( $selectors['selectors'] ) {
	        					foreach ($selectors['selectors'] as $id => $item) {
	        			?>
		        					<tr>
				        				<td><?php echo $item['selector'] ?></td>
				        				<td><?php echo $types[$item['type']] ?></td>
				        				<td>
				        					<button class="button ltp-delete" data-id="<?php echo $id ?>"><?php echo __( 'Delete' ) ?></button>
				        				</td>
				        			</tr>
	        			<?php
	        					}
	        				} else {
	        			?>
	        					<tr>
			        				<td colspan="3" class="ltp-text-center"><?php echo __('No data', 'link_to_popup') ?></td>
			        			</tr>
	        			<?php
	        				}
	        			?>
	        		</tbody>
	        	</table>
        	</div>

        	<div class="ltp-admin-right">
        		<form id="ltp-admin-settings-form" data-admin-url="<?php echo $admin_url ?>" data-nonce="<?php echo $nonce ?>">
	        		<div class="ltp-admin-row">
	        			<label><?php echo __( 'Type', 'link_to_popup' ) ?></label>
	        			<select name="ltp_type" id="ltp-type">
	        				<?php
	        					foreach ( $types as $value => $type ) {
	        						echo '<option value="' . $value . '">' . $type . '</option>';
	        					}
	        				?>
	        			</select>
	        		</div>
	        		<div class="ltp-admin-row">
	        			<input type="text" name="ltp_selector" id="ltp-selector" />
	        		</div>
	        		<div class="ltp-admin-row">
	        			<button class="button" id="ltp-admin-settings-form-save"><?php echo __( 'Save', 'link_to_popup' ) ?></button>
	        		</div>
	        	</form>
        	</div>
        </div>
    </div>
<?php
}

add_action( 'wp_footer', 'link_to_popup_footer' );
function link_to_popup_footer () {
	$selectors = get_option( 'link_to_popup_selectors' );
	if ( isset( $selectors['selectors'] ) ) {
		$admin_url = admin_url( 'admin-ajax.php' );
		$nonce = wp_create_nonce( 'link_to_popup_get_selectors' );
		echo '<div id="link-to-popup-ajax" data-admin-url="' . $admin_url . '" data-nonce="' . $nonce . '"></div>';
	}
}

add_action( 'wp_ajax_link_to_popup_get_selectors', 'link_to_popup_get_selectors' );
function link_to_popup_get_selectors () {
	$verify_nonce = wp_verify_nonce( $_GET['nonce'], 'link_to_popup_get_selectors' );
	if ( !$verify_nonce ) {
      exit( 'No naughty business please' );
   } 

	$result['selectors'] = array();
	$selectors = get_option( 'link_to_popup_selectors' );
	if ( isset($selectors['selectors']) ) {
		foreach ( $selectors['selectors'] as $selector ) {
			$value = $selector['selector'];
			switch ($selector['type']) {
				case 'class':
					$value = '.' . $value;
					break;
				case 'id':
					$value = '#' . $value;
					break;
			}
			array_push( $result['selectors'], $value );
		}
	}
	link_to_popup_response_ajax( 'success', __( 'Data have been retrieved successfully', 'link_to_popup' ), $result );
}

add_action( 'wp_ajax_link_to_popup_save_selector', 'link_to_popup_save_selector' );
function link_to_popup_save_selector () {
	$verify_nonce = wp_verify_nonce( $_POST['nonce'], 'link_to_popup_settings' );
	if ( !$verify_nonce ) {
      exit( 'No naughty business please' );
   } 

	$type = link_to_popup_sanitize_text_field( $_POST['type'] );
	$selector = link_to_popup_sanitize_text_field( $_POST['selector'] );

	$types = unserialize( LINK_TO_POPUP_TYPES );
	if ( !isset( $types[$type] ) ) {
		link_to_popup_response_ajax( 'error', __( 'Invalid type', 'link_to_popup' ) );
	}

	switch ( $type ) {
		case 'class':
			$valid_class_id_name = preg_match( '/^[_a-zA-Z]+[_a-zA-Z0-9-]*$/', $selector );
			if ( !$valid_class_id_name ) {
				link_to_popup_response_ajax( 'error', __( 'Invalid class name', 'link_to_popup' ) );
			}
			break;
		
		case 'id':
			$valid_class_id_name = preg_match( '/^[_a-zA-Z]+[_a-zA-Z0-9-]*$/', $selector );
			if ( !$valid_class_id_name ) {
				link_to_popup_response_ajax( 'error', __( 'Invalid id name', 'link_to_popup' ) );
			}
			break;
	}

	$selectors = get_option( 'link_to_popup_selectors' );
	$exists = link_to_popup_selector_exists( $selectors, $type, $selector );
	if ( $exists ) {
		link_to_popup_response_ajax( 'error', __( 'Selector already exists', 'link_to_popup' ) );
	}

	if ( $selectors ) {
		$id = uniqid();
		$data = array(
			'type' => $type,
			'selector' => $selector
		);
		$selectors['selectors'][$id] = $data;
		update_option( 'link_to_popup_selectors', $selectors );
		link_to_popup_response_ajax( 'success', __( 'Data have been saved successfully', 'link_to_popup' ), array( 'id' => $id ) );
	} else {
		link_to_popup_response_ajax( 'error', __( 'Can not get selectors from the DB', 'link_to_popup' ) );
	}
}

add_action( 'wp_ajax_link_to_popup_remove_selector', 'link_to_popup_remove_selector' );
function link_to_popup_remove_selector () {
	$verify_nonce = wp_verify_nonce( $_POST['nonce'], 'link_to_popup_settings' );
	if ( !$verify_nonce ) {
      exit( 'No naughty business please' );
   } 

	$id = link_to_popup_sanitize_text_field( $_POST['id'] );
	$selectors = get_option( 'link_to_popup_selectors' );
	if ( isset($selectors['selectors'][$id]) ) {
		unset($selectors['selectors'][$id]);
		update_option( 'link_to_popup_selectors', $selectors );
		link_to_popup_response_ajax( 'success', __( 'Data have been removed successfully', 'link_to_popup' ) );
	} else {
		link_to_popup_response_ajax( 'error', __( 'Can not get selectors from the DB', 'link_to_popup' ) );
	}
}

function link_to_popup_response_ajax ( $status, $message, $data = null ) {
	$response = array(
		'status' => $status,
		'message' => $message,
		'data' => $data
	);
	echo json_encode( $response );
	exit;
}

function link_to_popup_sanitize_text_field ( $value ) {
	$value = trim( $value );
	$value = sanitize_text_field( $value );
	return $value;
}

function link_to_popup_selector_exists( $selectors, $type, $selector ) {
	foreach ( $selectors['selectors'] as $item ) {
		if ( $item['type'] == $type && $item['selector'] == $selector ) {
			return true;
		}
	}
	return false;
}