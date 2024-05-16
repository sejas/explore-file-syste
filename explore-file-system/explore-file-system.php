<?php
/**
 * Plugin Name: Explore File System
 * Description: A plugin to list files and folders in a given path.
 * Version: 1.0
 * Author: Antonio Sejas
 */

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
    $path = isset($_GET['path']) ? sanitize_text_field($_GET['path']) : ABSPATH . 'wp-content';
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
    
    if (!file_exists($path)) {
        echo '<p>The specified path does not exist.</p>';
        return;
    }
    
    $items2 = $wp_filesystem->dirlist($path);
    ?><!--pre><?php esc_html(print_r($items2, true)); ?></pre!--><?php

    // Display stats of the given path with a expandable dtails summary
    $path_stats = stat($path);
    echo '<details>
            <summary>Current path Stats</summary>
            <ul>
                <li>Path: ' . esc_html($path) . '</li>
                <li> wp-exists: ' . $wp_filesystem->exists($path) . '</li>
                <li> wp-is_readable: ' . $wp_filesystem->is_readable($path) . '</li>
                <li> wp-is_writable: ' . $wp_filesystem->is_writable($path) . '</li>
                <li> wp-is_file: ' . var_export($wp_filesystem->is_file($path), true) . '</li>
                <li> wp-is_dir: ' . var_export($wp_filesystem->is_dir($path), true) . '</li>
                <li> is_link: ' . var_export(is_link($path), true) . '</li>
                <li>is_dir: ' . (is_dir($path) ? 'Directory' : 'File') . '</li>
                <li>is_file: ' . (is_file($path) ? 'File' : 'Directory') . '</li>
                <li>Permissions: ' . esc_html(var_export(fileperms($path), true)) . '</li>
                <li>Owner: ' . esc_html(fileowner($path)) . '</li>
                <li>Group: ' . esc_html(filegroup($path)) . '</li>
                <li>Size: ' . esc_html(size_format($path_stats['size'])) . '</li>
                <li> Stats: ' . esc_html(var_export($path_stats, true)) . '</li>
            </ul>
          </details>';
    

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