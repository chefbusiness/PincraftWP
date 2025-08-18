<?php
/**
 * Clase Admin de PincraftWP
 * 
 * Maneja la interfaz de administración del plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class Pincraft_Admin {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Inicializar hooks
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_post_pincraft_save_settings', array($this, 'save_settings'));
    }
    
    /**
     * Inicializar la clase admin
     */
    public function init() {
        // Ya se ejecuta en el constructor
    }
    
    /**
     * Agregar menús de administración
     */
    public function add_admin_menu() {
        // Página principal
        add_menu_page(
            __('PincraftWP', 'pincraft-wp'),
            __('PincraftWP', 'pincraft-wp'),
            'generate_pins',
            'pincraft-generator',
            array($this, 'display_generator_page'),
            'dashicons-pinterest',
            30
        );
        
        // Submenú: Generar Pines (misma página que el menú principal)
        add_submenu_page(
            'pincraft-generator',
            __('Generar Pines', 'pincraft-wp'),
            __('Generar Pines', 'pincraft-wp'),
            'generate_pins',
            'pincraft-generator',
            array($this, 'display_generator_page')
        );
        
        // Submenú: Historial
        add_submenu_page(
            'pincraft-generator',
            __('Historial', 'pincraft-wp'),
            __('Historial', 'pincraft-wp'),
            'generate_pins',
            'pincraft-history',
            array($this, 'display_history_page')
        );
        
        // Submenú: Configuración
        add_submenu_page(
            'pincraft-generator',
            __('Configuración', 'pincraft-wp'),
            __('Configuración', 'pincraft-wp'),
            'manage_pincraft',
            'pincraft-settings',
            array($this, 'display_settings_page')
        );
        
        // Submenú: Ayuda
        add_submenu_page(
            'pincraft-generator',
            __('Ayuda', 'pincraft-wp'),
            __('Ayuda', 'pincraft-wp'),
            'generate_pins',
            'pincraft-help',
            array($this, 'display_help_page')
        );
    }
    
    /**
     * Registrar configuraciones
     */
    public function register_settings() {
        register_setting('pincraft_settings', 'pincraft_api_key');
        register_setting('pincraft_settings', 'pincraft_api_endpoint');
        register_setting('pincraft_settings', 'pincraft_default_pin_count');
        register_setting('pincraft_settings', 'pincraft_enable_watermark');
        register_setting('pincraft_settings', 'pincraft_enable_analytics');
    }
    
    /**
     * Mostrar página del generador
     */
    public function display_generator_page() {
        include PINCRAFT_PLUGIN_DIR . 'admin/views/dashboard.php';
    }
    
    /**
     * Mostrar página de historial
     */
    public function display_history_page() {
        $generator = new Pincraft_Generator();
        $history = $generator->get_generation_history(50);
        
        include PINCRAFT_PLUGIN_DIR . 'admin/views/history.php';
    }
    
    /**
     * Mostrar página de configuración
     */
    public function display_settings_page() {
        // Procesar formulario si se envió
        if (isset($_POST['submit']) && check_admin_referer('pincraft_save_settings', 'pincraft_nonce')) {
            $this->save_settings();
        }
        
        include PINCRAFT_PLUGIN_DIR . 'admin/views/settings.php';
    }
    
    /**
     * Mostrar página de ayuda
     */
    public function display_help_page() {
        include PINCRAFT_PLUGIN_DIR . 'admin/views/help.php';
    }
    
    /**
     * Guardar configuraciones
     */
    public function save_settings() {
        if (!current_user_can('manage_pincraft')) {
            wp_die(__('No tienes permisos para realizar esta acción.', 'pincraft-wp'));
        }
        
        if (!check_admin_referer('pincraft_save_settings', 'pincraft_nonce')) {
            wp_die(__('Verificación de seguridad fallida.', 'pincraft-wp'));
        }
        
        // Sanitizar y guardar API Key
        if (isset($_POST['pincraft_api_key'])) {
            $api_key = sanitize_text_field($_POST['pincraft_api_key']);
            update_option('pincraft_api_key', $api_key);
            
            // Limpiar caché de validación
            delete_transient('pincraft_api_key_valid');
        }
        
        // Guardar otras configuraciones
        if (isset($_POST['pincraft_default_pin_count'])) {
            $pin_count = intval($_POST['pincraft_default_pin_count']);
            if ($pin_count >= 1 && $pin_count <= 10) {
                update_option('pincraft_default_pin_count', $pin_count);
            }
        }
        
        if (isset($_POST['pincraft_enable_watermark'])) {
            update_option('pincraft_enable_watermark', true);
        } else {
            update_option('pincraft_enable_watermark', false);
        }
        
        if (isset($_POST['pincraft_enable_analytics'])) {
            update_option('pincraft_enable_analytics', true);
        } else {
            update_option('pincraft_enable_analytics', false);
        }
        
        // Mostrar mensaje de éxito
        add_action('admin_notices', function() {
            ?>
            <div class="notice notice-success is-dismissible">
                <p><?php _e('Configuración guardada correctamente.', 'pincraft-wp'); ?></p>
            </div>
            <?php
        });
    }
    
    /**
     * Mostrar estadísticas del dashboard
     */
    public function get_dashboard_stats() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'pincraft_generations';
        
        // Total de pines generados
        $total_pins = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE status = 'completed'");
        
        // Pines este mes
        $this_month = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE status = 'completed' AND generation_date >= %s",
            date('Y-m-01')
        ));
        
        // Posts con pines
        $posts_with_pins = $wpdb->get_var("SELECT COUNT(DISTINCT post_id) FROM $table_name WHERE status = 'completed'");
        
        // Última generación
        $last_generation = $wpdb->get_row("SELECT * FROM $table_name WHERE status = 'completed' ORDER BY generation_date DESC LIMIT 1");
        
        return array(
            'total_pins' => intval($total_pins),
            'this_month' => intval($this_month),
            'posts_with_pins' => intval($posts_with_pins),
            'last_generation' => $last_generation
        );
    }
    
    /**
     * Obtener posts recientes para el selector
     */
    public function get_recent_posts($limit = 50) {
        return get_posts(array(
            'numberposts' => $limit,
            'post_status' => 'publish',
            'post_type' => 'post',
            'orderby' => 'date',
            'order' => 'DESC'
        ));
    }
    
    /**
     * Verificar estado de la API
     */
    public function check_api_status() {
        $api_key = get_option('pincraft_api_key', '');
        
        if (empty($api_key)) {
            return array(
                'status' => 'no_key',
                'message' => __('No hay API Key configurada', 'pincraft-wp')
            );
        }
        
        // Verificar conectividad con la API
        $response = wp_remote_get('https://pincraftwp-production.up.railway.app/api/v1/health', array(
            'timeout' => 5
        ));
        
        if (is_wp_error($response)) {
            return array(
                'status' => 'error',
                'message' => __('Error de conectividad con la API', 'pincraft-wp')
            );
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        
        if ($status_code !== 200) {
            return array(
                'status' => 'down',
                'message' => __('API no disponible temporalmente', 'pincraft-wp')
            );
        }
        
        return array(
            'status' => 'ok',
            'message' => __('API funcionando correctamente', 'pincraft-wp')
        );
    }
}