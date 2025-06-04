<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// GitHub Update Checker for Optha Vision Assessment plugin.
// Repository where releases are published.
define( 'OPTHA_UPDATE_REPO', 'https://api.github.com/repos/UniBed/Optha' );

define( 'OPTHA_BASENAME', plugin_basename( OPTHA_PLUGIN_FILE ) );

/**
 * Fetch the latest release information from GitHub.
 *
 * Results are cached for one hour to avoid hitting API rate limits.
 *
 * @return object|false
 */
function optha_get_latest_release() {
    $cached = get_transient( 'optha_release_info' );
    if ( false !== $cached ) {
        return $cached;
    }

    $response = wp_remote_get( OPTHA_UPDATE_REPO . '/releases/latest', array(
        'headers' => array(
            'Accept'     => 'application/vnd.github.v3+json',
            'User-Agent' => 'WordPress/' . get_bloginfo( 'version' ),
        ),
        'timeout' => 15,
    ) );

    if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
        return false;
    }

    $release = json_decode( wp_remote_retrieve_body( $response ) );
    if ( $release ) {
        set_transient( 'optha_release_info', $release, HOUR_IN_SECONDS );
    }

    return $release;
}

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

    $release = optha_get_latest_release();
    if ( ! $release || empty( $release->tag_name ) ) {
        return $transient;
    }

    $remote_version = ltrim( $release->tag_name, 'v' );

    $download_url = $release->zipball_url;
    if ( ! empty( $release->assets ) ) {
        foreach ( $release->assets as $asset ) {
            if ( false !== stripos( $asset->name, 'optha-vision-assessment.zip' ) ) {
                $download_url = $asset->browser_download_url;
                break;
            }
        }
    }

    if ( version_compare( $transient->checked[ OPTHA_BASENAME ], $remote_version, '<' ) ) {
        $plugin = (object) array(
            'slug'        => 'optha-vision-assessment',
            'new_version' => $remote_version,
            'url'         => 'https://github.com/UniBed/Optha',
            'package'     => $download_url,
        );
        $transient->response[ OPTHA_BASENAME ] = $plugin;
    }

    return $transient;
}
add_filter( 'pre_set_site_transient_update_plugins', 'optha_check_github_update' );

/**
 * Provide plugin information in the modal displayed by WordPress.
 */
function optha_plugin_info( $false, $action, $args ) {
    if ( 'plugin_information' !== $action || 'optha-vision-assessment' !== $args->slug ) {
        return false;
    }

    $release = optha_get_latest_release();
    if ( ! $release ) {
        return false;
    }

    $download_url = $release->zipball_url;
    if ( ! empty( $release->assets ) ) {
        foreach ( $release->assets as $asset ) {
            if ( false !== stripos( $asset->name, 'optha-vision-assessment.zip' ) ) {
                $download_url = $asset->browser_download_url;
                break;
            }
        }
    }

    $plugin = new stdClass();
    $plugin->name          = 'Optha Vision Assessment';
    $plugin->version       = ltrim( $release->tag_name, 'v' );
    $plugin->download_link = $download_url;
    $plugin->sections      = array( 'description' => 'Automatic updates from GitHub releases.' );

    return $plugin;
}
add_filter( 'plugins_api', 'optha_plugin_info', 10, 3 );

/**
 * Clear cached release info after an update completes so the next check
 * pulls fresh data from GitHub.
 */
function optha_clear_update_cache( $upgrader, $hook_extra ) {
    if ( ! empty( $hook_extra['plugins'] ) && in_array( OPTHA_BASENAME, $hook_extra['plugins'], true ) ) {
        delete_transient( 'optha_release_info' );
    }
}
add_action( 'upgrader_process_complete', 'optha_clear_update_cache', 10, 2 );

