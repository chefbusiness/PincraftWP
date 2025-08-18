<?php
/**
 * Vista de configuración del plugin
 */

// No permitir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

$api_key = get_option('pincraft_api_key', '');
$api_endpoint = get_option('pincraft_api_endpoint', 'https://pincraftwp-production.up.railway.app/api/v1');
$default_pin_count = get_option('pincraft_default_pin_count', 4);
$enable_watermark = get_option('pincraft_enable_watermark', true);
$enable_analytics = get_option('pincraft_enable_analytics', false);
?>

<div class="wrap">
    <h1>⚙️ Configuración de PincraftWP</h1>
    
    <form method="post" action="">
        <?php wp_nonce_field('pincraft_save_settings', 'pincraft_nonce'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="pincraft_api_key">🔑 API Key de PincraftWP</label>
                </th>
                <td>
                    <input type="password" 
                           id="pincraft_api_key" 
                           name="pincraft_api_key" 
                           value="<?php echo esc_attr($api_key); ?>" 
                           class="regular-text" 
                           placeholder="pk_test_..." />
                    <button type="button" id="toggle-api-key" class="button">👁️ Mostrar</button>
                    <br>
                    <span class="description">
                        Obtén tu API Key en <a href="https://pincraftwp.com" target="_blank">PincraftWP.com</a>. 
                        <strong>Necesaria para generar pines.</strong>
                    </span>
                    <div id="api-key-status" style="margin-top: 10px;"></div>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="pincraft_api_endpoint">🌐 Endpoint de API</label>
                </th>
                <td>
                    <input type="url" 
                           id="pincraft_api_endpoint" 
                           name="pincraft_api_endpoint" 
                           value="<?php echo esc_attr($api_endpoint); ?>" 
                           class="regular-text" />
                    <br>
                    <span class="description">URL base de la API de PincraftWP (generalmente no necesita cambios)</span>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="pincraft_default_pin_count">📊 Cantidad de Pines por Defecto</label>
                </th>
                <td>
                    <select id="pincraft_default_pin_count" name="pincraft_default_pin_count">
                        <?php for ($i = 1; $i <= 10; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php selected($default_pin_count, $i); ?>>
                                <?php echo $i; ?> <?php echo $i === 1 ? 'pin' : 'pines'; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                    <br>
                    <span class="description">Número predeterminado de pines a generar por artículo</span>
                </td>
            </tr>
            
            <tr>
                <th scope="row">Opciones de Imagen</th>
                <td>
                    <fieldset>
                        <label>
                            <input type="checkbox" 
                                   name="pincraft_enable_watermark" 
                                   value="1" 
                                   <?php checked($enable_watermark); ?> />
                            🌐 Incluir marca de agua con el dominio del sitio
                        </label>
                        <br><br>
                        
                        <label>
                            <input type="checkbox" 
                                   name="pincraft_enable_analytics" 
                                   value="1" 
                                   <?php checked($enable_analytics); ?> />
                            📊 Habilitar análisis de rendimiento (próximamente)
                        </label>
                    </fieldset>
                </td>
            </tr>
        </table>
        
        <?php submit_button('💾 Guardar Configuración'); ?>
    </form>
    
    <!-- Información del Plan -->
    <div class="card" style="margin-top: 30px;">
        <h2>📋 Información de tu Plan</h2>
        <div id="plan-info">
            <p>🔄 Cargando información del plan...</p>
        </div>
        <button id="refresh-plan-info" class="button">🔄 Actualizar</button>
    </div>
    
    <!-- Estado de la API -->
    <div class="card" style="margin-top: 20px;">
        <h2>🌐 Estado de la API</h2>
        <div id="api-status">
            <p>🔄 Verificando estado...</p>
        </div>
        <button id="test-connection" class="button">🧪 Probar Conexión</button>
    </div>
</div>

<style>
.card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

#plan-info, #api-status {
    background: #f9f9f9;
    padding: 15px;
    border-radius: 4px;
    margin: 10px 0;
}

.status-ok {
    color: #46b450;
}

.status-error {
    color: #dc3232;
}

.status-warning {
    color: #ffb900;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Toggle para mostrar/ocultar API key
    $('#toggle-api-key').click(function() {
        const input = $('#pincraft_api_key');
        const button = $(this);
        
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            button.text('🙈 Ocultar');
        } else {
            input.attr('type', 'password');
            button.text('👁️ Mostrar');
        }
    });
    
    // Validar API key cuando cambie
    $('#pincraft_api_key').on('blur', function() {
        const apiKey = $(this).val();
        const statusDiv = $('#api-key-status');
        
        if (!apiKey) {
            statusDiv.html('<span class="status-warning">⚠️ API Key requerida</span>');
            return;
        }
        
        statusDiv.html('<span>🔄 Validando...</span>');
        
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'pincraft_validate_api_key',
                api_key: apiKey,
                nonce: '<?php echo wp_create_nonce("pincraft_ajax_nonce"); ?>'
            },
            success: function(response) {
                if (response.success) {
                    statusDiv.html('<span class="status-ok">✅ API Key válida - Plan: ' + response.data.plan + '</span>');
                } else {
                    statusDiv.html('<span class="status-error">❌ ' + response.data + '</span>');
                }
            },
            error: function() {
                statusDiv.html('<span class="status-error">❌ Error al validar</span>');
            }
        });
    });
    
    // Cargar información del plan
    function loadPlanInfo() {
        $('#plan-info').html('<p>🔄 Cargando...</p>');
        
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'pincraft_get_account_usage',
                nonce: '<?php echo wp_create_nonce("pincraft_ajax_nonce"); ?>'
            },
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    $('#plan-info').html(`
                        <p><strong>Plan:</strong> ${data.plan || 'Free'}</p>
                        <p><strong>Créditos usados:</strong> ${data.total_used || 0}/${data.limit || 10}</p>
                        <p><strong>Próximo reset:</strong> ${data.reset_date || 'N/A'}</p>
                    `);
                } else {
                    $('#plan-info').html('<p class="status-error">❌ ' + response.data + '</p>');
                }
            },
            error: function() {
                $('#plan-info').html('<p class="status-error">❌ Error al obtener información</p>');
            }
        });
    }
    
    // Probar conexión con la API
    $('#test-connection').click(function() {
        $('#api-status').html('<p>🔄 Probando conexión...</p>');
        
        $.ajax({
            url: '<?php echo esc_js($api_endpoint); ?>/health',
            method: 'GET',
            timeout: 5000,
            success: function() {
                $('#api-status').html('<p class="status-ok">✅ Conexión exitosa con la API</p>');
            },
            error: function() {
                $('#api-status').html('<p class="status-error">❌ No se pudo conectar con la API</p>');
            }
        });
    });
    
    // Actualizar información del plan
    $('#refresh-plan-info').click(loadPlanInfo);
    
    // Cargar información inicial
    loadPlanInfo();
    $('#test-connection').click();
});
</script>