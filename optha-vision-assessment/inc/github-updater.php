<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// GitHub Update Checker for Optha Vision Assessment plugin.
define( 'OPTHA_UPDATE_REPO', 'https://api.github.com/repos/UniBed/Optha' );

define( 'OPTHA_BASENAME', plugin_basename( OPTHA_PLUGIN_FILE ) );

/**
 * Check GitHub for plugin updates.
 *
 * @param object $transient Plugin update data.
 * @return object
 */
function optha_check_github_update( $transient ) {
    if ( empty( $transient->checked[ OPTHA_BASENAME ] ) ) {
        return $transient;
    }

    $response = wp_remote_get( OPTHA_UPDATE_REPO . '/releases/latest', array(
        'headers' => array( 'Accept' => 'application/vnd.github.v3+json' ),
        'timeout' => 10,
    ) );

    if ( is_wp_error( $response ) ) {
        return $transient;
    }

    $release = json_decode( wp_remote_retrieve_body( $response ) );
    if ( empty( $release->tag_name ) ) {
        return $transient;
    }

    $remote_version = ltrim( $release->tag_name, 'v' );
    if ( version_compare( OPTHA_VERSION, $remote_version, '<' ) ) {
        $plugin = (object) array(
            'slug'        => 'optha-vision-assessment',
            'new_version' => $remote_version,
            'url'         => 'https://github.com/example/optha-vision-assessment',
            'package'     => $release->zipball_url,
        );
        $transient->response[ OPTHA_BASENAME ] = $plugin;
    }

    return $transient;
}
add_filter( 'pre_set_site_transient_update_plugins', 'optha_check_github_update' );

