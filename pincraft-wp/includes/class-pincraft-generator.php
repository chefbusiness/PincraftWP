<?php
/**
 * Clase para generar pines
 */

class Pincraft_Generator {
    
    private $api_endpoint;
    private $api_key;
    
    public function __construct() {
        $this->api_endpoint = get_option('pincraft_api_endpoint', 'https://pincraftwp-production.up.railway.app/api/v1');
        $this->api_key = get_option('pincraft_api_key', '');
    }
    
    /**
     * Generar pines para un post
     */
    public function generate_pins($post_id, $pin_count, $style = 'modern', $sector = null, $with_text = true, $show_domain = true) {
        // Verificar API key
        if (empty($this->api_key)) {
            return array(
                'success' => false,
                'message' => __('API Key no configurada. Ve a Configuración para agregar tu API Key.', 'pincraft-wp')
            );
        }
        
        // Obtener información del post
        $post = get_post($post_id);
        if (!$post) {
            return array(
                'success' => false,
                'message' => __('Post no encontrado', 'pincraft-wp')
            );
        }
        
        // Preparar datos para la API
        $site_url = get_site_url();
        $domain = parse_url($site_url, PHP_URL_HOST);
        
        $request_data = array(
            'title' => $post->post_title,
            'content' => wp_strip_all_tags($post->post_content),
            'excerpt' => $post->post_excerpt ?: wp_trim_words($post->post_content, 50),
            'domain' => $domain,
            'count' => intval($pin_count),
            'style' => $style,
            'sector' => $sector,
            'with_text' => $with_text,
            'show_domain' => $show_domain,
            'post_url' => get_permalink($post_id)
        );
        
        // Hacer llamada a la API
        $response = wp_remote_post($this->api_endpoint . '/pins/generate', array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'x-api-key' => $this->api_key
            ),
            'body' => json_encode($request_data),
            'timeout' => 120 // 2 minutos para generación de imágenes
        ));
        
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => sprintf(
                    __('Error de conexión: %s', 'pincraft-wp'),
                    $response->get_error_message()
                )
            );
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if ($status_code !== 200) {
            $error_message = isset($data['error']) ? $data['error'] : __('Error desconocido', 'pincraft-wp');
            
            // Mensajes de error específicos
            if ($status_code === 403 && isset($data['remaining_credits'])) {
                $error_message = sprintf(
                    __('Créditos insuficientes. Tienes %d créditos restantes pero necesitas %d.', 'pincraft-wp'),
                    $data['remaining_credits'],
                    $data['required_credits']
                );
            }
            
            return array(
                'success' => false,
                'message' => $error_message
            );
        }
        
        // Procesar pines generados
        if (!empty($data['pins'])) {
            // Guardar imágenes en la biblioteca de medios
            $saved_pins = $this->save_pins_to_media_library($data['pins'], $post_id);
            
            // Guardar registro en la base de datos
            $this->save_generation_record($post_id, $saved_pins, $data);
            
            return array(
                'success' => true,
                'data' => array(
                    'pins' => $saved_pins,
                    'generation_id' => $data['generation_id'],
                    'credits_used' => $data['credits_used'],
                    'remaining_credits' => $data['remaining_credits']
                )
            );
        }
        
        return array(
            'success' => false,
            'message' => __('No se pudieron generar los pines', 'pincraft-wp')
        );
    }
    
    /**
     * Guardar pines en la biblioteca de medios
     */
    private function save_pins_to_media_library($pins, $post_id) {
        $saved_pins = array();
        
        foreach ($pins as $pin) {
            // Descargar imagen desde URL
            $image_data = $this->download_image($pin['image_url']);
            
            if ($image_data) {
                // Generar nombre de archivo único
                $filename = sprintf(
                    'pincraft-%d-%s.jpg',
                    $post_id,
                    wp_generate_password(8, false)
                );
                
                // Subir a la biblioteca de medios
                $upload = wp_upload_bits($filename, null, $image_data);
                
                if (!$upload['error']) {
                    // Crear attachment
                    $attachment_id = $this->create_attachment(
                        $upload['file'],
                        $upload['url'],
                        $pin['title'],
                        $pin['description'],
                        $post_id
                    );
                    
                    $saved_pins[] = array(
                        'attachment_id' => $attachment_id,
                        'url' => $upload['url'],
                        'title' => $pin['title'],
                        'description' => $pin['description'],
                        'hashtags' => $pin['hashtags']
                    );
                }
            }
        }
        
        return $saved_pins;
    }
    
    /**
     * Descargar imagen desde URL
     */
    private function download_image($url) {
        $response = wp_remote_get($url, array(
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        
        if (empty($body)) {
            return false;
        }
        
        return $body;
    }
    
    /**
     * Crear attachment en WordPress
     */
    private function create_attachment($file_path, $file_url, $title, $description, $parent_post_id) {
        $file_type = wp_check_filetype(basename($file_path), null);
        
        $attachment = array(
            'guid' => $file_url,
            'post_mime_type' => $file_type['type'],
            'post_title' => $title,
            'post_content' => $description,
            'post_status' => 'inherit',
            'post_parent' => $parent_post_id
        );
        
        $attachment_id = wp_insert_attachment($attachment, $file_path, $parent_post_id);
        
        // Generar metadata del attachment
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attachment_data = wp_generate_attachment_metadata($attachment_id, $file_path);
        wp_update_attachment_metadata($attachment_id, $attachment_data);
        
        // Agregar meta personalizada
        update_post_meta($attachment_id, '_pincraft_generated', true);
        update_post_meta($attachment_id, '_pincraft_pinterest_title', $title);
        update_post_meta($attachment_id, '_pincraft_pinterest_description', $description);
        
        return $attachment_id;
    }
    
    /**
     * Guardar registro de generación
     */
    private function save_generation_record($post_id, $pins, $api_response) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'pincraft_generations';
        
        $wpdb->insert(
            $table_name,
            array(
                'post_id' => $post_id,
                'pin_count' => count($pins),
                'generation_date' => current_time('mysql'),
                'status' => 'completed',
                'pin_urls' => json_encode($pins),
                'metadata' => json_encode(array(
                    'generation_id' => $api_response['generation_id'],
                    'credits_used' => $api_response['credits_used']
                ))
            ),
            array('%d', '%d', '%s', '%s', '%s', '%s')
        );
        
        return $wpdb->insert_id;
    }
    
    /**
     * Obtener historial de generaciones
     */
    public function get_generation_history($limit = 20, $offset = 0) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'pincraft_generations';
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT g.*, p.post_title 
             FROM $table_name g
             LEFT JOIN {$wpdb->posts} p ON g.post_id = p.ID
             ORDER BY g.generation_date DESC
             LIMIT %d OFFSET %d",
            $limit,
            $offset
        ));
        
        foreach ($results as &$result) {
            $result->pin_urls = json_decode($result->pin_urls, true);
            $result->metadata = json_decode($result->metadata, true);
        }
        
        return $results;
    }
    
    /**
     * Obtener uso actual de créditos
     */
    public function get_usage_info() {
        if (empty($this->api_key)) {
            return false;
        }
        
        $response = wp_remote_get($this->api_endpoint . '/account/usage', array(
            'headers' => array(
                'X-API-Key' => $this->api_key
            ),
            'timeout' => 10
        ));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        return json_decode($body, true);
    }
}