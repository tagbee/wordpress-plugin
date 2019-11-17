<?php
/*
Plugin Name:  TagΒee, Automatic Post Tagging
Plugin URI:   https://developer.wordpress.org/plugins/the-basics/
Description:  Add Tags to posts
Version:      1.0.10
Author:       TagΒee Team
Author URI:   https://tagbee.co
License:      GPLv3 or later
License URI:  https://www.gnu.org/licenses/gpl-3.0.html
*/

/*
Copyright (C) 2018  TagΒee

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
defined('ABSPATH') or die('Wordpress Plugin');
define('TAGBEE_VERSION', "1.0.10");
define("TAGBEE_NAMESPACE", "tagbee");
define("TAGBEE_INNER_PROPOSAL_ENDPOINT", "proposals");

require_once("lib/tagbee-client.php");

add_action('admin_menu', 'tagbee_admin_menu_actions');
add_action('admin_init', 'register_tagbee_plugin_settings');
add_action('save_post', 'tagbee_post_info', 10, 2);

/**
 * Admin Menu Action
 *
 * - Add Administration Menu Item
 * - Remove Tags Meta Box
 */
function tagbee_admin_menu_actions() {
    add_menu_page('TagBee', 'TagBee', 'administrator', 'TagBee', 'tagbee_settings_page', 'dashicons-tag', 64);
}

/** Register Plugin Settings */
function register_tagbee_plugin_settings() {
    register_setting('tagbee-settings-group', 'tagbee_api_key');
    register_setting('tagbee-settings-group', 'tagbee_api_key_secret');
    register_setting('tagbee-settings-group', 'tagbee_auto_tag');
}

/** Submit Final Post Info and Save Post Meta */
function tagbee_post_info_callback( $id, $post ) {

    $id = (int) $id;

    if(wp_is_post_revision( $post ) || wp_is_post_autosave($post)) return;
    if(!current_user_can( 'edit_posts' )) return;

    $client = new Tagbee_Client(get_option('tagbee_api_key'), get_option('tagbee_api_key_secret'));

    try {
        $autoProposalRequest = new Tagbee_Auto_Proposals_Request(
            $post,
            wp_get_post_tags($id),
            get_post_meta($id)
        );
    } catch (\Exception $e) {
        return;
    }

    $response = $client->postAutoProposals($autoProposalRequest);

    if (!is_a($response, WP_Error::class)) {

        $response = json_decode($response['body'], true);

        if (!$response || !isset($response['data'])) {
            return;
        }

        $data = $response['data'];
        $remoteId = $data['id'];

        if (!wp_is_uuid($remoteId)) {
            return;
        }

        update_post_meta($id, 'tagbee_api_id', $remoteId, '');

        $tags = array_map(function ($tag) {
            return $tag['tag'];
        }, $data['tags']);

        wp_set_post_tags($id, $tags, true);

        $client->putTags($remoteId, new Tagbee_Update_Tags_Request(wp_get_post_tags($id)));
    }

    return;
}

/** Decide if the this is a rest api request */
function tagbee_is_api_used() {
    return defined('REST_REQUEST') && REST_REQUEST;
}

function tagbee_post_info_rest( $post ) {
    tagbee_post_info_callback($post->ID, $post);
}

function tagbee_post_info( $id, $post ) {

    if (tagbee_is_api_used()) {
        add_action('rest_after_insert_post', 'tagbee_post_info_rest', 10, 2);
        return;
    }

    tagbee_post_info_callback($id, $post);
}

/** Plugin Settings Page */
function tagbee_settings_page() { ?>
    <div class="wrap">
        <h1>TagBee Settings</h1>
        <form method="post" action="options.php">
            <?php settings_fields('tagbee-settings-group'); ?>
            <?php do_settings_sections('tagbee-settings-group'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">API Key public</th>
                    <td><input type="text" name="tagbee_api_key" value="<?php echo esc_attr(get_option('tagbee_api_key')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">API Key Secret</th>
                    <td><input type="text" name="tagbee_api_key_secret" value="<?php echo esc_attr(get_option('tagbee_api_key_secret')); ?>" /></td>
                </tr>
            </table>
            <p>Get your <strong>API public and secret keys</strong> at <a href="https://tagbee.co" target="_blank">https://tagbee.co</a></p>
            <?php submit_button(); ?>
        </form>
    </div>
<?php
}
