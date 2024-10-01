<?php


if (!defined('ABSPATH')) {
    exit;
}




add_action('admin_menu', 'wp_pinterest_api_menu');

function wp_pinterest_api_menu() {
    add_options_page(
        __('WP Pinterest', 'textdomain'),
        __('WP Pinterest', 'textdomain'),
        'manage_options',
        'wp-pinterest-api',
        'wp_pinterest_api_options_page'
    );
}


function wp_pinterest_api_options_page() {
    ?>
    <div class="wrap">
        <h1><?php _e('WP Pinterest : Renseignez vos clés API', 'textdomain'); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('wp_pinterest_api_options_group');
            do_settings_sections('wp_pinterest_api');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e('Clé API d\'accès Pinterest', 'textdomain'); ?><br><p>Token Prod</p></th>
                    <td><input type="text" name="pinterest_access_token" value="<?php echo esc_attr(get_option('pinterest_access_token')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Clé API pour ajouter des épingles', 'textdomain'); ?><br><p>Token Sandbox</p></th>
                    <td><input type="text" name="pinterest_add_pin_token" value="<?php echo esc_attr(get_option('pinterest_add_pin_token')); ?>" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

add_action('admin_init', 'wp_pinterest_api_register_settings');

function wp_pinterest_api_register_settings() {
    register_setting('wp_pinterest_api_options_group', 'pinterest_access_token');
    register_setting('wp_pinterest_api_options_group', 'pinterest_add_pin_token');
}



/* ajouter action groupée */ 
add_filter('bulk_actions-upload', 'register_pinterest_bulk_action');

function register_pinterest_bulk_action($bulk_actions) {
    $bulk_actions['save_to_pinterest'] = __('Enregistrer sur Pinterest', 'textdomain');
    return $bulk_actions;
}
