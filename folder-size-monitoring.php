<?php
/*
Plugin Name: Folder Size Monitoring
Plugin URI: https://drive.google.com/drive/folders/1R9Qu1buzvv6eRwBg5gMpZj9Lb60rzH6U?usp=sharing
Description: Desde el escritorio de WordPress, puede conocer y monitorear el tamaño de las principales carpetas y base de datos de su sitio web
Version: 1.0
Author: Invita Sutil
Author URI: https://youtube.com/@invitasutil
License: GPL2
*/



function calcular_tamano_carpeta($dir) {
    $tamano = 0;
    if (is_dir($dir)) {
        if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
                if ($file == '.' || $file == '..') continue;
                if (is_dir($dir . '/' . $file)) {
                    $tamano += calcular_tamano_carpeta($dir . '/' . $file);
                } else {
                    $tamano += filesize($dir . '/' . $file);
                }
            }
            closedir($dh);
        }
    }
    return $tamano;
}

function mostrar_estadisticas_tamano_sitio_escritorio() {
    $upload_dir = wp_upload_dir();
    $dir_upload = $upload_dir['basedir'];
    $tamano_upload = calcular_tamano_carpeta($dir_upload);
    $tamano_upload /= 1048576; // Convertir a Mbyte
    echo '<p>La carpeta <b>upload</b> tiene: <h2>' . round($tamano_upload, 2) . ' Mb</h2></p>';

    $dir_plugin = WP_PLUGIN_DIR;
    $tamano_plugin = calcular_tamano_carpeta($dir_plugin);
    $tamano_plugin /= 1048576; // Convertir a Mbyte
    echo '<p>La carpeta <b>plugin</b> tiene: <h2>' . round($tamano_plugin, 2) . ' Mb</h2></p>';

    $dir_temas = get_theme_root();
    $tamano_temas = calcular_tamano_carpeta($dir_temas);
    $tamano_temas /= 1048576; // Convertir a Mbyte
    echo '<p>La carpeta <b>temas</b> tiene: <h2>' . round($tamano_temas, 2) . ' Mb</h2></p>';

    global $wpdb;
    $query = "SELECT SUM(data_length + index_length) / 1024 / 1024 AS size FROM information_schema.TABLES WHERE table_schema = '" . DB_NAME . "'";
    $resultados = $wpdb->get_results($query, ARRAY_A);
    foreach ($resultados as $resultado) {
        echo '<p>La <b>base de datos</b> pesa: <h2>' . round($resultado['size'], 2) . ' Mb</h2></p>';
    }
}

add_action('wp_dashboard_setup', 'agregar_widget_estadisticas_tamano_sitio');

function agregar_widget_estadisticas_tamano_sitio() {
    wp_add_dashboard_widget('widget_estadisticas_tamano_sitio', 'Tamaño de las carpetas del sitio', 'mostrar_estadisticas_tamano_sitio_escritorio');
}
?>
