<?php
// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1>🎨 PincraftWP - Generador de Pines</h1>
    
    <div class="pincraft-card">
        <h2>Generar Pines para Pinterest</h2>
        
        <form id="pincraft-generate-form">
            <table class="form-table">
                <tr>
                    <th scope="row">🔍 Buscar Artículo</th>
                    <td>
                        <input type="text" id="pincraft-post-search" class="regular-text" placeholder="Escribe para buscar..." />
                        <input type="hidden" id="pincraft-post-id" name="post_id" />
                        <div id="search-results" style="display:none;"></div>
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
                        <label>
                            <input type="checkbox" id="pincraft-with-text" name="with_text" checked />
                            🔤 Incluir texto en la imagen
                        </label>
                        <br><br>
                        
                        <label>
                            <input type="checkbox" id="pincraft-show-domain" name="show_domain" checked />
                            🌐 Mostrar dominio como marca de agua
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
                        <div id="post-preview" style="display: none; background: #f9f9f9; padding: 15px; border-left: 4px solid #0073aa; margin-top: 10px;">
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
    
    <!-- Historial reciente -->
    <div class="pincraft-card" style="margin-top: 20px;">
        <h2>📋 Generaciones Recientes</h2>
        <div id="recent-history">
            <p>Cargando historial...</p>
        </div>
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

#pincraft-pin-count {
    width: 200px;
    margin-right: 10px;
}

#pin-count-display {
    font-weight: bold;
    color: #0073aa;
}

#search-results {
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    max-height: 200px;
    overflow-y: auto;
    margin-top: 5px;
}

.search-result-item {
    padding: 10px;
    border-bottom: 1px solid #eee;
    cursor: pointer;
}

.search-result-item:hover {
    background: #f0f0f1;
}

.search-result-title {
    font-weight: 500;
    color: #0073aa;
}

.search-result-date {
    color: #666;
    font-size: 12px;
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