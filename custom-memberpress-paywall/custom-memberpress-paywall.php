<?php
/**
 * Plugin Name: Custom MemberPress Paywall
 * Description: Overrides MemberPress paywall to allow X free views and bypass for specified Rule IDs (X and Rule IDs are editable in settings).
 * Version: 1.1
 * Author: Emil Amirakulov
 * Author URI: https://www.upwork.com/freelancers/~01934ce3183276c713
 * License: GPL2
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define settings option names
define('CUSTOM_MEPR_PAYWALL_VIEWS_OPTION', 'custom_mepr_paywall_views');
define('CUSTOM_MEPR_PAYWALL_RULE_IDS_OPTION', 'custom_mepr_paywall_rule_ids');

// Add admin menu to WordPress dashboard
add_action('admin_menu', 'custom_mepr_paywall_admin_menu');
function custom_mepr_paywall_admin_menu() {
    add_options_page(
        'MemberPress Paywall Settings',
        'MemberPress Paywall',
        'manage_options',
        'custom-mepr-paywall',
        'custom_mepr_paywall_settings_page'
    );
}

// Display settings page in WordPress admin
function custom_mepr_paywall_settings_page() {
    ?>
    <div class="wrap">
        <h1>Custom MemberPress Paywall Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('custom_mepr_paywall_settings');
            do_settings_sections('custom-mepr-paywall');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Register settings fields
add_action('admin_init', 'custom_mepr_paywall_register_settings');
function custom_mepr_paywall_register_settings() {
    register_setting('custom_mepr_paywall_settings', CUSTOM_MEPR_PAYWALL_VIEWS_OPTION, [
        'sanitize_callback' => 'intval' // Ensures only integers are saved
    ]);
    
    register_setting('custom_mepr_paywall_settings', CUSTOM_MEPR_PAYWALL_RULE_IDS_OPTION, [
        'sanitize_callback' => 'custom_mepr_sanitize_rule_ids' // Custom function for sanitizing Rule IDs
    ]);
    
    // Sanitization function for Rule IDs (allows numbers & commas)
    function custom_mepr_sanitize_rule_ids($input) {
        return preg_replace('/[^0-9,]/', '', $input); // Removes anything that's not a number or comma
    }

    // Sanitization function for Rule IDs (allows numbers & commas)
    function custom_mepr_sanitize_rule_ids($input) {
        return preg_replace('/[^0-9,]/', '', $input); // Removes anything that's not a number or comma
    }
    
    add_settings_section('custom_mepr_paywall_section', 'Paywall Settings', null, 'custom-mepr-paywall');
    
    add_settings_field(
        'custom_mepr_paywall_views',
        'Number of Free Views',
        'custom_mepr_paywall_views_field',
        'custom-mepr-paywall',
        'custom_mepr_paywall_section'
    );

    add_settings_field(
        'custom_mepr_paywall_rule_ids',
        'Allowed Rule IDs (comma-separated)',
        'custom_mepr_paywall_rule_ids_field',
        'custom-mepr-paywall',
        'custom_mepr_paywall_section'
    );
}

// Render input fields
function custom_mepr_paywall_views_field() {
    $num_views = get_option(CUSTOM_MEPR_PAYWALL_VIEWS_OPTION, 2); // Default: 2 free views
    echo '<input type="number" name="' . CUSTOM_MEPR_PAYWALL_VIEWS_OPTION . '" value="' . esc_attr($num_views) . '" min="0">';
}

function custom_mepr_paywall_rule_ids_field() {
    $rule_ids = get_option(CUSTOM_MEPR_PAYWALL_RULE_IDS_OPTION, '71'); // Default: 71
    echo '<input type="text" name="' . CUSTOM_MEPR_PAYWALL_RULE_IDS_OPTION . '" value="' . esc_attr($rule_ids) . '" placeholder="e.g., 71, 102, 200">';
}

// Remove default paywall and apply custom logic
add_action('init', function () {
    remove_filter('mepr-pre-run-rule-content', ['MeprPayWallCtrl', 'paywall_allow_through_content'], 10);
    remove_filter('mepr-pre-run-rule-redirection', ['MeprPayWallCtrl', 'paywall_allow_through_redirection'], 10);

    add_filter('mepr-pre-run-rule-content', 'custom_mepr_paywall_content', 10, 3);
    add_filter('mepr-pre-run-rule-redirection', 'custom_mepr_paywall_redirection', 10, 3);
});

// Override paywall content check
function custom_mepr_paywall_content($protect, $post, $uri) {
    return custom_mepr_paywall_allow_through() ? false : $protect;
}

// Override paywall redirection check
function custom_mepr_paywall_redirection($protect, $uri, $delim) {
    return custom_mepr_paywall_allow_through('uri') ? false : $protect;
}

// Custom paywall logic with editable free views and multiple rule IDs
function custom_mepr_paywall_allow_through($type = 'content') {
    $post = MeprUtils::get_current_post();
    $is_logged_in = MeprUtils::is_user_logged_in();

    // Block guest users completely and remove cookie
    if (!$is_logged_in) {
        if (!headers_sent()) {
            setcookie(MeprPayWallCtrl::$cookie_name, "", time() - 3600, "/");
            unset($_COOKIE[MeprPayWallCtrl::$cookie_name]); // Remove from the global $_COOKIE array
        }
        return false;
    }

    // Fetch Rule IDs from settings (comma-separated string)
    $allowed_rule_ids = get_option(CUSTOM_MEPR_PAYWALL_RULE_IDS_OPTION, '71');
    $rule_ids = array_map('trim', explode(',', $allowed_rule_ids)); // Convert to an array

    // Allow users who match ANY of the specified Rule IDs to bypass paywall
    foreach ($rule_ids as $rule_id) {
        if (MeprRule::is_allowed_by_rule((int) $rule_id)) {
            return true;
        }
    }

    // Do nothing if the user is a bot
    if (MeprPayWallCtrl::verify_bot()) {
        return false;
    }

    // Check if Post is excluded from the PayWall
    if ($post !== false && MeprPayWallCtrl::is_excluded($post)) {
        return false;
    }

    $mepr_options = MeprOptions::fetch();

    // Get the free views limit from settings (default: 2)
    $free_views_limit = get_option(CUSTOM_MEPR_PAYWALL_VIEWS_OPTION, 2);

    if ($mepr_options->paywall_enabled && $free_views_limit > 0) {
        // Get the current view count from the cookie (decode only if set)
        $num_views = isset($_COOKIE[MeprPayWallCtrl::$cookie_name]) ? (int) base64_decode($_COOKIE[MeprPayWallCtrl::$cookie_name]) : 0;

        // If checking URIs, increment views **before checking the condition**
        if ($type === 'uri') {
            $num_views += 1; // Increment first

            // Set the updated view count in the cookie **before output**
            if (!headers_sent()) {
                setcookie(MeprPayWallCtrl::$cookie_name, base64_encode($num_views), time() + (30 * 24 * 60 * 60), "/");
            }
        }

        // Now check if views exceed the limit (correct logic)
        return $num_views < $free_views_limit; // Less than the limit
    }

    return false;
}