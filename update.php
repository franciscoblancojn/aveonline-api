<?php

if (!function_exists("github_updater_plugin_wordpress_function_v2")) {

    function github_updater_plugin_wordpress_function_v2($config)
    {

        if (!is_admin()) {
            return;
        }

        $plugin_slug = basename(rtrim($config['dir'], '/'));

        $plugin_file_php = $config['file'];

        $plugin_file = $plugin_slug . '/' . $plugin_file_php;

        add_filter(
            'site_transient_update_plugins',
            function ($transient) use (
                $config,
                $plugin_slug,
                $plugin_file,
                $plugin_file_php
            ) {

                if (empty($transient->checked)) {
                    return $transient;
                }

                $github_api_url =
                    'https://api.github.com/repos/' .
                    $config['path_repository'] .
                    '/releases/latest';

                $response = wp_remote_get($github_api_url, [
                    'headers' => [
                        'User-Agent' => 'WordPress-GitHub-Updater',
                        'Accept' => 'application/vnd.github+json',
                    ],
                    'timeout' => 20,
                ]);

                if (is_wp_error($response)) {
                    return $transient;
                }

                $release = json_decode(
                    wp_remote_retrieve_body($response)
                );

                if (
                    empty($release) ||
                    empty($release->tag_name)
                ) {
                    return $transient;
                }

                /**
                 * VERSION GITHUB
                 */
                $latest_version = ltrim(
                    trim($release->tag_name),
                    'v'
                );

                /**
                 * VERSION INSTALADA
                 */
                if (!function_exists('get_plugin_data')) {
                    require_once ABSPATH . 'wp-admin/includes/plugin.php';
                }

                $plugin_path =
                    trailingslashit($config['dir']) .
                    $plugin_file_php;

                $plugin_data =
                    get_plugin_data($plugin_path);

                $current_version =
                    $plugin_data['Version'];

                /**
                 * COMPARAR VERSIONES
                 */
                if (
                    version_compare(
                        $current_version,
                        $latest_version,
                        '<'
                    )
                ) {

                    $transient->response[$plugin_file] = (object) [
                        'slug' => $plugin_slug,
                        'plugin' => $plugin_file,
                        'new_version' => $latest_version,

                        /**
                         * IMPORTANTE
                         */
                        'package' => $release->zipball_url,

                        'url' => 'https://github.com/' . $config['path_repository'],
                    ];
                }

                return $transient;
            }
        );

        /**
         * RENOMBRAR HASH ZIP
         */
        add_filter(
            'upgrader_source_selection',
            function (
                $source,
                $remote_source,
                $upgrader,
                $hook_extra
            ) use ($plugin_slug) {

                global $wp_filesystem;

                /**
                 * carpeta final
                 */
                $corrected_source =
                    trailingslashit($remote_source) .
                    $plugin_slug;

                /**
                 * borrar existente
                 */
                if (
                    $wp_filesystem->exists(
                        $corrected_source
                    )
                ) {

                    $wp_filesystem->delete(
                        $corrected_source,
                        true
                    );
                }

                /**
                 * mover hash => plugin real
                 */
                $wp_filesystem->move(
                    $source,
                    $corrected_source
                );

                return $corrected_source;
            },
            10,
            4
        );

        /**
         * BOTON UPDATE
         */
        add_filter(
            'plugin_action_links_' . $config['basename'],
            function ($links, $file) use ($config, $plugin_slug) {

                if ($file !== $config['basename']) {
                    return $links;
                }

                $actualizar_url = wp_nonce_url(
                    admin_url(
                        'update.php?action=upgrade-plugin&plugin=' . $file
                    ),
                    'upgrade-plugin_' . $file
                );

                $links[] =
                    '<a href="' .
                    esc_url($actualizar_url) .
                    '" style="color:#2271b1;font-weight:600;">
                        Actualizar
                    </a>
                    <style>
                        tr.plugin-update-tr[data-slug="'.$plugin_slug.'"] a,
                        tr.plugin-update-tr[data-slug="'.$plugin_slug.'"] a + *{
                            display:none;
                        }
                    </style>
                    ';

                return $links;
            },
            10,
            2
        );

        /**
         * REFRESH
         */
        delete_site_transient('update_plugins');
    }
}