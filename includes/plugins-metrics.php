<?php

function simple_tech_metrics_get_plugins_data() {
    // Obtenir les plugins actifs et inactifs
    $all_plugins = get_plugins();
    $active_plugins = get_option('active_plugins', []);
    $plugin_updates = get_site_transient('update_plugins'); // Vérifie les mises à jour disponibles

    $plugins_data = [];
    foreach ($all_plugins as $plugin_file => $plugin_info) {
        $plugin_dir = WP_PLUGIN_DIR . '/' . dirname($plugin_file);
        $plugin_size = simple_tech_metrics_calculate_folder_size($plugin_dir);
        $update_available = isset($plugin_updates->response[$plugin_file]); // Vérifie si une mise à jour est dispo

        $plugins_data[] = [
            'name'        => $plugin_info['Name'],
            'version'     => $plugin_info['Version'],
            'status'      => in_array($plugin_file, $active_plugins) ? 'active' : 'inactive',
            'size'        => $plugin_size,
            'update'      => is_plugin_update_available($plugin_file),
            'update_version' => $update_available ? $plugin_updates->response[$plugin_file]->new_version : null
        ];
    }

    return $plugins_data;
}

function is_plugin_update_available($plugin_file) {
    // Vérifier si une mise à jour est disponible pour un plugin
    $update_plugins = get_site_transient('update_plugins');
    if (!empty($update_plugins->response[$plugin_file])) {
        return true;
    }
    return false;
}

function simple_tech_metrics_display_plugins_table() {
    $plugins_data = simple_tech_metrics_get_plugins_data();

    echo '<h2>' . __('Plugins', SIMPLE_TECH_METRICS_TEXT_DOMAIN) . '</h2>';
    echo '<table class="widefat fixed striped">';
    echo '<thead>
            <tr>
                <th>' . __('Name', SIMPLE_TECH_METRICS_TEXT_DOMAIN) . '</th>
                <th>' . __('Version', SIMPLE_TECH_METRICS_TEXT_DOMAIN) . '</th>
                <th>' . __('Status', SIMPLE_TECH_METRICS_TEXT_DOMAIN) . '</th>
                <th>' . __('Size', SIMPLE_TECH_METRICS_TEXT_DOMAIN) . '</th>
                <th>' . __('Update Available', SIMPLE_TECH_METRICS_TEXT_DOMAIN) . '</th>
            </tr>
          </thead>';
    echo '<tbody>';
    foreach ($plugins_data as $plugin) {
        $tr_class = $plugin['status'] === 'active' ? 'active' : '';
        $plug_updt_class = $plugin['update'] ? 'wpmetrics-green' : '';
        $plugin_version = esc_html($plugin['version']);

        if ($plug_updt_class) {
            $tr_class = 'warning';
        }

        echo "<tr class='{$tr_class}'>";
        echo '<td>' . esc_html($plugin['name']) . '</td>';
        echo '<td>' . $plugin_version . '</td>';
        echo '<td>' . esc_html($plugin['status']) . '</td>';
        echo '<td>' . esc_html($plugin['size']) . '</td>';
        if ($plugin['update']) {
            $update_version = esc_html($plugin['update_version']);
            echo "<td>
                    <span class='{$plug_updt_class}'>
                        " . __('Yes', SIMPLE_TECH_METRICS_TEXT_DOMAIN) . " : 
                    </span>&nbsp;&nbsp;<span>{$plugin_version} -> {$update_version}</span>
                  </td>";
        } else {
            echo "<td>" . __('No', SIMPLE_TECH_METRICS_TEXT_DOMAIN) . "</td>";
        }
        
        echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
}
