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
                        <th scope="row">🔍 Buscar Artículo</th>
                        <td>
                            <input type="text" id="pincraft-post-search" class="regular-text" placeholder="Escribe para buscar..." />
                            <input type="hidden" id="pincraft-post-id" name="post_id" />
                            <div id="search-results" style="display:none; border: 1px solid #ddd; max-height: 200px; overflow-y: auto; margin-top: 5px;"></div>
                            <p class="description">Busca y selecciona el artículo para generar pines</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">📂 Sector/Nicho</th>
                        <td>
                            <select id="pincraft-sector" name="sector" class="regular-text" required>
                                <option value="">-- Seleccionar sector --</option>
                                <option value="decoracion_hogar">🏠 Decoración del Hogar y DIY</option>
                                <option value="recetas_comida">🍲 Recetas y Comida</option>
                                <option value="moda_femenina">👗 Moda Femenina</option>
                                <option value="belleza_cuidado">💄 Belleza y Cuidado Personal</option>
                                <option value="bodas_eventos">👰 Bodas y Eventos</option>
                                <option value="maternidad_bebes">👶 Maternidad y Bebés</option>
                                <option value="viajes_aventuras">✈️ Viajes y Aventuras</option>
                                <option value="fitness_ejercicio">💪 Fitness y Ejercicio</option>
                                <option value="salud_bienestar">🧘 Salud y Bienestar</option>
                                <option value="negocios_emprendimiento">💼 Negocios y Emprendimiento</option>
                                <option value="educacion_aprendizaje">📚 Educación y Aprendizaje</option>
                                <option value="arte_creatividad">🎨 Arte y Creatividad</option>
                                <option value="tecnologia_gadgets">💻 Tecnología y Gadgets</option>
                                <option value="jardin_plantas">🌱 Jardín y Plantas</option>
                                <option value="mascotas_animales">🐕 Mascotas y Animales</option>
                            </select>
                            <p class="description">Selecciona el sector para optimizar el estilo del pin</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">📊 Cantidad de Pines</th>
                        <td>
                            <input type="range" id="pincraft-pin-count" name="pin_count" 
                                   min="1" max="10" value="4" class="regular-text" />
                            <span id="pin-count-display">4</span> pines
                            <p class="description">Desliza para seleccionar cuántos pines generar (1-10)</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">⚙️ Opciones de Imagen</th>
                        <td>
                            <label class="toggle-container">
                                <input type="checkbox" id="pincraft-with-text" name="with_text" checked />
                                <span class="toggle-slider"></span>
                                <span class="toggle-label">🔤 Incluir texto en la imagen</span>
                            </label>
                            <br><br>
                            
                            <label class="toggle-container">
                                <input type="checkbox" id="pincraft-show-domain" name="show_domain" checked />
                                <span class="toggle-slider"></span>
                                <span class="toggle-label">🌐 Mostrar dominio como marca de agua</span>
                            </label>
                            
                            <p class="description">
                                • Texto: Si está desactivado, genera imágenes puramente visuales sin texto<br>
                                • Dominio: Muestra tu sitio web como marca de agua discreta
                            </p>
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

/* Toggle Switches */
.toggle-container {
    display: flex;
    align-items: center;
    margin: 5px 0;
}

.toggle-container input[type="checkbox"] {
    display: none;
}

.toggle-slider {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 24px;
    background-color: #ccc;
    border-radius: 24px;
    transition: background-color 0.3s;
    margin-right: 10px;
}

.toggle-slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    top: 3px;
    background-color: white;
    border-radius: 50%;
    transition: transform 0.3s;
}

.toggle-container input[type="checkbox"]:checked + .toggle-slider {
    background-color: #0073aa;
}

.toggle-container input[type="checkbox"]:checked + .toggle-slider:before {
    transform: translateX(26px);
}

.toggle-label {
    font-weight: 500;
    color: #333;
}

#search-results {
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.search-result-item {
    padding: 10px;
    border-bottom: 1px solid #eee;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
}

.search-result-item:hover {
    background: #f0f0f1;
}

.search-result-item:last-child {
    border-bottom: none;
}

.search-result-title {
    font-weight: 500;
    color: #0073aa;
}

.search-result-date {
    color: #666;
    font-size: 12px;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Actualizar contador de pines
    $('#pincraft-pin-count').on('input', function() {
        $('#pin-count-display').text($(this).val());
    });
    
    // Buscador de posts con autocompletado
    let searchTimeout;
    $('#pincraft-post-search').on('input', function() {
        const query = $(this).val();
        
        clearTimeout(searchTimeout);
        
        if (query.length < 2) {
            $('#search-results').hide();
            return;
        }
        
        searchTimeout = setTimeout(function() {
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'pincraft_search_posts',
                    query: query,
                    nonce: '<?php echo wp_create_nonce("pincraft_search"); ?>'
                },
                success: function(response) {
                    if (response.success && response.data.posts.length > 0) {
                        let html = '';
                        response.data.posts.forEach(function(post) {
                            html += `<div class="search-result-item" data-id="${post.ID}">
                                       <div class="search-result-title">${post.post_title}</div>
                                       <div class="search-result-date">${post.post_date}</div>
                                     </div>`;
                        });
                        $('#search-results').html(html).show();
                    } else {
                        $('#search-results').html('<div class="search-result-item">No se encontraron artículos</div>').show();
                    }
                }
            });
        }, 300);
    });
    
    // Seleccionar post de los resultados
    $(document).on('click', '.search-result-item', function() {
        const postId = $(this).data('id');
        const postTitle = $(this).find('.search-result-title').text();
        
        if (postId) {
            $('#pincraft-post-id').val(postId);
            $('#pincraft-post-search').val(postTitle);
            $('#search-results').hide();
            
            // Mostrar vista previa
            loadPostPreview(postId);
        }
    });
    
    // Función para cargar vista previa
    function loadPostPreview(postId) {
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
    }
    
    // Generar pines
    $('#pincraft-generate-form').on('submit', function(e) {
        e.preventDefault();
        
        const postId = $('#pincraft-post-id').val();
        const sector = $('#pincraft-sector').val();
        const pinCount = $('#pincraft-pin-count').val();
        const withText = $('#pincraft-with-text').is(':checked');
        const showDomain = $('#pincraft-show-domain').is(':checked');
        
        if (!postId) {
            alert('Por favor busca y selecciona un artículo');
            return;
        }
        
        if (!sector) {
            alert('Por favor selecciona un sector/nicho');
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
                sector: sector,
                pin_count: pinCount,
                with_text: withText ? 1 : 0,
                show_domain: showDomain ? 1 : 0,
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