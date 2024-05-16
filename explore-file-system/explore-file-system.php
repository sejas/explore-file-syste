<?php
/**
 * Plugin Name: Explore File System
 * Description: A plugin to list files and folders in a given path.
 * Version: 1.0
 * Author: Antonio Sejas
 */

// Enqueue the custom JavaScript
add_action('admin_enqueue_scripts', 'flp_enqueue_scripts');

function flp_enqueue_scripts($hook) {
    if ($hook != 'toplevel_page_file-listing-plugin') {
        return;
    }
    wp_enqueue_script('flp-script', plugin_dir_url(__FILE__) . 'explore-file-system.js', array(), null, true);
}

// Add a menu item to the admin menu
add_action('admin_menu', 'flp_add_admin_menu');

function flp_add_admin_menu() {
    add_menu_page(
        'File Listing Plugin', 
        'File Listing', 
        'manage_options', 
        'file-listing-plugin', 
        'flp_display_file_listing', 
        'dashicons-admin-site', 
        6
    );
}

// Display the file listing form and results
function flp_display_file_listing() {
    $path = isset($_GET['path']) ? sanitize_text_field($_GET['path']) : '';
    ?>
    <div class="wrap">
        <h1>File Listing Plugin</h1>
        <form id="flp-form" method="get" action="">
            <input type="hidden" name="page" value="file-listing-plugin">
            <input type="text" id="flp_path" name="path" placeholder="Enter path" style="width: 300px;" value="<?php echo esc_attr($path); ?>" required>
            <input type="submit" name="flp_submit" class="button button-primary" value="List Files">
            <?php 
                if (!empty($path)) {
                    $up_path = dirname($path);
                    echo '<a href="' . add_query_arg('path', $up_path) . '" class="button">⬆️</a>';
                }
            ?>
        </form>
        <?php

        if (!empty($path)) {
            flp_list_files($path);
        }
        ?>
    </div>
    <?php
}

// List files and folders in the given path
function flp_list_files($path) {
    global $wp_filesystem;
    include_once ABSPATH . '/wp-admin/includes/file.php';
    WP_Filesystem();
    
    if ($wp_filesystem->exists($path)) {
        echo '<p>File exists</p>';
    } else {
        echo '<p>File does not exist</p>';
    }

    if (!file_exists($path)) {
        echo '<p>The specified path does not exist.</p>';
        return;
    }
    
    $items2 = $wp_filesystem->dirlist($path);
    ?><pre><?php var_dump($items2); ?></pre><?php
    return;

    $items = scandir($path);
    if ($items === false) {
        echo '<p>Unable to read the directory.</p>';
        return;
    }

    echo '<table class="widefat fixed" cellspacing="0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Permissions</th>
                    <th>Type</th>
                    <th>Size</th>
                    <th>Last Updated</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>';

    foreach ($items as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }

        $item_path = $path . DIRECTORY_SEPARATOR . $item;
        $is_dir = is_dir($item_path);
        $permissions = substr(sprintf('%o', fileperms($item_path)), -4);
        $size = $is_dir ? '-' : filesize($item_path);
        $last_updated = date("Y-m-d H:i:s", filemtime($item_path));
        $created = date("Y-m-d H:i:s", filectime($item_path));

        $link = $is_dir ? add_query_arg('path', $item_path) : '#';
        $file_name = $is_dir ? "<a href='$link'>" . esc_html($item) . "</a>" : esc_html($item);
        echo '<tr>
                <td>' . $file_name . '</td>
                <td>' . esc_html($permissions) . '</td>
                <td>' . ($is_dir ? 'Directory' : 'File') . '</td>
                <td>' . ($is_dir ? '-' : esc_html(size_format($size))) . '</td>
                <td>' . esc_html($last_updated) . '</td>
                <td>' . esc_html($created) . '</td>
              </tr>';
    }

    echo '</tbody></table>';
}