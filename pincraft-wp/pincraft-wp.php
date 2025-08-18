<?php
/**
 * Plugin Name: PincraftWP
 * Plugin URI: https://pincraftwp.com
 * Description: Generación automática de pines optimizados para Pinterest desde tu contenido de WordPress
 * Version: 1.0.0
 * Author: PincraftWP Team
 * Author URI: https://pincraftwp.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: pincraft-wp
 * Domain Path: /languages
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Definir constantes del plugin
define('PINCRAFT_VERSION', '1.0.0');
define('PINCRAFT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PINCRAFT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('PINCRAFT_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Cargar archivos principales
require_once PINCRAFT_PLUGIN_DIR . 'includes/class-pincraft-core.php';
require_once PINCRAFT_PLUGIN_DIR . 'includes/class-pincraft-admin.php';
require_once PINCRAFT_PLUGIN_DIR . 'includes/class-pincraft-api.php';
require_once PINCRAFT_PLUGIN_DIR . 'includes/class-pincraft-generator.php';
require_once PINCRAFT_PLUGIN_DIR . 'includes/class-pincraft-settings.php';

// Función de activación
function pincraft_activate() {
    // Crear tabla para historial de generaciones
    global $wpdb;
    $table_name = $wpdb->prefix . 'pincraft_generations';
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        post_id bigint(20) NOT NULL,
        pin_count tinyint(2) NOT NULL DEFAULT 1,
        generation_date datetime DEFAULT CURRENT_TIMESTAMP,
        status varchar(20) DEFAULT 'pending',
        pin_urls text,
        metadata text,
        PRIMARY KEY (id),
        KEY post_id (post_id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    // Guardar versión del plugin
    update_option('pincraft_version', PINCRAFT_VERSION);
    
    // Configuraciones predeterminadas
    $default_settings = array(
        'pincraft_api_key' => '',
        'api_endpoint' => 'https://pincraftwp-production.up.railway.app/api/v1',
        'default_pin_count' => 4,
        'max_pin_count' => 10,
        'enable_watermark' => true,
        'watermark_position' => 'bottom-right',
        'enable_auto_hashtags' => true,
        'max_hashtags' => 10,
        'pin_style' => 'modern',
        'color_palette' => 'vibrant',
        'enable_cache' => true,
        'cache_duration' => 7, // días
        'daily_generation_limit' => 50,
        'enable_analytics' => false
    );
    
    foreach ($default_settings as $key => $value) {
        if (get_option('pincraft_' . $key) === false) {
            add_option('pincraft_' . $key, $value);
        }
    }
    
    // Crear carpeta de caché
    $upload_dir = wp_upload_dir();
    $cache_dir = $upload_dir['basedir'] . '/pincraft-cache';
    if (!file_exists($cache_dir)) {
        wp_mkdir_p($cache_dir);
    }
    
    // Programar limpieza de caché
    if (!wp_next_scheduled('pincraft_clean_cache')) {
        wp_schedule_event(time(), 'daily', 'pincraft_clean_cache');
    }
}
register_activation_hook(__FILE__, 'pincraft_activate');

// Función de desactivación
function pincraft_deactivate() {
    // Limpiar tareas programadas
    wp_clear_scheduled_hook('pincraft_clean_cache');
    
    // Limpiar transients
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_pincraft_%'");
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_pincraft_%'");
}
register_deactivation_hook(__FILE__, 'pincraft_deactivate');

// Función de desinstalación
function pincraft_uninstall() {
    // Solo ejecutar si es desinstalación real
    if (!defined('WP_UNINSTALL_PLUGIN')) {
        return;
    }
    
    // Eliminar tabla de la base de datos
    global $wpdb;
    $table_name = $wpdb->prefix . 'pincraft_generations';
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
    
    // Eliminar todas las opciones del plugin
    $options_to_delete = array(
        'pincraft_version',
        'pincraft_api_key',
        'pincraft_api_endpoint',
        'pincraft_default_pin_count',
        'pincraft_max_pin_count',
        'pincraft_enable_watermark',
        'pincraft_watermark_position',
        'pincraft_enable_auto_hashtags',
        'pincraft_max_hashtags',
        'pincraft_pin_style',
        'pincraft_color_palette',
        'pincraft_enable_cache',
        'pincraft_cache_duration',
        'pincraft_daily_generation_limit',
        'pincraft_enable_analytics'
    );
    
    foreach ($options_to_delete as $option) {
        delete_option($option);
    }
    
    // Eliminar archivos de caché
    $upload_dir = wp_upload_dir();
    $cache_dir = $upload_dir['basedir'] . '/pincraft-cache';
    if (file_exists($cache_dir)) {
        pincraft_delete_directory($cache_dir);
    }
}
// Registrar función de desinstalación (se llama desde uninstall.php)

// Función auxiliar para eliminar directorio
function pincraft_delete_directory($dir) {
    if (!file_exists($dir)) {
        return true;
    }
    
    if (!is_dir($dir)) {
        return unlink($dir);
    }
    
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }
        
        if (!pincraft_delete_directory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }
    }
    
    return rmdir($dir);
}

// Inicializar el plugin
function pincraft_init() {
    // Cargar textdomain para traducciones
    load_plugin_textdomain('pincraft-wp', false, dirname(PINCRAFT_PLUGIN_BASENAME) . '/languages');
    
    // Inicializar clase principal
    $pincraft = new Pincraft_Core();
    $pincraft->init();
    
    // Inicializar admin si estamos en el dashboard
    if (is_admin()) {
        $pincraft_admin = new Pincraft_Admin();
        $pincraft_admin->init();
    }
}
add_action('plugins_loaded', 'pincraft_init');

// Hook para limpieza de caché
add_action('pincraft_clean_cache', 'pincraft_perform_cache_cleanup');

function pincraft_perform_cache_cleanup() {
    $upload_dir = wp_upload_dir();
    $cache_dir = $upload_dir['basedir'] . '/pincraft-cache';
    $cache_duration = get_option('pincraft_cache_duration', 7);
    $expiration_time = time() - ($cache_duration * DAY_IN_SECONDS);
    
    if (is_dir($cache_dir)) {
        $files = glob($cache_dir . '/*');
        foreach ($files as $file) {
            if (is_file($file) && filemtime($file) < $expiration_time) {
                unlink($file);
            }
        }
    }
}

// Agregar enlaces en la página de plugins
function pincraft_plugin_action_links($links) {
    $settings_link = '<a href="' . admin_url('admin.php?page=pincraft-settings') . '">' . __('Configuración', 'pincraft-wp') . '</a>';
    $generate_link = '<a href="' . admin_url('admin.php?page=pincraft-generator') . '" style="color: #00a32a; font-weight: bold;">' . __('Generar Pines', 'pincraft-wp') . '</a>';
    
    array_unshift($links, $generate_link);
    array_unshift($links, $settings_link);
    
    return $links;
}
add_filter('plugin_action_links_' . PINCRAFT_PLUGIN_BASENAME, 'pincraft_plugin_action_links');

// Agregar meta enlaces
function pincraft_plugin_row_meta($links, $file) {
    if (PINCRAFT_PLUGIN_BASENAME === $file) {
        $row_meta = array(
            'docs' => '<a href="https://docs.pincraftwp.com" target="_blank">' . __('Documentación', 'pincraft-wp') . '</a>',
            'support' => '<a href="https://support.pincraftwp.com" target="_blank">' . __('Soporte', 'pincraft-wp') . '</a>',
            'pro' => '<a href="https://pincraftwp.com/pro" target="_blank" style="color: #ff6900; font-weight: bold;">' . __('Obtener Pro', 'pincraft-wp') . '</a>'
        );
        
        return array_merge($links, $row_meta);
    }
    
    return $links;
}
add_filter('plugin_row_meta', 'pincraft_plugin_row_meta', 10, 2);

// AJAX handlers para el generador
add_action('wp_ajax_pincraft_search_posts', 'pincraft_ajax_search_posts');
function pincraft_ajax_search_posts() {
    // Verificar nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pincraft_ajax_nonce')) {
        wp_die('Security check failed');
    }
    
    // Verificar permisos
    if (!current_user_can('edit_posts')) {
        wp_die('Permission denied');
    }
    
    $search = sanitize_text_field($_POST['search']);
    
    $args = array(
        'post_type' => 'post',
        'post_status' => 'publish',
        's' => $search,
        'posts_per_page' => 20,
        'orderby' => 'date',
        'order' => 'DESC'
    );
    
    $posts = get_posts($args);
    $results = array();
    
    foreach ($posts as $post) {
        $results[] = array(
            'id' => $post->ID,
            'title' => $post->post_title,
            'date' => get_the_date('Y-m-d', $post),
            'excerpt' => wp_trim_words($post->post_excerpt ?: $post->post_content, 20),
            'featured_image' => get_the_post_thumbnail_url($post->ID, 'thumbnail')
        );
    }
    
    wp_send_json_success($results);
}

add_action('wp_ajax_pincraft_generate_pins', 'pincraft_ajax_generate_pins');
function pincraft_ajax_generate_pins() {
    // Verificar nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pincraft_ajax_nonce')) {
        wp_send_json_error('Security check failed');
    }
    
    // Verificar permisos
    if (!current_user_can('edit_posts')) {
        wp_send_json_error('Permission denied');
    }
    
    $post_id = intval($_POST['post_id']);
    $pin_count = intval($_POST['pin_count']);
    $style = sanitize_text_field($_POST['style']);
    
    // Validar entrada
    if (!$post_id || $pin_count < 1 || $pin_count > 10) {
        wp_send_json_error('Invalid parameters');
    }
    
    // Inicializar generador
    $generator = new Pincraft_Generator();
    $result = $generator->generate_pins($post_id, $pin_count, $style);
    
    if ($result['success']) {
        wp_send_json_success($result['data']);
    } else {
        wp_send_json_error($result['message']);
    }
}

// Registrar endpoint REST API para integraciones externas
add_action('rest_api_init', function() {
    register_rest_route('pincraft/v1', '/generate', array(
        'methods' => 'POST',
        'callback' => 'pincraft_rest_generate_pins',
        'permission_callback' => function() {
            return current_user_can('edit_posts');
        },
        'args' => array(
            'post_id' => array(
                'required' => true,
                'validate_callback' => function($param) {
                    return is_numeric($param);
                }
            ),
            'pin_count' => array(
                'default' => 4,
                'validate_callback' => function($param) {
                    return is_numeric($param) && $param >= 1 && $param <= 10;
                }
            )
        )
    ));
});

function pincraft_rest_generate_pins($request) {
    $post_id = $request->get_param('post_id');
    $pin_count = $request->get_param('pin_count');
    
    $generator = new Pincraft_Generator();
    $result = $generator->generate_pins($post_id, $pin_count);
    
    if ($result['success']) {
        return new WP_REST_Response($result['data'], 200);
    } else {
        return new WP_Error('generation_failed', $result['message'], array('status' => 500));
    }
}