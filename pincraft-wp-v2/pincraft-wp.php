<?php
/**
 * Plugin Name: PincraftWP Professional
 * Plugin URI: https://pincraftwp.com
 * Description: Generación automática de pines optimizados para Pinterest - Versión Profesional Completa
 * Version: 2.0.0
 * Author: PincraftWP Team
 * License: GPL v2 or later
 * Text Domain: pincraft-wp
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit('Direct access forbidden.');
}

// Definir constantes
define('PINCRAFT_VERSION', '2.4.0');
define('PINCRAFT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PINCRAFT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('PINCRAFT_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('PINCRAFT_API_ENDPOINT', 'https://pincraftwp-production.up.railway.app');

/**
 * Clase principal del plugin PincraftWP
 */
class PincraftWP_Professional {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        add_action('plugins_loaded', array($this, 'init'));
    }
    
    public function init() {
        // Verificar requisitos
        if (!$this->check_requirements()) {
            return;
        }
        
        // Cargar componentes
        $this->load_textdomain();
        $this->init_hooks();
        
        // Inicializar admin
        if (is_admin()) {
            $this->init_admin();
        }
    }
    
    private function check_requirements() {
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p><strong>PincraftWP:</strong> Requiere PHP 7.4 o superior. Versión actual: ' . PHP_VERSION . '</p></div>';
            });
            return false;
        }
        
        if (version_compare(get_bloginfo('version'), '5.0', '<')) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p><strong>PincraftWP:</strong> Requiere WordPress 5.0 o superior.</p></div>';
            });
            return false;
        }
        
        return true;
    }
    
    private function load_textdomain() {
        load_plugin_textdomain('pincraft-wp', false, dirname(PINCRAFT_PLUGIN_BASENAME) . '/languages');
    }
    
    private function init_hooks() {
        // AJAX hooks
        add_action('wp_ajax_pincraft_search_posts', array($this, 'ajax_search_posts'));
        add_action('wp_ajax_pincraft_validate_api_key', array($this, 'ajax_validate_api_key'));
        add_action('wp_ajax_pincraft_generate_pins', array($this, 'ajax_generate_pins'));
        add_action('wp_ajax_pincraft_get_usage', array($this, 'ajax_get_usage'));
    }
    
    private function init_admin() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    public function activate() {
        // Crear tabla de historial
        global $wpdb;
        $table_name = $wpdb->prefix . 'pincraft_generations';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            pin_count tinyint(2) NOT NULL DEFAULT 1,
            sector varchar(50) DEFAULT NULL,
            generation_date datetime DEFAULT CURRENT_TIMESTAMP,
            status varchar(20) DEFAULT 'completed',
            pin_urls longtext,
            metadata longtext,
            PRIMARY KEY (id),
            KEY post_id (post_id),
            KEY generation_date (generation_date)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Configuraciones por defecto
        add_option('pincraft_api_key', '');
        add_option('pincraft_api_endpoint', PINCRAFT_API_ENDPOINT);
        add_option('pincraft_default_pin_count', 4);
        
        // Crear directorio de caché
        $upload_dir = wp_upload_dir();
        $cache_dir = $upload_dir['basedir'] . '/pincraft-cache';
        if (!file_exists($cache_dir)) {
            wp_mkdir_p($cache_dir);
        }
    }
    
    public function deactivate() {
        // Limpiar transients
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_pincraft_%'");
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_pincraft_%'");
    }
    
    public function add_admin_menu() {
        // Página principal
        add_menu_page(
            'PincraftWP',
            'PincraftWP',
            'edit_posts',
            'pincraft-generator',
            array($this, 'display_generator_page'),
            'dashicons-pinterest',
            30
        );
        
        // Submenús
        add_submenu_page(
            'pincraft-generator',
            'Generar Pines',
            'Generar Pines',
            'edit_posts',
            'pincraft-generator',
            array($this, 'display_generator_page')
        );
        
        add_submenu_page(
            'pincraft-generator',
            'Historial',
            'Historial',
            'edit_posts',
            'pincraft-history',
            array($this, 'display_history_page')
        );
        
        add_submenu_page(
            'pincraft-generator',
            'Configuración',
            'Configuración',
            'manage_options',
            'pincraft-settings',
            array($this, 'display_settings_page')
        );
    }
    
    public function register_settings() {
        register_setting('pincraft_settings', 'pincraft_api_key', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        register_setting('pincraft_settings', 'pincraft_default_pin_count', array(
            'sanitize_callback' => 'intval'
        ));
    }
    
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'pincraft') === false) {
            return;
        }
        
        wp_enqueue_script('jquery');
        wp_enqueue_script(
            'pincraft-admin',
            PINCRAFT_PLUGIN_URL . 'assets/admin.js',
            array('jquery'),
            PINCRAFT_VERSION,
            true
        );
        
        wp_localize_script('pincraft-admin', 'pincraftAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pincraft_nonce'),
            'strings' => array(
                'generating' => __('Generando pines...', 'pincraft-wp'),
                'success' => __('¡Pines generados exitosamente!', 'pincraft-wp'),
                'error' => __('Error al generar pines', 'pincraft-wp'),
            )
        ));
        
        wp_enqueue_style(
            'pincraft-admin',
            PINCRAFT_PLUGIN_URL . 'assets/admin.css',
            array(),
            PINCRAFT_VERSION
        );
    }
    
    public function display_generator_page() {
        $api_key = get_option('pincraft_api_key', '');
        if (empty($api_key)) {
            echo '<div class="wrap"><h1>PincraftWP</h1>';
            echo '<div class="notice notice-warning"><p><strong>API Key requerida:</strong> <a href="' . admin_url('admin.php?page=pincraft-settings') . '">Configurar ahora</a></p></div>';
            echo '</div>';
            return;
        }
        
        include __DIR__ . '/templates/generator.php';
    }
    
    public function display_history_page() {
        include __DIR__ . '/templates/history.php';
    }
    
    public function display_settings_page() {
        if (isset($_POST['submit']) && check_admin_referer('pincraft_settings', 'pincraft_nonce')) {
            update_option('pincraft_api_key', sanitize_text_field($_POST['pincraft_api_key']));
            update_option('pincraft_default_pin_count', intval($_POST['pincraft_default_pin_count']));
            echo '<div class="notice notice-success"><p>Configuración guardada correctamente.</p></div>';
        }
        
        include __DIR__ . '/templates/settings.php';
    }
    
    // AJAX Handlers
    public function ajax_search_posts() {
        check_ajax_referer('pincraft_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied');
        }
        
        $query = sanitize_text_field($_POST['query']);
        
        if (strlen($query) < 2) {
            wp_send_json_error('Query too short');
        }
        
        $posts = get_posts(array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'numberposts' => 10,
            's' => $query,
            'orderby' => 'date',
            'order' => 'DESC'
        ));
        
        $results = array();
        foreach ($posts as $post) {
            $results[] = array(
                'ID' => $post->ID,
                'post_title' => $post->post_title,
                'post_date' => get_the_date('Y-m-d H:i', $post->ID),
                'post_excerpt' => wp_trim_words($post->post_content, 20)
            );
        }
        
        wp_send_json_success(array('posts' => $results));
    }
    
    public function ajax_validate_api_key() {
        check_ajax_referer('pincraft_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }
        
        $api_key = sanitize_text_field($_POST['api_key']);
        
        if (empty($api_key)) {
            wp_send_json_error('API Key requerida');
        }
        
        $response = wp_remote_get(PINCRAFT_API_ENDPOINT . '/api/v1/account/profile', array(
            'headers' => array(
                'x-api-key' => $api_key
            ),
            'timeout' => 10
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error('Error de conexión: ' . $response->get_error_message());
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        
        if ($status_code === 200) {
            wp_send_json_success(array(
                'message' => 'API Key válida',
                'plan' => 'pro'
            ));
        } else {
            wp_send_json_error('API Key inválida (código: ' . $status_code . ')');
        }
    }
    
    public function ajax_generate_pins() {
        check_ajax_referer('pincraft_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied');
        }
        
        $post_id = intval($_POST['post_id']);
        $pin_count = intval($_POST['pin_count']);
        $sector = sanitize_text_field($_POST['sector']);
        $color_palette = sanitize_text_field($_POST['color_palette'] ?? 'auto');
        $with_text = isset($_POST['with_text']) && $_POST['with_text'] == '1';
        $show_domain = isset($_POST['show_domain']) && $_POST['show_domain'] == '1';
        
        if (!$post_id || $pin_count < 1 || $pin_count > 10) {
            wp_send_json_error('Parámetros inválidos');
        }
        
        $post = get_post($post_id);
        if (!$post) {
            wp_send_json_error('Post no encontrado');
        }
        
        $api_key = get_option('pincraft_api_key', '');
        if (empty($api_key)) {
            wp_send_json_error('API Key no configurada');
        }
        
        $request_data = array(
            'title' => $post->post_title,
            'content' => wp_strip_all_tags($post->post_content),
            'domain' => parse_url(home_url(), PHP_URL_HOST),
            'count' => $pin_count,
            'sector' => $sector,
            'color_palette' => $color_palette,
            'with_text' => $with_text,
            'show_domain' => $show_domain
        );
        
        $response = wp_remote_post(PINCRAFT_API_ENDPOINT . '/api/v1/pins/generate', array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'x-api-key' => $api_key
            ),
            'body' => json_encode($request_data),
            'timeout' => 180
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error('Error de conexión: ' . $response->get_error_message());
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if ($status_code === 200 && isset($data['success']) && $data['success']) {
            $generated_pins = $data['data']['pins'] ?? array();
            $processed_pins = array();
            
            // Descargar y guardar cada imagen en la Media Library
            foreach ($generated_pins as $pin) {
                $image_url = $pin['image_url'];
                $processed_pin = $pin;
                
                error_log('PincraftWP: Attempting to download image from: ' . $image_url);
                
                // Descargar imagen
                $image_data = wp_remote_get($image_url, array('timeout' => 30));
                
                if (!is_wp_error($image_data) && wp_remote_retrieve_response_code($image_data) === 200) {
                    $image_content = wp_remote_retrieve_body($image_data);
                    
                    // Generar nombre único para el archivo
                    $filename = 'pincraft-' . $post_id . '-' . time() . '-' . uniqid() . '.jpg';
                    
                    // Subir a Media Library
                    $upload = wp_upload_bits($filename, null, $image_content);
                    
                    if (!$upload['error']) {
                        // Crear attachment en WordPress
                        $attachment = array(
                            'post_mime_type' => 'image/jpeg',
                            'post_title' => sanitize_file_name($post->post_title . ' - Pin'),
                            'post_content' => 'Pin generado automáticamente por PincraftWP',
                            'post_status' => 'inherit'
                        );
                        
                        $attachment_id = wp_insert_attachment($attachment, $upload['file']);
                        
                        if ($attachment_id) {
                            // Generar metadatos de imagen
                            require_once(ABSPATH . 'wp-admin/includes/image.php');
                            $attachment_data = wp_generate_attachment_metadata($attachment_id, $upload['file']);
                            wp_update_attachment_metadata($attachment_id, $attachment_data);
                            
                            // Actualizar información del pin
                            $processed_pin['local_url'] = $upload['url'];
                            $processed_pin['attachment_id'] = $attachment_id;
                        }
                    }
                }
                
                $processed_pins[] = $processed_pin;
            }
            
            // Guardar en historial
            global $wpdb;
            $table_name = $wpdb->prefix . 'pincraft_generations';
            
            $insert_result = $wpdb->insert(
                $table_name,
                array(
                    'post_id' => $post_id,
                    'pin_count' => count($processed_pins),
                    'sector' => $sector,
                    'generation_date' => current_time('mysql'),
                    'status' => 'completed',
                    'pin_urls' => json_encode($processed_pins),
                    'metadata' => json_encode(array(
                        'with_text' => $with_text,
                        'show_domain' => $show_domain
                    ))
                ),
                array('%d', '%d', '%s', '%s', '%s', '%s', '%s')
            );
            
            // Log error if insert fails
            if ($insert_result === false) {
                error_log('PincraftWP: Failed to insert into history table. Error: ' . $wpdb->last_error);
            }
            
            wp_send_json_success(array(
                'message' => 'Pines generados y guardados exitosamente',
                'pins' => $processed_pins,
                'credits_used' => $data['data']['credits_used'] ?? count($processed_pins)
            ));
        } else {
            wp_send_json_error($data['error'] ?? 'Error desconocido');
        }
    }
    
    public function ajax_get_usage() {
        check_ajax_referer('pincraft_nonce', 'nonce');
        
        $api_key = get_option('pincraft_api_key', '');
        if (empty($api_key)) {
            wp_send_json_error('API Key no configurada');
        }
        
        $response = wp_remote_get(PINCRAFT_API_ENDPOINT . '/api/v1/account/usage', array(
            'headers' => array(
                'x-api-key' => $api_key
            ),
            'timeout' => 10
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error('Error de conexión');
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        wp_send_json_success($data);
    }
}

// Inicializar el plugin
PincraftWP_Professional::get_instance();