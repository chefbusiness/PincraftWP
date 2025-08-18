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
                    <th scope="row">🎨 Paleta de Colores</th>
                    <td>
                        <div class="palette-selector">
                            <div class="palette-grid">
                                <div class="palette-option" data-palette="auto">
                                    <div class="palette-preview auto">
                                        <span class="palette-icon">🤖</span>
                                    </div>
                                    <span class="palette-name">Auto</span>
                                </div>
                                
                                <div class="palette-option" data-palette="ember">
                                    <div class="palette-preview">
                                        <span style="background: #C44536"></span>
                                        <span style="background: #D4A574"></span>
                                        <span style="background: #8B4513"></span>
                                        <span style="background: #CD853F"></span>
                                    </div>
                                    <span class="palette-name">Ember</span>
                                </div>
                                
                                <div class="palette-option" data-palette="fresh">
                                    <div class="palette-preview">
                                        <span style="background: #32CD32"></span>
                                        <span style="background: #98FB98"></span>
                                        <span style="background: #50C878"></span>
                                        <span style="background: #228B22"></span>
                                    </div>
                                    <span class="palette-name">Fresh</span>
                                </div>
                                
                                <div class="palette-option" data-palette="jungle">
                                    <div class="palette-preview">
                                        <span style="background: #355E3B"></span>
                                        <span style="background: #808000"></span>
                                        <span style="background: #6B8E23"></span>
                                        <span style="background: #228B22"></span>
                                    </div>
                                    <span class="palette-name">Jungle</span>
                                </div>
                                
                                <div class="palette-option" data-palette="magic">
                                    <div class="palette-preview">
                                        <span style="background: #8A2BE2"></span>
                                        <span style="background: #E6E6FA"></span>
                                        <span style="background: #191970"></span>
                                        <span style="background: #FF69B4"></span>
                                    </div>
                                    <span class="palette-name">Magic</span>
                                </div>
                                
                                <div class="palette-option" data-palette="melon">
                                    <div class="palette-preview">
                                        <span style="background: #FF6B6B"></span>
                                        <span style="background: #FFA07A"></span>
                                        <span style="background: #32CD32"></span>
                                        <span style="background: #FFA500"></span>
                                    </div>
                                    <span class="palette-name">Melon</span>
                                </div>
                                
                                <div class="palette-option" data-palette="mosaic">
                                    <div class="palette-preview">
                                        <span style="background: #FF1493"></span>
                                        <span style="background: #4169E1"></span>
                                        <span style="background: #40E0D0"></span>
                                        <span style="background: #FF8C00"></span>
                                    </div>
                                    <span class="palette-name">Mosaic</span>
                                </div>
                                
                                <div class="palette-option" data-palette="pastel">
                                    <div class="palette-preview">
                                        <span style="background: #FFB6C1"></span>
                                        <span style="background: #87CEEB"></span>
                                        <span style="background: #F5F5DC"></span>
                                        <span style="background: #DDA0DD"></span>
                                    </div>
                                    <span class="palette-name">Pastel</span>
                                </div>
                                
                                <div class="palette-option" data-palette="ultramarine">
                                    <div class="palette-preview">
                                        <span style="background: #000080"></span>
                                        <span style="background: #4169E1"></span>
                                        <span style="background: #87CEEB"></span>
                                        <span style="background: #48D1CC"></span>
                                    </div>
                                    <span class="palette-name">Ultramarine</span>
                                </div>
                                
                                <!-- PALETAS DE COMIDA -->
                                <div class="palette-option" data-palette="pizza">
                                    <div class="palette-preview">
                                        <span style="background: #C72C41"></span>
                                        <span style="background: #4CAF50"></span>
                                        <span style="background: #FFF8DC"></span>
                                        <span style="background: #8B4513"></span>
                                    </div>
                                    <span class="palette-name">🍕 Pizza</span>
                                </div>
                                
                                <div class="palette-option" data-palette="fastfood">
                                    <div class="palette-preview">
                                        <span style="background: #FFC107"></span>
                                        <span style="background: #DC143C"></span>
                                        <span style="background: #FFFFFF"></span>
                                        <span style="background: #8B4513"></span>
                                    </div>
                                    <span class="palette-name">🍟 Fast Food</span>
                                </div>
                                
                                <div class="palette-option" data-palette="streetfood">
                                    <div class="palette-preview">
                                        <span style="background: #FF6B35"></span>
                                        <span style="background: #004E64"></span>
                                        <span style="background: #25A18E"></span>
                                        <span style="background: #F7B801"></span>
                                    </div>
                                    <span class="palette-name">🌮 Street</span>
                                </div>
                                
                                <div class="palette-option" data-palette="sushi">
                                    <div class="palette-preview">
                                        <span style="background: #2C3E50"></span>
                                        <span style="background: #E74C3C"></span>
                                        <span style="background: #ECEFF1"></span>
                                        <span style="background: #27AE60"></span>
                                    </div>
                                    <span class="palette-name">🍣 Sushi</span>
                                </div>
                                
                                <div class="palette-option" data-palette="cafe">
                                    <div class="palette-preview">
                                        <span style="background: #6F4E37"></span>
                                        <span style="background: #C8AD88"></span>
                                        <span style="background: #FFF8DC"></span>
                                        <span style="background: #2F1B14"></span>
                                    </div>
                                    <span class="palette-name">☕ Café</span>
                                </div>
                                
                                <div class="palette-option" data-palette="healthy">
                                    <div class="palette-preview">
                                        <span style="background: #8BC34A"></span>
                                        <span style="background: #4CAF50"></span>
                                        <span style="background: #CDDC39"></span>
                                        <span style="background: #FFC107"></span>
                                    </div>
                                    <span class="palette-name">🥗 Healthy</span>
                                </div>
                                
                                <div class="palette-option" data-palette="gourmet">
                                    <div class="palette-preview">
                                        <span style="background: #2C3E50"></span>
                                        <span style="background: #BDC3C7"></span>
                                        <span style="background: #D4AF37"></span>
                                        <span style="background: #FFFFFF"></span>
                                    </div>
                                    <span class="palette-name">🍽️ Gourmet</span>
                                </div>
                                
                                <div class="palette-option" data-palette="bbq">
                                    <div class="palette-preview">
                                        <span style="background: #8B2500"></span>
                                        <span style="background: #FF4500"></span>
                                        <span style="background: #FFD700"></span>
                                        <span style="background: #4B0000"></span>
                                    </div>
                                    <span class="palette-name">🔥 BBQ</span>
                                </div>
                                
                                <!-- PALETAS PROFESIONALES -->
                                <div class="palette-option" data-palette="consultant">
                                    <div class="palette-preview">
                                        <span style="background: #1E3A5F"></span>
                                        <span style="background: #4A90E2"></span>
                                        <span style="background: #F5F5F5"></span>
                                        <span style="background: #333333"></span>
                                    </div>
                                    <span class="palette-name">💼 Business</span>
                                </div>
                                
                                <div class="palette-option" data-palette="tech">
                                    <div class="palette-preview">
                                        <span style="background: #00D4FF"></span>
                                        <span style="background: #7B2CBF"></span>
                                        <span style="background: #FF006E"></span>
                                        <span style="background: #1A1A2E"></span>
                                    </div>
                                    <span class="palette-name">💻 Tech</span>
                                </div>
                            </div>
                            
                            <input type="hidden" id="pincraft-color-palette" name="color_palette" value="auto" />
                            
                            <p class="description">
                                🎨 Selecciona una paleta de colores trending para tus pines. <strong>Auto</strong> detecta automáticamente la mejor paleta según el contenido.
                            </p>
                        </div>
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