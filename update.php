<?php

if (!function_exists("github_updater_plugin_wordpress_function_v1")) {

    function github_updater_plugin_wordpress_function_v1($config)
    {

        if (!is_admin()) {
            return;
        }

        /**
         * Configuración base
         */
        $plugin_slug = basename(rtrim($config['dir'], '/'));

        $plugin_file = $plugin_slug . '/' . $config['file'];

        /**
         * Forzar refresh cache plugins
         */
        add_action('admin_init', function () {

            if (!get_transient('github_updater_plugin_wordpress_check')) {

                delete_site_transient('update_plugins');

                set_transient(
                    'github_updater_plugin_wordpress_check',
                    true,
                    60
                );
            }
        });

        /**
         * Buscar updates
         */
        add_filter(
            'pre_set_site_transient_update_plugins',
            function ($transient) use ($config, $plugin_slug, $plugin_file) {

                if (empty($transient->checked)) {
                    return $transient;
                }

                /**
                 * API GitHub
                 */
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
                 * Version GitHub
                 */
                $latest_version = ltrim(
                    trim($release->tag_name),
                    'v'
                );

                /**
                 * Version instalada
                 */
                if (!function_exists('get_plugin_data')) {
                    require_once ABSPATH . 'wp-admin/includes/plugin.php';
                }

                $plugin_path =
                    trailingslashit($config['dir']) .
                    $config['file'];

                $plugin_data = get_plugin_data($plugin_path);

                $current_version =
                    $plugin_data['Version'];

                /**
                 * Comparar versiones
                 */
                if (
                    version_compare(
                        $current_version,
                        $latest_version,
                        '<'
                    )
                ) {

                    /**
                     * Buscar ZIP asset
                     */
                    $package_url = null;

                    if (!empty($release->assets)) {

                        foreach ($release->assets as $asset) {

                            if (
                                !empty($asset->browser_download_url) &&
                                str_ends_with(
                                    strtolower($asset->name),
                                    '.zip'
                                )
                            ) {

                                $package_url =
                                    $asset->browser_download_url;

                                break;
                            }
                        }
                    }

                    /**
                     * fallback zipball
                     */
                    if (
                        !$package_url &&
                        !empty($release->zipball_url)
                    ) {

                        $package_url =
                            $release->zipball_url;
                    }

                    /**
                     * Registrar update
                     */
                    $transient->response[$plugin_file] = (object) [
                        'slug'        => $plugin_slug,
                        'plugin'      => $plugin_file,
                        'new_version' => $latest_version,
                        'package'     => $package_url,
                        'url'         => 'https://github.com/' . $config['path_repository'],
                        'tested'      => get_bloginfo('version'),
                        'requires'    => '5.0',
                    ];
                }

                return $transient;
            }
        );

        /**
         * FIX IMPORTANTE:
         * Renombrar carpeta extraída del ZIP
         *
         * GitHub extrae:
         * repo-hash
         *
         * WordPress necesita:
         * plugin-slug
         */
        add_filter(
            'upgrader_source_selection',
            function (
                $source,
                $remote_source,
                $upgrader,
                $hook_extra
            ) use ($plugin_slug) {

                if (
                    empty($hook_extra['plugin'])
                ) {
                    return $source;
                }

                /**
                 * Ruta final correcta
                 */
                $corrected_source =
                    trailingslashit($remote_source) .
                    $plugin_slug;

                /**
                 * Si ya existe eliminar
                 */
                if (file_exists($corrected_source)) {
                    WP_Filesystem();
                    global $wp_filesystem;
                    $wp_filesystem->delete(
                        $corrected_source,
                        true
                    );
                }

                /**
                 * Renombrar carpeta
                 */
                rename(
                    $source,
                    $corrected_source
                );

                return $corrected_source;
            },
            10,
            4
        );

        /**
         * Mostrar botón actualizar
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

                $links[] = '
                    <a 
                        href="' . esc_url($actualizar_url) . '" 
                        style="color:#2271b1;font-weight:600;"
                    >
                        Actualizar
                    </a>

                    <style>
                        tr.plugin-update-tr[data-slug="' . $plugin_slug . '"] a,
                        tr.plugin-update-tr[data-slug="' . $plugin_slug . '"] a + *{
                            display:none;
                        }
                    </style>
                ';

                return $links;
            },
            10,
            2
        );
    }
}