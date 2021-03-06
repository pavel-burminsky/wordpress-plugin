<?php

// Used to set cookie after login - we need to do it through JS because of different proxies and cache setups
function peerboard_set_auth_cookie($cookie, $redirect = '') {
  echo "<script>
  function p_b_setCookie(value) {
    let expires = 'expires=0;';
    if (value === '') {
      expires = 'expires=-1;';
    }
    document.cookie = 'wp-peerboard-auth=' + value + '; ' + expires + ' path=/;';
  }
  p_b_setCookie('$cookie');";
  if ($redirect !== '') {
    echo "window.location.replace('$redirect');";
  }
  echo '</script>';
}

function peerboard_get_domain() {
  $info = peerboard_bloginfo_array();
  return $info['hosting']['domain'];
}

function peerboard_bloginfo_array() {
    $fields = array('name', 'wpurl', 'admin_email');
    $data = array();
    foreach($fields as $field) {
      $field_data = get_bloginfo($field);
      if ($field === 'wpurl') {
        $field_data = explode('://', $field_data);
        $field_data = $field_data[1];
      }
      $data[$field] = $field_data;
    }
    $data['type'] = 'wordpress';
    return array(
      'name' => $data['name'],
      'admins' => array(
        array(
          'email' => $data['admin_email'],
        )
      ),
      'hosting' => array(
        'domain' => $data['wpurl'],
        'type' => 'wordpress',
      )
    );
}

function peerboard_base64url_encode($data)
{
  // First of all you should encode $data to Base64 string
  $b64 = base64_encode($data);

  // Make sure you get a valid result, otherwise, return FALSE, as the base64_encode() function do
  if ($b64 === false) {
    return false;
  }

  // Convert Base64 to Base64URL by replacing “+” with “-” and “/” with “_”
  $url = strtr($b64, '+/', '-_');

  // Remove padding character from the end of line and return the Base64URL result
  return rtrim($url, '=');
}

function peerboard_is_embed_page() {
  return ( get_the_ID() == get_option( 'peerboard_post' ) );
}

function peerboard_get_tail_path($prefix) {
  $r = $_SERVER['REQUEST_URI'];
  // Trim /peerboard from request - uses for login redirect
  $trimmed = substr($r, strlen($prefix) + 1);
  if ($trimmed === "") {
    $trimmed = "/";
  }
	return $trimmed;
}

function peerboard_get_auth_hash($params, $secret) {
  $strings = array();
  foreach ($params as $key => $value) {
    $strings[] = $key . '=' . $value;
  }
  sort($strings);

  return hash_hmac('sha256', implode("\n", $strings), hash('sha256', $secret, true));
}

function peerboard_get_options($data) {
  if (is_wp_error( $data )) {
    return array("error" => $data);
  }
  $integration_type = $data['hosting']['type'];
  $mode = 'proxy';
  if ($integration_type === 'sdk') {
    $mode = 'sdk';
  }

  return array(
    'community_id' => $data['id'],
    'auth_token' => $data['auth_token'],
    'prefix' => $data['hosting']['path'],
    'redirect' => $data['url'],
    'mode' => $mode,
  );
}

function peerboard_update_post_slug($slug) {
  wp_update_post( array(
    "ID" => get_option('peerboard_post'),
    "post_name" => $slug,
  ), false, false );
}
