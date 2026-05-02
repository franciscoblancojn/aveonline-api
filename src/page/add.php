<?php

// use franciscoblancojn\wordpress_utils\FWUSystemLog;
// 1. Crear menú en el admin
add_action('admin_menu', function () {
    add_menu_page(
        'Aveonline Api Configuración', // Título página
        'Aveonline Api',              // Nombre en menú
        'manage_options',        // Permisos
        AVE_API_KEY,      // Slug
        'AVE_API_PAGE_VIEW'  // Callback
    );
});

// 2. Página HTML
function AVE_API_PAGE_VIEW()
{
    require_once AVE_API_DIR . 'src/page/page.php';
}
