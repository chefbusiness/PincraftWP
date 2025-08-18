<?php
// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

$api_key = get_option('pincraft_api_key', '');
$default_pin_count = get_option('pincraft_default_pin_count', 4);
?>

<div class="wrap">
    <h1>⚙️ Configuración de PincraftWP</h1>
    
    <form method="post" action="">
        <?php wp_nonce_field('pincraft_settings', 'pincraft_nonce'); ?>
        
        <div class="pincraft-card">
            <h2>🔑 Configuración de API</h2>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="pincraft_api_key">API Key de PincraftWP</label>
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
                            <strong>Para testing:</strong> <code>pk_test_b1898d7279d995b11fd3d5a6</code><br>
                            Obtén tu API Key en <a href="https://pincraftwp.com" target="_blank">PincraftWP.com</a>
                        </span>
                        <div id="api-key-status" style="margin-top: 10px;"></div>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="pincraft_default_pin_count">Cantidad por Defecto</label>
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
                        <span class="description">Número predeterminado de pines a generar</span>
                    </td>
                </tr>
            </table>
            
            <?php submit_button('💾 Guardar Configuración'); ?>
        </div>
    </form>
    
    <!-- Estado del sistema -->
    <div class="pincraft-card">
        <h2>🌐 Estado del Sistema</h2>
        <table class="form-table">
            <tr>
                <th>Plugin Version:</th>
                <td><?php echo PINCRAFT_VERSION; ?></td>
            </tr>
            <tr>
                <th>WordPress Version:</th>
                <td><?php echo get_bloginfo('version'); ?></td>
            </tr>
            <tr>
                <th>PHP Version:</th>
                <td><?php echo PHP_VERSION; ?></td>
            </tr>
            <tr>
                <th>API Endpoint:</th>
                <td><code><?php echo PINCRAFT_API_ENDPOINT; ?></code></td>
            </tr>
            <tr>
                <th>API Status:</th>
                <td id="api-status">🔄 Verificando...</td>
            </tr>
        </table>
        <button id="test-connection" class="button">🧪 Probar Conexión</button>
    </div>
</div>

<style>
.pincraft-card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
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
    
    // Validar API key
    $('#pincraft_api_key').on('blur', function() {
        const apiKey = $(this).val();
        const statusDiv = $('#api-key-status');
        
        if (!apiKey) {
            statusDiv.html('<span class="status-warning">⚠️ API Key requerida</span>');
            return;
        }
        
        statusDiv.html('<span>🔄 Validando...</span>');
        
        $.ajax({
            url: pincraftAjax.ajaxurl,
            method: 'POST',
            data: {
                action: 'pincraft_validate_api_key',
                api_key: apiKey,
                nonce: pincraftAjax.nonce
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
    
    // Probar conexión
    $('#test-connection').click(function() {
        $('#api-status').html('<span>🔄 Probando...</span>');
        
        $.ajax({
            url: '<?php echo PINCRAFT_API_ENDPOINT; ?>/health',
            method: 'GET',
            timeout: 5000,
            success: function() {
                $('#api-status').html('<span class="status-ok">✅ API funcionando correctamente</span>');
            },
            error: function() {
                $('#api-status').html('<span class="status-error">❌ No se pudo conectar con la API</span>');
            }
        });
    });
    
    // Auto-test al cargar
    $('#test-connection').click();
});
</script>