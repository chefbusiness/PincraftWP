<?php
/**
 * Vista de ayuda del plugin
 */

// No permitir acceso directo
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1>📚 Ayuda - PincraftWP</h1>
    
    <div class="card">
        <h2>🚀 Primeros Pasos</h2>
        <ol>
            <li><strong>Obtén tu API Key:</strong> Ve a <a href="https://pincraftwp.com" target="_blank">PincraftWP.com</a> y regístrate para obtener tu API Key gratuita.</li>
            <li><strong>Configura la API Key:</strong> Ve a <a href="<?php echo admin_url('admin.php?page=pincraft-settings'); ?>">Configuración</a> e introduce tu API Key.</li>
            <li><strong>Genera tu primer pin:</strong> Ve a <a href="<?php echo admin_url('admin.php?page=pincraft-generator'); ?>">Generar Pines</a>, selecciona un artículo y genera tus pines.</li>
            <li><strong>Descarga o usa:</strong> Los pines se guardan automáticamente en tu biblioteca de medios de WordPress.</li>
        </ol>
    </div>
    
    <div class="card">
        <h2>❓ Preguntas Frecuentes</h2>
        
        <h3>¿Cómo obtengo una API Key?</h3>
        <p>Visita <a href="https://pincraftwp.com" target="_blank">PincraftWP.com</a>, crea una cuenta gratuita y obtendrás tu API Key inmediatamente. El plan gratuito incluye 10 generaciones por mes.</p>
        
        <h3>¿Cuántos pines puedo generar?</h3>
        <p>Depende de tu plan:</p>
        <ul>
            <li><strong>Free:</strong> 10 pines/mes</li>
            <li><strong>Pro:</strong> 200 pines/mes</li>
            <li><strong>Agency:</strong> 1000 pines/mes</li>
        </ul>
        
        <h3>¿Dónde se guardan los pines generados?</h3>
        <p>Los pines se guardan automáticamente en tu biblioteca de medios de WordPress y también puedes verlos en el <a href="<?php echo admin_url('admin.php?page=pincraft-history'); ?>">Historial</a>.</p>
        
        <h3>¿Puedo personalizar el estilo de los pines?</h3>
        <p>Sí, puedes seleccionar diferentes sectores/nichos que optimizan automáticamente el estilo del pin según el tipo de contenido.</p>
        
        <h3>¿Los pines incluyen mi marca?</h3>
        <p>Sí, puedes activar la marca de agua en la <a href="<?php echo admin_url('admin.php?page=pincraft-settings'); ?>">Configuración</a> para incluir tu dominio en los pines.</p>
    </div>
    
    <div class="card">
        <h2>🎯 Sectores Disponibles</h2>
        <p>PincraftWP incluye prompts optimizados para 15+ sectores especializados:</p>
        
        <div style="columns: 2; gap: 20px;">
            <ul>
                <li>🏠 Decoración del Hogar y DIY</li>
                <li>🍲 Recetas y Comida</li>
                <li>👗 Moda Femenina</li>
                <li>💄 Belleza y Cuidado Personal</li>
                <li>👰 Bodas y Eventos</li>
                <li>👶 Maternidad y Bebés</li>
                <li>✈️ Viajes y Aventuras</li>
                <li>💪 Fitness y Ejercicio</li>
                <li>🧘 Salud y Bienestar</li>
                <li>💼 Negocios y Emprendimiento</li>
                <li>📚 Educación y Aprendizaje</li>
                <li>🎨 Arte y Creatividad</li>
                <li>💻 Tecnología y Gadgets</li>
                <li>🌱 Jardín y Plantas</li>
                <li>🐕 Mascotas y Animales</li>
            </ul>
        </div>
        
        <p><em>Más sectores se agregan regularmente. ¡Mantente atento a las actualizaciones!</em></p>
    </div>
    
    <div class="card">
        <h2>🔧 Solución de Problemas</h2>
        
        <h3>Error: "API Key inválida"</h3>
        <ul>
            <li>Verifica que hayas copiado la API Key completa desde tu cuenta en PincraftWP.com</li>
            <li>Asegúrate de que no haya espacios extra al principio o final</li>
            <li>La API Key debe comenzar con "pk_"</li>
        </ul>
        
        <h3>Error: "No se pudo conectar con la API"</h3>
        <ul>
            <li>Verifica tu conexión a internet</li>
            <li>Asegúrate de que tu servidor permita conexiones externas</li>
            <li>Contacta a tu proveedor de hosting si el problema persiste</li>
        </ul>
        
        <h3>Los pines no se generan</h3>
        <ul>
            <li>Verifica que tengas créditos disponibles en tu plan</li>
            <li>Asegúrate de haber seleccionado un artículo y un sector</li>
            <li>Revisa que el artículo tenga contenido suficiente</li>
        </ul>
        
        <h3>Imágenes de mala calidad</h3>
        <ul>
            <li>Asegúrate de seleccionar el sector correcto para tu contenido</li>
            <li>Verifica que el título del artículo sea descriptivo</li>
            <li>Intenta con diferentes configuraciones de texto/marca de agua</li>
        </ul>
    </div>
    
    <div class="card">
        <h2>📞 Soporte</h2>
        <p>¿Necesitas ayuda adicional?</p>
        <ul>
            <li>📧 <strong>Email:</strong> <a href="mailto:support@pincraftwp.com">support@pincraftwp.com</a></li>
            <li>📚 <strong>Documentación:</strong> <a href="https://docs.pincraftwp.com" target="_blank">docs.pincraftwp.com</a></li>
            <li>💬 <strong>Chat en vivo:</strong> Disponible en <a href="https://pincraftwp.com" target="_blank">PincraftWP.com</a></li>
        </ul>
        
        <p><strong>Horarios de soporte:</strong></p>
        <ul>
            <li>Lunes a Viernes: 9:00 AM - 6:00 PM (EST)</li>
            <li>Respuesta típica: 24-48 horas</li>
            <li>Planes Pro/Agency: Soporte prioritario</li>
        </ul>
    </div>
    
    <div class="card">
        <h2>ℹ️ Información del Sistema</h2>
        <table class="form-table">
            <tr>
                <th>Versión del Plugin:</th>
                <td><?php echo PINCRAFT_VERSION; ?></td>
            </tr>
            <tr>
                <th>Versión de WordPress:</th>
                <td><?php echo get_bloginfo('version'); ?></td>
            </tr>
            <tr>
                <th>Versión de PHP:</th>
                <td><?php echo PHP_VERSION; ?></td>
            </tr>
            <tr>
                <th>API Endpoint:</th>
                <td><?php echo get_option('pincraft_api_endpoint', 'No configurado'); ?></td>
            </tr>
            <tr>
                <th>API Key Status:</th>
                <td id="api-key-system-status">🔄 Verificando...</td>
            </tr>
        </table>
    </div>
</div>

<style>
.card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.card h2 {
    margin-top: 0;
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
}

.card h3 {
    color: #0073aa;
    margin-top: 20px;
}

.card ul, .card ol {
    margin: 10px 0;
}

.card li {
    margin: 5px 0;
}

.status-ok {
    color: #46b450;
}

.status-error {
    color: #dc3232;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Verificar estado de la API Key
    function checkApiKeyStatus() {
        const apiKey = '<?php echo esc_js(get_option('pincraft_api_key', '')); ?>';
        const statusElement = $('#api-key-system-status');
        
        if (!apiKey) {
            statusElement.html('<span class="status-error">❌ No configurada</span>');
            return;
        }
        
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
                    statusElement.html('<span class="status-ok">✅ Válida (' + response.data.plan + ')</span>');
                } else {
                    statusElement.html('<span class="status-error">❌ Inválida</span>');
                }
            },
            error: function() {
                statusElement.html('<span class="status-error">❌ Error al verificar</span>');
            }
        });
    }
    
    // Verificar estado al cargar la página
    checkApiKeyStatus();
});
</script>