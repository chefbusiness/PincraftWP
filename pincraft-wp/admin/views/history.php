<?php
/**
 * Vista de historial de generaciones
 */

// No permitir acceso directo
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1>📋 Historial de Generaciones</h1>
    
    <?php if (empty($history)): ?>
        <div class="card">
            <h2>🎨 ¡Comienza a generar pines!</h2>
            <p>Aún no has generado ningún pin. <a href="<?php echo admin_url('admin.php?page=pincraft-generator'); ?>" class="button button-primary">🚀 Crear tu primer pin</a></p>
        </div>
    <?php else: ?>
        <div class="tablenav top">
            <div class="alignleft actions">
                <select id="filter-status">
                    <option value="">Todos los estados</option>
                    <option value="completed">Completados</option>
                    <option value="pending">Pendientes</option>
                    <option value="failed">Fallidos</option>
                </select>
                <button class="button">Filtrar</button>
            </div>
            <div class="alignright">
                <span class="displaying-num"><?php echo count($history); ?> elementos</span>
            </div>
        </div>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th scope="col" class="manage-column">📝 Artículo</th>
                    <th scope="col" class="manage-column">📊 Pines</th>
                    <th scope="col" class="manage-column">📅 Fecha</th>
                    <th scope="col" class="manage-column">✅ Estado</th>
                    <th scope="col" class="manage-column">🎬 Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($history as $generation): ?>
                    <tr>
                        <td>
                            <strong>
                                <?php if ($generation->post_title): ?>
                                    <a href="<?php echo get_edit_post_link($generation->post_id); ?>" target="_blank">
                                        <?php echo esc_html($generation->post_title); ?>
                                    </a>
                                <?php else: ?>
                                    <em>Post eliminado</em>
                                <?php endif; ?>
                            </strong>
                            <br>
                            <small>ID: <?php echo $generation->post_id; ?></small>
                        </td>
                        <td>
                            <span class="pin-count"><?php echo $generation->pin_count; ?></span>
                            <?php if ($generation->pin_urls && is_array($generation->pin_urls)): ?>
                                <br>
                                <button class="button button-small view-pins" data-generation-id="<?php echo $generation->id; ?>">
                                    👁️ Ver pines
                                </button>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php 
                            $date = new DateTime($generation->generation_date);
                            echo $date->format('d/m/Y H:i');
                            ?>
                            <br>
                            <small><?php echo human_time_diff(strtotime($generation->generation_date), current_time('timestamp')); ?> ago</small>
                        </td>
                        <td>
                            <?php
                            $status_icons = array(
                                'completed' => '✅',
                                'pending' => '🔄',
                                'failed' => '❌'
                            );
                            $status_labels = array(
                                'completed' => 'Completado',
                                'pending' => 'Pendiente',
                                'failed' => 'Fallido'
                            );
                            $status = $generation->status;
                            echo $status_icons[$status] ?? '❓';
                            echo ' ' . ($status_labels[$status] ?? $status);
                            ?>
                            
                            <?php if ($generation->metadata && is_array($generation->metadata)): ?>
                                <br>
                                <small>
                                    <?php if (isset($generation->metadata['credits_used'])): ?>
                                        💳 <?php echo $generation->metadata['credits_used']; ?> créditos
                                    <?php endif; ?>
                                </small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="row-actions">
                                <?php if ($generation->status === 'completed' && $generation->pin_urls): ?>
                                    <span class="view">
                                        <a href="#" class="view-pins" data-generation-id="<?php echo $generation->id; ?>">Ver pines</a> |
                                    </span>
                                    <span class="download">
                                        <a href="#" class="download-all" data-generation-id="<?php echo $generation->id; ?>">Descargar</a> |
                                    </span>
                                <?php endif; ?>
                                
                                <span class="regenerate">
                                    <a href="<?php echo admin_url('admin.php?page=pincraft-generator&post_id=' . $generation->post_id); ?>">Regenerar</a> |
                                </span>
                                
                                <span class="delete">
                                    <a href="#" class="delete-generation" data-id="<?php echo $generation->id; ?>" style="color: #a00;">Eliminar</a>
                                </span>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Modal para ver pines -->
<div id="pins-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2>🎨 Pines Generados</h2>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body">
            <div id="pins-gallery"></div>
        </div>
        <div class="modal-footer">
            <button class="button button-primary" id="download-all-pins">📥 Descargar Todos</button>
            <button class="button" id="close-modal">Cerrar</button>
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

.pin-count {
    background: #0073aa;
    color: white;
    padding: 2px 8px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: bold;
}

.view-pins, .download-all, .delete-generation {
    cursor: pointer;
}

/* Modal styles */
.modal {
    position: fixed;
    z-index: 100000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 0;
    border: none;
    border-radius: 4px;
    width: 80%;
    max-width: 800px;
    max-height: 80vh;
    overflow: hidden;
    box-shadow: 0 4px 8px rgba(0,0,0,0.3);
}

.modal-header {
    padding: 20px;
    border-bottom: 1px solid #ddd;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    margin: 0;
}

.close {
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    color: #aaa;
}

.close:hover {
    color: #000;
}

.modal-body {
    padding: 20px;
    max-height: 60vh;
    overflow-y: auto;
}

.modal-footer {
    padding: 20px;
    border-top: 1px solid #ddd;
    text-align: right;
}

#pins-gallery {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 20px;
}

.pin-item {
    text-align: center;
}

.pin-item img {
    max-width: 100%;
    height: auto;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.pin-actions {
    margin-top: 10px;
}

.pin-actions a {
    margin: 0 5px;
    text-decoration: none;
    color: #0073aa;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Ver pines en modal
    $('.view-pins').click(function(e) {
        e.preventDefault();
        const generationId = $(this).data('generation-id');
        
        // Aquí cargarías los pines desde la base de datos
        // Por ahora mostramos placeholder
        $('#pins-gallery').html('<p>🔄 Cargando pines...</p>');
        $('#pins-modal').show();
        
        // Simular carga de pines
        setTimeout(function() {
            $('#pins-gallery').html(`
                <div class="pin-item">
                    <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjM1MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZGRkIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPkVqZW1wbG8gUGluPC90ZXh0Pjwvc3ZnPg==" alt="Pin generado">
                    <div class="pin-actions">
                        <a href="#" class="download-pin">📥 Descargar</a>
                        <a href="#" class="view-full">🔍 Ver completo</a>
                    </div>
                </div>
            `);
        }, 1000);
    });
    
    // Cerrar modal
    $('.close, #close-modal').click(function() {
        $('#pins-modal').hide();
    });
    
    // Cerrar modal al hacer click fuera
    $(window).click(function(e) {
        if (e.target.id === 'pins-modal') {
            $('#pins-modal').hide();
        }
    });
    
    // Eliminar generación
    $('.delete-generation').click(function(e) {
        e.preventDefault();
        const generationId = $(this).data('id');
        
        if (confirm('¿Estás seguro de que quieres eliminar esta generación?')) {
            // Aquí implementarías la eliminación
            alert('Función de eliminación no implementada aún');
        }
    });
    
    // Descargar todos los pines
    $('.download-all, #download-all-pins').click(function(e) {
        e.preventDefault();
        alert('Función de descarga masiva no implementada aún');
    });
    
    // Filtros
    $('#filter-status').change(function() {
        const status = $(this).val();
        const rows = $('tbody tr');
        
        if (status === '') {
            rows.show();
        } else {
            rows.hide();
            rows.filter(':contains("' + status + '")').show();
        }
    });
});
</script>