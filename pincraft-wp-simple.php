<?php
/**
 * Plugin Name: PincraftWP Simple
 * Plugin URI: https://pincraftwp.com
 * Description: Generación automática de pines optimizados para Pinterest - Versión Simplificada
 * Version: 1.0.1
 * Author: PincraftWP Team
 * License: GPL v2 or later
 * Text Domain: pincraft-wp
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Definir constantes
define('PINCRAFT_VERSION', '1.0.1');
define('PINCRAFT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PINCRAFT_PLUGIN_URL', plugin_dir_url(__FILE__));

class PincraftWP_Simple {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp_ajax_pincraft_test_connection', array($this, 'test_connection'));
        add_action('wp_ajax_pincraft_generate_simple', array($this, 'generate_simple'));
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'PincraftWP Simple',
            'PincraftWP',
            'manage_options',
            'pincraft-simple',
            array($this, 'admin_page'),
            'dashicons-pinterest',
            30
        );
    }
    
    public function register_settings() {
        register_setting('pincraft_settings', 'pincraft_api_key');
    }
    
    public function admin_page() {
        if (isset($_POST['submit'])) {
            update_option('pincraft_api_key', sanitize_text_field($_POST['pincraft_api_key']));
            echo '<div class="notice notice-success"><p>Configuración guardada!</p></div>';
        }
        
        $api_key = get_option('pincraft_api_key', '');
        ?>
        <div class="wrap">
            <h1>🎨 PincraftWP - Versión Simplificada</h1>
            
            <div class="card" style="max-width: 600px; padding: 20px; margin: 20px 0;">
                <h2>⚙️ Configuración API</h2>
                <form method="post" action="">
                    <table class="form-table">
                        <tr>
                            <th scope="row">API Key:</th>
                            <td>
                                <input type="text" name="pincraft_api_key" value="<?php echo esc_attr($api_key); ?>" class="regular-text" placeholder="pk_test_..." />
                                <p class="description">Tu API Key: <code>pk_test_b1898d7279d995b11fd3d5a6</code></p>
                            </td>
                        </tr>
                    </table>
                    <?php submit_button('Guardar API Key'); ?>
                </form>
                
                <h3>🧪 Probar Conexión</h3>
                <button id="test-connection" class="button">Probar API</button>
                <div id="test-result" style="margin-top: 10px;"></div>
                
                <h3>🎨 Generar Pin de Prueba</h3>
                <button id="generate-test" class="button button-primary">Generar Pin Simple</button>
                <div id="generate-result" style="margin-top: 10px;"></div>
                
                <h3>📋 Información</h3>
                <ul>
                    <li><strong>API Endpoint:</strong> <code>https://pincraftwp-production.up.railway.app/api/v1</code></li>
                    <li><strong>Estado de WordPress:</strong> ✅ Funcionando</li>
                    <li><strong>Plugin Activo:</strong> ✅ Cargado correctamente</li>
                </ul>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#test-connection').click(function() {
                $('#test-result').html('🔄 Probando...');
                $.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'pincraft_test_connection',
                        api_key: $('input[name="pincraft_api_key"]').val()
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#test-result').html('<span style="color: green;">✅ ' + response.data + '</span>');
                        } else {
                            $('#test-result').html('<span style="color: red;">❌ ' + response.data + '</span>');
                        }
                    },
                    error: function() {
                        $('#test-result').html('<span style="color: red;">❌ Error en la petición</span>');
                    }
                });
            });
            
            $('#generate-test').click(function() {
                $('#generate-result').html('🔄 Generando pin de prueba...');
                $.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'pincraft_generate_simple',
                        api_key: $('input[name="pincraft_api_key"]').val()
                    },
                    timeout: 60000,
                    success: function(response) {
                        if (response.success) {
                            $('#generate-result').html('<span style="color: green;">✅ ' + response.data + '</span>');
                        } else {
                            $('#generate-result').html('<span style="color: red;">❌ ' + response.data + '</span>');
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#generate-result').html('<span style="color: red;">❌ Error: ' + (status === 'timeout' ? 'Timeout' : error) + '</span>');
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    public function test_connection() {
        $api_key = sanitize_text_field($_POST['api_key']);
        
        if (empty($api_key)) {
            wp_send_json_error('API Key requerida');
        }
        
        // Probar conexión con la API
        $response = wp_remote_get('https://pincraftwp-production.up.railway.app/api/v1/health', array(
            'timeout' => 10
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error('Error de conexión: ' . $response->get_error_message());
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        
        if ($status_code === 200) {
            wp_send_json_success('Conexión exitosa con la API!');
        } else {
            wp_send_json_error('API respondió con código: ' . $status_code);
        }
    }
    
    public function generate_simple() {
        $api_key = sanitize_text_field($_POST['api_key']);
        
        if (empty($api_key)) {
            wp_send_json_error('API Key requerida');
        }
        
        // Datos de prueba para generar un pin
        $test_data = array(
            'title' => 'Prueba de PincraftWP',
            'content' => 'Este es un pin de prueba generado desde WordPress usando PincraftWP.',
            'domain' => parse_url(home_url(), PHP_URL_HOST),
            'count' => 1,
            'sector' => 'decoracion_hogar',
            'with_text' => true,
            'show_domain' => true
        );
        
        $response = wp_remote_post('https://pincraftwp-production.up.railway.app/api/v1/pins/generate', array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'x-api-key' => $api_key
            ),
            'body' => json_encode($test_data),
            'timeout' => 60
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error('Error de conexión: ' . $response->get_error_message());
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if ($status_code === 200) {
            if (isset($data['success']) && $data['success']) {
                $image_url = $data['data']['pins'][0]['image_url'] ?? 'No URL';
                wp_send_json_success('Pin generado exitosamente! URL: ' . $image_url);
            } else {
                wp_send_json_error('Error en generación: ' . ($data['error'] ?? 'Desconocido'));
            }
        } else {
            wp_send_json_error('Error HTTP ' . $status_code . ': ' . ($data['error'] ?? $body));
        }
    }
}

// Inicializar el plugin
new PincraftWP_Simple();