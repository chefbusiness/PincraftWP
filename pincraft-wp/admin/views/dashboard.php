<?php
/**
 * Dashboard principal del plugin
 */

// No permitir acceso directo
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1>PincraftWP - Generador de Pines</h1>
    
    <div id="pincraft-dashboard">
        <!-- Configuración de API Key -->
        <div class="card" style="margin-bottom: 20px;">
            <h2>🔑 Configuración API</h2>
            <form method="post" action="">
                <?php wp_nonce_field('pincraft_save_settings', 'pincraft_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">API Key de PincraftWP</th>
                        <td>
                            <input type="password" name="pincraft_api_key" 
                                   value="<?php echo esc_attr(get_option('pincraft_api_key')); ?>" 
                                   class="regular-text" placeholder="pk_..." />
                            <p class="description">
                                Obtén tu API Key en <a href="https://pincraftwp.com" target="_blank">PincraftWP.com</a>
                            </p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button('Guardar Configuración'); ?>
            </form>
        </div>

        <!-- Selector de artículos y generación -->
        <div class="card">
            <h2>🎨 Generar Pines</h2>
            
            <form id="pincraft-generate-form">
                <table class="form-table">
                    <tr>
                        <th scope="row">Seleccionar Artículo</th>
                        <td>
                            <select id="pincraft-post-selector" name="post_id" class="regular-text" required>
                                <option value="">-- Seleccionar artículo --</option>
                                <?php
                                $posts = get_posts(array(
                                    'numberposts' => 50,
                                    'post_status' => 'publish',
                                    'post_type' => 'post'
                                ));
                                
                                foreach($posts as $post) {
                                    echo '<option value="' . $post->ID . '">' . esc_html($post->post_title) . '</option>';
                                }
                                ?>
                            </select>
                            <p class="description">Selecciona el artículo para generar pines</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Cantidad de Pines</th>
                        <td>
                            <input type="range" id="pincraft-pin-count" name="pin_count" 
                                   min="1" max="10" value="4" class="regular-text" />
                            <span id="pin-count-display">4</span> pines
                            <p class="description">Desliza para seleccionar cuántos pines generar (1-10)</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Vista Previa</th>
                        <td>
                            <div id="post-preview" style="display: none;">
                                <h4 id="preview-title"></h4>
                                <p id="preview-excerpt"></p>
                                <small id="preview-url"></small>
                            </div>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <button type="submit" class="button button-primary" id="generate-pins-btn">
                        🎨 Generar Pines
                    </button>
                    <span id="generation-status" style="margin-left: 10px;"></span>
                </p>
            </form>
        </div>

        <!-- Historial de generaciones -->
        <div class="card" style="margin-top: 20px;">
            <h2>📋 Historial de Generaciones</h2>
            <div id="pincraft-history">
                <p>Cargando historial...</p>
            </div>
        </div>
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

#pincraft-pin-count {
    width: 200px;
    margin-right: 10px;
}

#pin-count-display {
    font-weight: bold;
    color: #0073aa;
}

#post-preview {
    background: #f9f9f9;
    padding: 15px;
    border-left: 4px solid #0073aa;
    margin-top: 10px;
}

#generation-status {
    font-weight: bold;
}

.generating {
    color: #0073aa;
}

.success {
    color: #46b450;
}

.error {
    color: #dc3232;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Actualizar contador de pines
    $('#pincraft-pin-count').on('input', function() {
        $('#pin-count-display').text($(this).val());
    });
    
    // Vista previa del artículo
    $('#pincraft-post-selector').on('change', function() {
        const postId = $(this).val();
        if (postId) {
            // Hacer petición AJAX para obtener datos del post
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'pincraft_get_post_preview',
                    post_id: postId,
                    nonce: '<?php echo wp_create_nonce("pincraft_preview"); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        $('#preview-title').text(response.data.title);
                        $('#preview-excerpt').text(response.data.excerpt);
                        $('#preview-url').text(response.data.url);
                        $('#post-preview').show();
                    }
                }
            });
        } else {
            $('#post-preview').hide();
        }
    });
    
    // Generar pines
    $('#pincraft-generate-form').on('submit', function(e) {
        e.preventDefault();
        
        const postId = $('#pincraft-post-selector').val();
        const pinCount = $('#pincraft-pin-count').val();
        
        if (!postId) {
            alert('Por favor selecciona un artículo');
            return;
        }
        
        // Cambiar estado del botón
        $('#generate-pins-btn').prop('disabled', true).text('🔄 Generando...');
        $('#generation-status').removeClass().addClass('generating').text('Procesando...');
        
        // Hacer petición AJAX para generar pines
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'pincraft_generate_pins',
                post_id: postId,
                pin_count: pinCount,
                nonce: '<?php echo wp_create_nonce("pincraft_generate"); ?>'
            },
            timeout: 60000, // 60 segundos
            success: function(response) {
                if (response.success) {
                    $('#generation-status').removeClass().addClass('success')
                        .text('✅ ' + response.data.message);
                    
                    // Recargar historial
                    loadHistory();
                } else {
                    $('#generation-status').removeClass().addClass('error')
                        .text('❌ ' + response.data.message);
                }
            },
            error: function(xhr, status, error) {
                $('#generation-status').removeClass().addClass('error')
                    .text('❌ Error: ' + error);
            },
            complete: function() {
                $('#generate-pins-btn').prop('disabled', false).text('🎨 Generar Pines');
            }
        });
    });
    
    // Cargar historial
    function loadHistory() {
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'pincraft_get_history',
                nonce: '<?php echo wp_create_nonce("pincraft_history"); ?>'
            },
            success: function(response) {
                if (response.success) {
                    $('#pincraft-history').html(response.data.html);
                }
            }
        });
    }
    
    // Cargar historial al inicio
    loadHistory();
});
</script>