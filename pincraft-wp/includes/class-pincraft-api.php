<?php
/**
 * Clase API de PincraftWP
 * 
 * Maneja todas las comunicaciones con la API externa de PincraftWP
 */

if (!defined('ABSPATH')) {
    exit;
}

class Pincraft_API {
    
    /**
     * API Key del usuario
     */
    private $api_key;
    
    /**
     * URL base de la API
     */
    private $api_base_url;
    
    /**
     * Timeout para requests
     */
    private $timeout = 30;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->api_key = get_option('pincraft_api_key', '');
        $this->api_base_url = get_option('pincraft_api_endpoint', 'https://pincraftwp-production.up.railway.app/api/v1') . '/';
    }
    
    /**
     * Realizar petición a la API
     */
    private function make_request($endpoint, $method = 'GET', $body = null) {
        if (empty($this->api_key)) {
            return new WP_Error('no_api_key', __('No se ha configurado la API Key', 'pincraft-wp'));
        }
        
        $url = $this->api_base_url . $endpoint;
        
        $args = array(
            'method' => $method,
            'timeout' => $this->timeout,
            'headers' => array(
                'X-API-Key' => $this->api_key,
                'Content-Type' => 'application/json',
                'User-Agent' => 'PincraftWP/' . PINCRAFT_VERSION
            )
        );
        
        if ($body !== null) {
            $args['body'] = is_array($body) ? json_encode($body) : $body;
        }
        
        $response = wp_remote_request($url, $args);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        if ($response_code >= 400) {
            $error_data = json_decode($response_body, true);
            $error_message = isset($error_data['message']) 
                ? $error_data['message'] 
                : sprintf(__('Error HTTP %d', 'pincraft-wp'), $response_code);
            
            return new WP_Error('api_error', $error_message, array('status' => $response_code));
        }
        
        $data = json_decode($response_body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('json_error', __('Error al decodificar respuesta JSON', 'pincraft-wp'));
        }
        
        return $data;
    }
    
    /**
     * Validar API Key
     */
    public function validate_api_key($api_key = null) {
        if ($api_key === null) {
            $api_key = $this->api_key;
        }
        
        if (empty($api_key)) {
            return false;
        }
        
        // Usar API key temporal para validación
        $temp_api_key = $this->api_key;
        $this->api_key = $api_key;
        
        $result = $this->make_request('auth/validate', 'POST');
        
        // Restaurar API key original
        $this->api_key = $temp_api_key;
        
        if (is_wp_error($result)) {
            return false;
        }
        
        return isset($result['valid']) && $result['valid'] === true ? $result : false;
    }
    
    /**
     * Obtener información de la cuenta
     */
    public function get_account_info() {
        $cached = get_transient('pincraft_account_info');
        if ($cached !== false) {
            return $cached;
        }
        
        $result = $this->make_request('account/info');
        
        if (!is_wp_error($result)) {
            set_transient('pincraft_account_info', $result, 5 * MINUTE_IN_SECONDS);
        }
        
        return $result;
    }
    
    /**
     * Obtener uso de créditos
     */
    public function get_usage() {
        $result = $this->make_request('account/usage');
        
        if (is_wp_error($result)) {
            return array(
                'total_used' => 0,
                'limit' => 0,
                'remaining' => 0,
                'reset_date' => '',
                'plan' => 'free'
            );
        }
        
        // Calcular créditos restantes
        $result['remaining'] = max(0, $result['limit'] - $result['total_used']);
        
        return $result;
    }
    
    /**
     * Generar pines para un post
     */
    public function generate_pins($post_id, $count = 4, $style = 'modern', $options = array()) {
        // Obtener datos del post
        $post = get_post($post_id);
        if (!$post) {
            return new WP_Error('invalid_post', __('Post no encontrado', 'pincraft-wp'));
        }
        
        // Preparar contenido
        $title = get_the_title($post);
        $content = wp_strip_all_tags($post->post_content);
        $excerpt = has_excerpt($post) ? get_the_excerpt($post) : wp_trim_words($content, 50);
        $domain = parse_url(home_url(), PHP_URL_HOST);
        
        // Obtener categorías y tags para contexto
        $categories = wp_get_post_categories($post_id, array('fields' => 'names'));
        $tags = wp_get_post_tags($post_id, array('fields' => 'names'));
        
        // Construir request body
        $request_body = array(
            'post_id' => $post_id,
            'title' => $title,
            'content' => substr($content, 0, 2000), // Limitar contenido
            'excerpt' => $excerpt,
            'domain' => $domain,
            'url' => get_permalink($post),
            'count' => intval($count),
            'style' => $style,
            'categories' => $categories,
            'tags' => $tags,
            'options' => wp_parse_args($options, array(
                'color_palette' => get_option('pincraft_color_palette', 'vibrant'),
                'include_branding' => true,
                'optimize_for_pinterest' => true
            ))
        );
        
        // Agregar imagen destacada si existe
        $featured_image_id = get_post_thumbnail_id($post_id);
        if ($featured_image_id) {
            $featured_image_url = wp_get_attachment_image_url($featured_image_id, 'full');
            if ($featured_image_url) {
                $request_body['featured_image'] = $featured_image_url;
            }
        }
        
        // Hacer la petición
        $result = $this->make_request('pins/generate', 'POST', $request_body);
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        // Procesar respuesta
        if (isset($result['pins']) && is_array($result['pins'])) {
            // Guardar URLs de pines generados
            $this->save_generation_history($post_id, $result['pins'], $count, $style);
            
            // Descargar y guardar imágenes en Media Library
            $saved_pins = $this->save_pins_to_media_library($result['pins'], $post_id);
            
            return array(
                'success' => true,
                'pins' => $saved_pins,
                'credits_used' => $result['credits_used'] ?? $count,
                'message' => sprintf(
                    __('%d pines generados exitosamente', 'pincraft-wp'),
                    count($saved_pins)
                )
            );
        }
        
        return new WP_Error('generation_failed', __('Error al generar pines', 'pincraft-wp'));
    }
    
    /**
     * Guardar pines en la biblioteca de medios
     */
    private function save_pins_to_media_library($pins, $post_id) {
        $saved_pins = array();
        $post_title = get_the_title($post_id);
        
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        
        foreach ($pins as $index => $pin) {
            if (!isset($pin['url'])) {
                continue;
            }
            
            // Descargar imagen
            $tmp = download_url($pin['url']);
            
            if (is_wp_error($tmp)) {
                continue;
            }
            
            // Preparar archivo para Media Library
            $file_array = array(
                'name' => sprintf('pincraft-%d-pin-%d-%s.jpg', $post_id, $index + 1, sanitize_title($post_title)),
                'tmp_name' => $tmp
            );
            
            // Guardar en Media Library
            $attachment_id = media_handle_sideload($file_array, $post_id, 
                sprintf(__('Pin %d para: %s', 'pincraft-wp'), $index + 1, $post_title)
            );
            
            if (is_wp_error($attachment_id)) {
                @unlink($tmp);
                continue;
            }
            
            // Agregar metadata
            update_post_meta($attachment_id, '_pincraft_generated', true);
            update_post_meta($attachment_id, '_pincraft_post_id', $post_id);
            update_post_meta($attachment_id, '_pincraft_style', $pin['style'] ?? 'modern');
            update_post_meta($attachment_id, '_pincraft_generated_date', current_time('mysql'));
            
            // Agregar alt text y descripción optimizados para Pinterest
            update_post_meta($attachment_id, '_wp_attachment_image_alt', 
                $pin['alt_text'] ?? $post_title
            );
            
            wp_update_post(array(
                'ID' => $attachment_id,
                'post_content' => $pin['description'] ?? '',
                'post_excerpt' => $pin['pinterest_description'] ?? ''
            ));
            
            $saved_pins[] = array(
                'attachment_id' => $attachment_id,
                'url' => wp_get_attachment_url($attachment_id),
                'thumbnail' => wp_get_attachment_image_url($attachment_id, 'medium'),
                'title' => $pin['title'] ?? $post_title,
                'description' => $pin['description'] ?? '',
                'pinterest_description' => $pin['pinterest_description'] ?? '',
                'hashtags' => $pin['hashtags'] ?? array()
            );
        }
        
        return $saved_pins;
    }
    
    /**
     * Guardar historial de generación
     */
    private function save_generation_history($post_id, $pins, $count, $style) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'pincraft_generations';
        
        $wpdb->insert(
            $table_name,
            array(
                'post_id' => $post_id,
                'pin_count' => $count,
                'generation_date' => current_time('mysql'),
                'status' => 'completed',
                'pin_urls' => json_encode(array_column($pins, 'url')),
                'metadata' => json_encode(array(
                    'style' => $style,
                    'api_version' => '1.0',
                    'wordpress_version' => get_bloginfo('version'),
                    'plugin_version' => PINCRAFT_VERSION
                ))
            ),
            array('%d', '%d', '%s', '%s', '%s', '%s')
        );
    }
    
    /**
     * Obtener historial de generaciones
     */
    public function get_generation_history($post_id = null, $limit = 10) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'pincraft_generations';
        
        $query = "SELECT * FROM $table_name";
        $params = array();
        
        if ($post_id !== null) {
            $query .= " WHERE post_id = %d";
            $params[] = $post_id;
        }
        
        $query .= " ORDER BY generation_date DESC LIMIT %d";
        $params[] = $limit;
        
        if (!empty($params)) {
            $query = $wpdb->prepare($query, $params);
        }
        
        $results = $wpdb->get_results($query, ARRAY_A);
        
        // Decodificar JSON fields
        foreach ($results as &$row) {
            $row['pin_urls'] = json_decode($row['pin_urls'], true);
            $row['metadata'] = json_decode($row['metadata'], true);
        }
        
        return $results;
    }
    
    /**
     * Verificar si hay créditos disponibles
     */
    public function has_credits($required = 1) {
        $usage = $this->get_usage();
        
        if (is_wp_error($usage)) {
            return false;
        }
        
        return $usage['remaining'] >= $required;
    }
    
    /**
     * Obtener estilos disponibles
     */
    public function get_available_styles() {
        return array(
            'modern' => __('Moderno', 'pincraft-wp'),
            'minimalist' => __('Minimalista', 'pincraft-wp'),
            'vibrant' => __('Vibrante', 'pincraft-wp'),
            'elegant' => __('Elegante', 'pincraft-wp'),
            'playful' => __('Divertido', 'pincraft-wp'),
            'professional' => __('Profesional', 'pincraft-wp'),
            'vintage' => __('Vintage', 'pincraft-wp'),
            'bold' => __('Audaz', 'pincraft-wp')
        );
    }
}