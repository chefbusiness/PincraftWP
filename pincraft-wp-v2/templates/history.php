<?php
// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Obtener historial
global $wpdb;
$table_name = $wpdb->prefix . 'pincraft_generations';
$history = $wpdb->get_results(
    "SELECT g.*, p.post_title 
     FROM $table_name g
     LEFT JOIN {$wpdb->posts} p ON g.post_id = p.ID
     ORDER BY g.generation_date DESC
     LIMIT 50",
    ARRAY_A
);
?>

<div class="wrap">
    <h1>📋 Historial de Generaciones</h1>
    
    <?php if (empty($history)): ?>
        <div class="pincraft-card">
            <h2>🎨 ¡Comienza a generar pines!</h2>
            <p>Aún no has generado ningún pin.</p>
            <a href="<?php echo admin_url('admin.php?page=pincraft-generator'); ?>" class="button button-primary">🚀 Crear tu primer pin</a>
        </div>
    <?php else: ?>
        <div class="pincraft-card">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>📝 Artículo</th>
                        <th>📂 Sector</th>
                        <th>📊 Pines</th>
                        <th>📅 Fecha</th>
                        <th>✅ Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $generation): ?>
                        <tr>
                            <td>
                                <strong>
                                    <?php if ($generation['post_title']): ?>
                                        <a href="<?php echo get_edit_post_link($generation['post_id']); ?>" target="_blank">
                                            <?php echo esc_html($generation['post_title']); ?>
                                        </a>
                                    <?php else: ?>
                                        <em>Post eliminado (ID: <?php echo $generation['post_id']; ?>)</em>
                                    <?php endif; ?>
                                </strong>
                            </td>
                            <td>
                                <?php 
                                $sectors = array(
                                    'decoracion_hogar' => '🏠 Decoración del Hogar',
                                    'recetas_comida' => '🍲 Recetas y Comida',
                                    'moda_femenina' => '👗 Moda Femenina',
                                    'belleza_cuidado' => '💄 Belleza',
                                    'bodas_eventos' => '👰 Bodas y Eventos',
                                    'maternidad_bebes' => '👶 Maternidad',
                                    'viajes_aventuras' => '✈️ Viajes',
                                    'fitness_ejercicio' => '💪 Fitness',
                                    'salud_bienestar' => '🧘 Salud',
                                    'negocios_emprendimiento' => '💼 Negocios',
                                    'educacion_aprendizaje' => '📚 Educación',
                                    'arte_creatividad' => '🎨 Arte',
                                    'tecnologia_gadgets' => '💻 Tecnología',
                                    'jardin_plantas' => '🌱 Jardín',
                                    'mascotas_animales' => '🐕 Mascotas'
                                );
                                echo $sectors[$generation['sector']] ?? '📌 ' . $generation['sector'];
                                ?>
                            </td>
                            <td>
                                <span style="background: #0073aa; color: white; padding: 2px 8px; border-radius: 3px; font-size: 11px; font-weight: bold;">
                                    <?php echo $generation['pin_count']; ?>
                                </span>
                            </td>
                            <td>
                                <?php 
                                $date = new DateTime($generation['generation_date']);
                                echo $date->format('d/m/Y H:i');
                                ?>
                                <br>
                                <small style="color: #666;">
                                    <?php echo human_time_diff(strtotime($generation['generation_date']), current_time('timestamp')); ?> atrás
                                </small>
                            </td>
                            <td>
                                <?php if ($generation['status'] === 'completed'): ?>
                                    <span style="color: #46b450;">✅ Completado</span>
                                <?php else: ?>
                                    <span style="color: #dc3232;">❌ <?php echo ucfirst($generation['status']); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <p style="margin-top: 20px;">
                <strong>Total de generaciones:</strong> <?php echo count($history); ?>
            </p>
        </div>
    <?php endif; ?>
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
</style>