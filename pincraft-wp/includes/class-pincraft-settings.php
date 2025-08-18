<?php
/**
 * Clase para gestionar la configuración del plugin
 */

class Pincraft_Settings {
    
    private $api_endpoint;
    private $api_key;
    
    public function __construct() {
        $this->api_endpoint = get_option('pincraft_api_endpoint', 'https://pincraftwp-production.up.railway.app/api/v1');
        $this->api_key = get_option('pincraft_api_key', '');
    }
    
    /**
     * Inicializar configuraciones
     */
    public function init() {
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp_ajax_pincraft_validate_api_key', array($this, 'ajax_validate_api_key'));
    }
    
    /**
     * Agregar página de configuración al menú
     */
    public function add_settings_page() {
        add_submenu_page(
            'pincraft-wp',
            __('Configuración', 'pincraft-wp'),
            __('Configuración', 'pincraft-wp'),
            'manage_options',
            'pincraft-settings',
            array($this, 'render_settings_page')
        );
    }
    
    /**
     * Registrar configuraciones
     */
    public function register_settings() {
        register_setting('pincraft_settings', 'pincraft_api_key', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        
        register_setting('pincraft_settings', 'pincraft_api_endpoint', array(
            'default' => 'https://api.pincraftwp.com/api/v1',
            'sanitize_callback' => 'esc_url_raw'
        ));
        
        register_setting('pincraft_settings', 'pincraft_default_pin_count', array(
            'type' => 'integer',
            'default' => 4,
            'sanitize_callback' => 'absint'
        ));
        
        register_setting('pincraft_settings', 'pincraft_pin_style', array(
            'default' => 'modern',
            'sanitize_callback' => 'sanitize_text_field'
        ));
    }
    
    /**
     * Renderizar página de configuración
     */
    public function render_settings_page() {
        $api_key = get_option('pincraft_api_key', '');
        $api_endpoint = get_option('pincraft_api_endpoint', 'https://api.pincraftwp.com/api/v1');
        $default_pin_count = get_option('pincraft_default_pin_count', 4);
        $pin_style = get_option('pincraft_pin_style', 'modern');
        
        // Validar API key si existe
        $api_status = false;
        $api_info = null;
        if (!empty($api_key)) {
            $api_info = $this->validate_api_key($api_key);
            $api_status = $api_info && isset($api_info['valid']) && $api_info['valid'];
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <?php if (isset($_GET['settings-updated'])): ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php _e('Configuración guardada exitosamente.', 'pincraft-wp'); ?></p>
                </div>
            <?php endif; ?>
            
            <div class="pincraft-settings-container">
                <div class="pincraft-settings-main">
                    <form method="post" action="options.php">
                        <?php settings_fields('pincraft_settings'); ?>
                        
                        <div class="card">
                            <h2><?php _e('Configuración de API', 'pincraft-wp'); ?></h2>
                            
                            <?php if (empty($api_key)): ?>
                                <div class="notice notice-warning inline">
                                    <p>
                                        <?php _e('Necesitas una API Key para usar PincraftWP.', 'pincraft-wp'); ?>
                                        <a href="https://pincraftwp.com/register" target="_blank" class="button button-primary">
                                            <?php _e('Obtener API Key Gratis', 'pincraft-wp'); ?>
                                        </a>
                                    </p>
                                </div>
                            <?php endif; ?>
                            
                            <table class="form-table">
                                <tr>
                                    <th scope="row">
                                        <label for="pincraft_api_key"><?php _e('API Key', 'pincraft-wp'); ?></label>
                                    </th>
                                    <td>
                                        <input type="text" 
                                               id="pincraft_api_key" 
                                               name="pincraft_api_key" 
                                               value="<?php echo esc_attr($api_key); ?>" 
                                               class="regular-text code" 
                                               placeholder="pk_xxxxxxxxxxxxxxxx" />
                                        <button type="button" 
                                                id="validate-api-key" 
                                                class="button button-secondary">
                                            <?php _e('Validar', 'pincraft-wp'); ?>
                                        </button>
                                        
                                        <?php if ($api_status && $api_info): ?>
                                            <div class="api-status valid">
                                                <span class="dashicons dashicons-yes-alt"></span>
                                                <?php printf(
                                                    __('API Key válida - Plan: %s', 'pincraft-wp'),
                                                    '<strong>' . esc_html($api_info['plan']) . '</strong>'
                                                ); ?>
                                                <br>
                                                <small>
                                                    <?php printf(
                                                        __('Créditos: %d/%d este mes', 'pincraft-wp'),
                                                        $api_info['credits_used'],
                                                        $api_info['monthly_credits']
                                                    ); ?>
                                                </small>
                                            </div>
                                        <?php elseif (!empty($api_key) && !$api_status): ?>
                                            <div class="api-status invalid">
                                                <span class="dashicons dashicons-warning"></span>
                                                <?php _e('API Key inválida o expirada', 'pincraft-wp'); ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <p class="description">
                                            <?php _e('Ingresa tu API Key de PincraftWP. Puedes obtenerla en tu dashboard.', 'pincraft-wp'); ?>
                                        </p>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">
                                        <label for="pincraft_api_endpoint"><?php _e('Endpoint API', 'pincraft-wp'); ?></label>
                                    </th>
                                    <td>
                                        <input type="url" 
                                               id="pincraft_api_endpoint" 
                                               name="pincraft_api_endpoint" 
                                               value="<?php echo esc_url($api_endpoint); ?>" 
                                               class="regular-text code" />
                                        <p class="description">
                                            <?php _e('URL del servidor API. Normalmente no necesitas cambiar esto.', 'pincraft-wp'); ?>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="card">
                            <h2><?php _e('Configuración de Generación', 'pincraft-wp'); ?></h2>
                            
                            <table class="form-table">
                                <tr>
                                    <th scope="row">
                                        <label for="pincraft_default_pin_count">
                                            <?php _e('Cantidad de Pines por Defecto', 'pincraft-wp'); ?>
                                        </label>
                                    </th>
                                    <td>
                                        <input type="number" 
                                               id="pincraft_default_pin_count" 
                                               name="pincraft_default_pin_count" 
                                               value="<?php echo esc_attr($default_pin_count); ?>" 
                                               min="1" 
                                               max="10" 
                                               class="small-text" />
                                        <p class="description">
                                            <?php _e('Número predeterminado de pines a generar (1-10).', 'pincraft-wp'); ?>
                                        </p>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">
                                        <label for="pincraft_pin_style">
                                            <?php _e('Estilo Visual por Defecto', 'pincraft-wp'); ?>
                                        </label>
                                    </th>
                                    <td>
                                        <select id="pincraft_pin_style" name="pincraft_pin_style">
                                            <option value="modern" <?php selected($pin_style, 'modern'); ?>>
                                                <?php _e('Moderno', 'pincraft-wp'); ?>
                                            </option>
                                            <option value="vibrant" <?php selected($pin_style, 'vibrant'); ?>>
                                                <?php _e('Vibrante', 'pincraft-wp'); ?>
                                            </option>
                                            <option value="elegant" <?php selected($pin_style, 'elegant'); ?>>
                                                <?php _e('Elegante', 'pincraft-wp'); ?>
                                            </option>
                                            <option value="playful" <?php selected($pin_style, 'playful'); ?>>
                                                <?php _e('Divertido', 'pincraft-wp'); ?>
                                            </option>
                                            <option value="professional" <?php selected($pin_style, 'professional'); ?>>
                                                <?php _e('Profesional', 'pincraft-wp'); ?>
                                            </option>
                                        </select>
                                        <p class="description">
                                            <?php _e('Estilo visual predeterminado para los pines generados.', 'pincraft-wp'); ?>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <?php submit_button(__('Guardar Configuración', 'pincraft-wp')); ?>
                    </form>
                </div>
                
                <div class="pincraft-settings-sidebar">
                    <div class="card">
                        <h3><?php _e('Recursos', 'pincraft-wp'); ?></h3>
                        <ul>
                            <li>
                                <a href="https://docs.pincraftwp.com" target="_blank">
                                    <?php _e('Documentación', 'pincraft-wp'); ?>
                                </a>
                            </li>
                            <li>
                                <a href="https://pincraftwp.com/dashboard" target="_blank">
                                    <?php _e('Mi Dashboard', 'pincraft-wp'); ?>
                                </a>
                            </li>
                            <li>
                                <a href="https://pincraftwp.com/pricing" target="_blank">
                                    <?php _e('Planes y Precios', 'pincraft-wp'); ?>
                                </a>
                            </li>
                            <li>
                                <a href="https://support.pincraftwp.com" target="_blank">
                                    <?php _e('Soporte', 'pincraft-wp'); ?>
                                </a>
                            </li>
                        </ul>
                    </div>
                    
                    <?php if ($api_status && $api_info): ?>
                    <div class="card">
                        <h3><?php _e('Tu Plan', 'pincraft-wp'); ?></h3>
                        <div class="plan-info">
                            <div class="plan-name"><?php echo esc_html(ucfirst($api_info['plan'])); ?></div>
                            <div class="credits-info">
                                <div class="credits-used">
                                    <strong><?php echo esc_html($api_info['credits_used']); ?></strong>
                                    <span><?php _e('Usados', 'pincraft-wp'); ?></span>
                                </div>
                                <div class="credits-total">
                                    <strong><?php echo esc_html($api_info['monthly_credits']); ?></strong>
                                    <span><?php _e('Total', 'pincraft-wp'); ?></span>
                                </div>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" 
                                     style="width: <?php echo esc_attr(($api_info['credits_used'] / $api_info['monthly_credits']) * 100); ?>%">
                                </div>
                            </div>
                            <?php if ($api_info['plan'] === 'free'): ?>
                                <a href="https://pincraftwp.com/upgrade" 
                                   target="_blank" 
                                   class="button button-primary" 
                                   style="width: 100%; margin-top: 10px;">
                                    <?php _e('Actualizar Plan', 'pincraft-wp'); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <style>
            .pincraft-settings-container {
                display: flex;
                gap: 20px;
                margin-top: 20px;
            }
            .pincraft-settings-main {
                flex: 1;
            }
            .pincraft-settings-sidebar {
                width: 300px;
            }
            .api-status {
                margin-top: 10px;
                padding: 10px;
                border-radius: 4px;
            }
            .api-status.valid {
                background: #d4edda;
                color: #155724;
                border: 1px solid #c3e6cb;
            }
            .api-status.invalid {
                background: #f8d7da;
                color: #721c24;
                border: 1px solid #f5c6cb;
            }
            .api-status .dashicons {
                margin-right: 5px;
            }
            .plan-info {
                text-align: center;
                padding: 15px;
            }
            .plan-name {
                font-size: 20px;
                font-weight: bold;
                margin-bottom: 15px;
            }
            .credits-info {
                display: flex;
                justify-content: space-around;
                margin-bottom: 15px;
            }
            .credits-info > div {
                text-align: center;
            }
            .credits-info strong {
                display: block;
                font-size: 24px;
            }
            .credits-info span {
                font-size: 12px;
                color: #666;
            }
            .progress-bar {
                height: 10px;
                background: #e0e0e0;
                border-radius: 5px;
                overflow: hidden;
            }
            .progress-fill {
                height: 100%;
                background: #4caf50;
                transition: width 0.3s ease;
            }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            $('#validate-api-key').on('click', function() {
                var apiKey = $('#pincraft_api_key').val();
                var button = $(this);
                
                if (!apiKey) {
                    alert('<?php _e('Por favor ingresa una API Key', 'pincraft-wp'); ?>');
                    return;
                }
                
                button.prop('disabled', true).text('<?php _e('Validando...', 'pincraft-wp'); ?>');
                
                $.post(ajaxurl, {
                    action: 'pincraft_validate_api_key',
                    api_key: apiKey,
                    nonce: '<?php echo wp_create_nonce('pincraft_ajax_nonce'); ?>'
                }, function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.data || '<?php _e('Error al validar API Key', 'pincraft-wp'); ?>');
                    }
                }).always(function() {
                    button.prop('disabled', false).text('<?php _e('Validar', 'pincraft-wp'); ?>');
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * Validar API key contra el servidor
     */
    public function validate_api_key($api_key) {
        $response = wp_remote_post($this->api_endpoint . '/auth/validate', array(
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode(array(
                'api_key' => $api_key
            )),
            'timeout' => 10
        ));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        return json_decode($body, true);
    }
    
    /**
     * AJAX handler para validar API key
     */
    public function ajax_validate_api_key() {
        check_ajax_referer('pincraft_ajax_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Permission denied');
        }
        
        $api_key = sanitize_text_field($_POST['api_key']);
        $result = $this->validate_api_key($api_key);
        
        if ($result && isset($result['valid']) && $result['valid']) {
            update_option('pincraft_api_key', $api_key);
            wp_send_json_success(__('API Key válida', 'pincraft-wp'));
        } else {
            wp_send_json_error(__('API Key inválida', 'pincraft-wp'));
        }
    }
}