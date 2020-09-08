<?php
add_action( 'activated_plugin', function( $plugin ) {
  global $peerboard_options;
  $peerboard_options = get_option( 'peerboard_options', array() );
  if (count($peerboard_options) === 0) {
		peerboard_send_analytics('activate_plugin');
    $peerboard_options = peerboard_get_options(peerboard_create_community());
    update_option('peerboard_options', $peerboard_options);
  }
	if( $plugin == plugin_basename( __FILE__ ) && array_key_exists('redirect', $peerboard_options)) {
		if ($peerboard_options['redirect']) {
			$url = $peerboard_options['redirect'];
			if (PEERBOARD_REDIRECT_URL !== '') {
				$url = PEERBOARD_REDIRECT_URL . '?redirect=' . urlencode($peerboard_options['redirect']) . '&communityId=' . $peerboard_options['community_id'];
			}
			exit( wp_redirect($url));
		}
	}
});

function peerboard_install() {
  global $peerboard_options;
  if ( ! current_user_can( 'activate_plugins' ) )
    return;

  $peerboard_post = get_option("peerboard_post");
	if (is_null($peerboard_post) || !$peerboard_post) {
		$post_data = array(
			'post_title'    => 'Community',
			'post_alias'    => 'community',
			'post_content'  => '',
			'post_status'   => 'publish',
			'post_type'     => 'page',
			'post_author'   => 1
		);
		$post_id = wp_insert_post( $post_data );
		update_option( "peerboard_post", $post_id);
	}
}

function peerboard_uninstall() {
	global $peerboard_options;
  if ( ! current_user_can( 'activate_plugins' ) ) return;
  $post_id = get_option('peerboard_post');
  wp_delete_post($post_id, true);
	peerboard_send_analytics('uninstall_plugin', $peerboard_options['community_id']);
  // TODO: send post integration request to switch community to /id
  // Probably send some sort of notifaction, about availability on our url
  delete_option('peerboard_post');
  delete_option('peerboard_options');
}

register_activation_hook( __FILE__, 'peerboard_install');
register_uninstall_hook( __FILE__, 'peerboard_uninstall');