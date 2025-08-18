<?php
/**
 * Clase Core de PincraftWP
 * 
 * Maneja la lógica principal del plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class Pincraft_Core {
    
    /**
     * Instancia única de la clase
     */
    private static $instance = null;
    
    /**
     * API Key del usuario
     */
    private $api_key;
    
    /**
     * URL base de la API de PincraftWP
     */
    private $api_base_url = 'https://pincraftwp-production.up.railway.app/api/v1';
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->api_key = get_option('pincraft_api_key', '');
    }
    
    /**
     * Obtener instancia única
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Inicializar el plugin
     */
    public function init() {
        // Cargar textdomain
        add_action('init', array($this, 'load_textdomain'));
        
        // Registrar hooks
        $this->register_hooks();
        
        // Verificar si hay API key configurada
        if (is_admin() && !$this->has_valid_api_key()) {
            add_action('admin_notices', array($this, 'show_api_key_notice'));
        }
    }
    
    /**
     * Registrar hooks y filtros
     */
    private function register_hooks() {
        // Agregar capacidades personalizadas
        add_action('admin_init', array($this, 'add_capabilities'));
        
        // Registrar scripts y estilos
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // AJAX handlers
        add_action('wp_ajax_pincraft_validate_api_key', array($this, 'ajax_validate_api_key'));
        add_action('wp_ajax_pincraft_get_account_usage', array($this, 'ajax_get_account_usage'));
    }
    
    /**
     * Cargar archivos de traducción
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'pincraft-wp',
            false,
            dirname(plugin_basename(PINCRAFT_PLUGIN_DIR)) . '/languages/'
        );
    }
    
    /**
     * Agregar capacidades de usuario
     */
    public function add_capabilities() {
        $role = get_role('administrator');
        if ($role) {
            $role->add_cap('manage_pincraft');
            $role->add_cap('generate_pins');
        }
        
        $role = get_role('editor');
        if ($role) {
            $role->add_cap('generate_pins');
        }
    }
    
    /**
     * Cargar assets de administración
     */
    public function enqueue_admin_assets($hook) {
        // Solo cargar en páginas del plugin
        if (strpos($hook, 'pincraft') === false) {
            return;
        }
        
        // CSS
        wp_enqueue_style(
            'pincraft-admin',
            PINCRAFT_PLUGIN_URL . 'admin/css/pincraft-admin.css',
            array(),
            PINCRAFT_VERSION
        );
        
        // JavaScript
        wp_enqueue_script(
            'pincraft-admin',
            PINCRAFT_PLUGIN_URL . 'admin/js/pincraft-admin.js',
            array('jquery', 'wp-api'),
            PINCRAFT_VERSION,
            true
        );
        
        // Localizar script con datos para JavaScript
        wp_localize_script('pincraft-admin', 'pincraftAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pincraft_ajax_nonce'),
            'apiKey' => $this->api_key,
            'strings' => array(
                'generating' => __('Generando pines...', 'pincraft-wp'),
                'success' => __('¡Pines generados exitosamente!', 'pincraft-wp'),
                'error' => __('Error al generar pines', 'pincraft-wp'),
                'selectPost' => __('Por favor selecciona un artículo', 'pincraft-wp'),
                'confirmGenerate' => __('¿Generar %d pines para este artículo?', 'pincraft-wp'),
                'noCredits' => __('No tienes suficientes créditos', 'pincraft-wp'),
                'invalidApiKey' => __('API Key inválida', 'pincraft-wp')
            )
        ));
    }
    
    /**
     * Verificar si hay API key válida
     */
    public function has_valid_api_key() {
        if (empty($this->api_key)) {
            return false;
        }
        
        // Verificar en caché
        $cached_validation = get_transient('pincraft_api_key_valid');
        if ($cached_validation !== false) {
            return $cached_validation === 'valid';
        }
        
        // Validar con el servidor
        $validation = $this->validate_api_key($this->api_key);
        
        // Guardar en caché por 1 hora
        set_transient('pincraft_api_key_valid', $validation ? 'valid' : 'invalid', HOUR_IN_SECONDS);
        
        return $validation;
    }
    
    /**
     * Validar API key con el servidor
     */
    private function validate_api_key($api_key) {
        $response = wp_remote_post($this->api_base_url . 'auth/validate', array(
            'body' => json_encode(array('api_key' => $api_key)),
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'timeout' => 10
        ));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        return isset($data['valid']) && $data['valid'] === true;
    }
    
    /**
     * Mostrar notificación de API key faltante
     */
    public function show_api_key_notice() {
        if (!current_user_can('manage_pincraft')) {
            return;
        }
        
        $settings_url = admin_url('admin.php?page=pincraft-settings');
        ?>
        <div class="notice notice-warning is-dismissible">
            <p>
                <strong><?php _e('PincraftWP:', 'pincraft-wp'); ?></strong>
                <?php _e('Por favor configura tu API Key para comenzar a generar pines.', 'pincraft-wp'); ?>
                <a href="<?php echo esc_url($settings_url); ?>" class="button button-primary" style="margin-left: 10px;">
                    <?php _e('Configurar ahora', 'pincraft-wp'); ?>
                </a>
            </p>
        </div>
        <?php
    }
    
    /**
     * AJAX: Validar API Key
     */
    public function ajax_validate_api_key() {
        check_ajax_referer('pincraft_ajax_nonce', 'nonce');
        
        if (!current_user_can('manage_pincraft')) {
            wp_send_json_error('Permission denied');
        }
        
        $api_key = sanitize_text_field($_POST['api_key']);
        
        if (empty($api_key)) {
            wp_send_json_error('API Key is required');
        }
        
        $response = wp_remote_post($this->api_base_url . 'auth/validate', array(
            'body' => json_encode(array('api_key' => $api_key)),
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'timeout' => 10
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error($response->get_error_message());
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['valid']) && $data['valid'] === true) {
            // Guardar API key
            update_option('pincraft_api_key', $api_key);
            
            // Limpiar caché de validación
            delete_transient('pincraft_api_key_valid');
            
            wp_send_json_success(array(
                'message' => __('API Key válida', 'pincraft-wp'),
                'plan' => $data['plan'] ?? 'free',
                'credits' => $data['remaining_credits'] ?? 0
            ));
        } else {
            wp_send_json_error(__('API Key inválida', 'pincraft-wp'));
        }
    }
    
    /**
     * AJAX: Obtener uso de la cuenta
     */
    public function ajax_get_account_usage() {
        check_ajax_referer('pincraft_ajax_nonce', 'nonce');
        
        if (!current_user_can('generate_pins')) {
            wp_send_json_error('Permission denied');
        }
        
        if (empty($this->api_key)) {
            wp_send_json_error('No API Key configured');
        }
        
        $response = wp_remote_get($this->api_base_url . 'account/usage', array(
            'headers' => array(
                'X-API-Key' => $this->api_key,
            ),
            'timeout' => 10
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error($response->get_error_message());
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        wp_send_json_success($data);
    }
    
    /**
     * Obtener información del plan actual
     */
    public function get_plan_info() {
        $cached_info = get_transient('pincraft_plan_info');
        if ($cached_info !== false) {
            return $cached_info;
        }
        
        if (empty($this->api_key)) {
            return array(
                'plan' => 'none',
                'credits_used' => 0,
                'credits_limit' => 0,
                'reset_date' => ''
            );
        }
        
        $response = wp_remote_get($this->api_base_url . 'account/usage', array(
            'headers' => array(
                'X-API-Key' => $this->api_key,
            ),
            'timeout' => 10
        ));
        
        if (is_wp_error($response)) {
            return array(
                'plan' => 'error',
                'credits_used' => 0,
                'credits_limit' => 0,
                'reset_date' => ''
            );
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        $plan_info = array(
            'plan' => $data['plan'] ?? 'free',
            'credits_used' => $data['total_used'] ?? 0,
            'credits_limit' => $data['limit'] ?? 10,
            'reset_date' => $data['reset_date'] ?? ''
        );
        
        // Cachear por 5 minutos
        set_transient('pincraft_plan_info', $plan_info, 5 * MINUTE_IN_SECONDS);
        
        return $plan_info;
    }
    
    /**
     * Obtener límites del plan
     */
    public function get_plan_limits($plan = null) {
        if ($plan === null) {
            $info = $this->get_plan_info();
            $plan = $info['plan'];
        }
        
        $limits = array(
            'free' => array(
                'monthly_pins' => 10,
                'max_pins_per_post' => 3,
                'watermark' => true,
                'priority_support' => false
            ),
            'pro' => array(
                'monthly_pins' => 200,
                'max_pins_per_post' => 10,
                'watermark' => false,
                'priority_support' => true
            ),
            'agency' => array(
                'monthly_pins' => 1000,
                'max_pins_per_post' => 10,
                'watermark' => false,
                'priority_support' => true,
                'white_label' => true
            )
        );
        
        return $limits[$plan] ?? $limits['free'];
    }
}