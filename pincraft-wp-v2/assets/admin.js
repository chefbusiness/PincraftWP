/**
 * JavaScript para PincraftWP Admin
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        // Actualizar contador de pines
        $('#pincraft-pin-count').on('input', function() {
            $('#pin-count-display').text($(this).val());
        });
        
        // Buscador de posts
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
                    url: pincraftAjax.ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'pincraft_search_posts',
                        query: query,
                        nonce: pincraftAjax.nonce
                    },
                    success: function(response) {
                        if (response.success && response.data.posts.length > 0) {
                            let html = '';
                            response.data.posts.forEach(function(post) {
                                html += '<div class="search-result-item" data-id="' + post.ID + '">';
                                html += '<div class="search-result-title">' + post.post_title + '</div>';
                                html += '<div class="search-result-date">' + post.post_date + '</div>';
                                html += '</div>';
                            });
                            $('#search-results').html(html).show();
                        } else {
                            $('#search-results').html('<div class="search-result-item">No se encontraron artículos</div>').show();
                        }
                    }
                });
            }, 300);
        });
        
        // Seleccionar post
        $(document).on('click', '.search-result-item', function() {
            const postId = $(this).data('id');
            const postTitle = $(this).find('.search-result-title').text();
            
            if (postId) {
                $('#pincraft-post-id').val(postId);
                $('#pincraft-post-search').val(postTitle);
                $('#search-results').hide();
                
                // Mostrar vista previa
                $('#preview-title').text(postTitle);
                $('#preview-excerpt').text('Post ID: ' + postId);
                $('#preview-url').text(window.location.origin);
                $('#post-preview').show();
            }
        });
        
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
            
            // Actualizar UI
            const $button = $('#generate-pins-btn');
            const $status = $('#generation-status');
            
            $button.prop('disabled', true).text('🔄 Generando...');
            $status.removeClass().addClass('generating').text('Procesando con IA...');
            
            // Hacer petición
            $.ajax({
                url: pincraftAjax.ajaxurl,
                method: 'POST',
                data: {
                    action: 'pincraft_generate_pins',
                    post_id: postId,
                    sector: sector,
                    pin_count: pinCount,
                    with_text: withText ? 1 : 0,
                    show_domain: showDomain ? 1 : 0,
                    nonce: pincraftAjax.nonce
                },
                timeout: 120000,
                success: function(response) {
                    console.log('PincraftWP: API Response:', response);
                    if (response.success) {
                        $status.removeClass().addClass('success')
                            .html('✅ ' + response.data.message + '<br><small>💳 ' + response.data.credits_used + ' créditos utilizados</small>');
                        
                        // Mostrar pines generados con vista previa
                        if (response.data.pins && response.data.pins.length > 0) {
                            let pinsHtml = '<div style="margin-top: 15px;"><strong>📌 Pines generados (' + response.data.pins.length + '):</strong>';
                            pinsHtml += '<div class="pin-preview-container">';
                            
                            response.data.pins.forEach(function(pin, index) {
                                const imageUrl = pin.local_url || pin.image_url;
                                const pinNumber = index + 1;
                                
                                // Parse optimized text
                                const optimizedText = pin.optimized_text || '';
                                const titleMatch = optimizedText.match(/Título Pinterest:\s*["""]?([^"""\n]+)["""]?/i);
                                const descMatch = optimizedText.match(/Descripción:\s*["""]?([^"""\n]+)["""]?/i);
                                
                                const pinTitle = titleMatch ? titleMatch[1].trim() : 'Título no disponible';
                                const pinDescription = descMatch ? descMatch[1].trim() : 'Descripción no disponible';
                                
                                pinsHtml += '<div class="pin-preview-card">';
                                pinsHtml += '<img src="' + imageUrl + '" alt="Pin ' + pinNumber + '" class="pin-preview-image" />';
                                pinsHtml += '<div class="pin-preview-title">Pin ' + pinNumber + '</div>';
                                
                                // Pinterest content section
                                pinsHtml += '<div class="pinterest-content">';
                                pinsHtml += '<div class="pinterest-title-section">';
                                pinsHtml += '<label><strong>📌 Título para Pinterest:</strong></label>';
                                pinsHtml += '<div class="pinterest-text-box">' + pinTitle + '</div>';
                                pinsHtml += '<button type="button" class="copy-btn" onclick="copyToClipboard(\'' + pinTitle.replace(/'/g, "\\'") + '\', this); event.stopPropagation(); return false;">📋 Copiar</button>';
                                pinsHtml += '</div>';
                                
                                pinsHtml += '<div class="pinterest-desc-section">';
                                pinsHtml += '<label><strong>📝 Descripción para Pinterest:</strong></label>';
                                pinsHtml += '<div class="pinterest-text-box">' + pinDescription + '</div>';
                                pinsHtml += '<button type="button" class="copy-btn" onclick="copyToClipboard(\'' + pinDescription.replace(/'/g, "\\'") + '\', this); event.stopPropagation(); return false;">📋 Copiar</button>';
                                pinsHtml += '</div>';
                                pinsHtml += '</div>';
                                
                                if (pin.local_url) {
                                    pinsHtml += '<div class="pin-preview-status" style="color: #46b450;">✅ Guardado en Media Library</div>';
                                    pinsHtml += '<div class="pin-preview-actions">';
                                    pinsHtml += '<a href="' + pin.local_url + '" target="_blank">Ver imagen</a>';
                                    if (pin.attachment_id) {
                                        pinsHtml += ' | <a href="/wp-admin/post.php?post=' + pin.attachment_id + '&action=edit" target="_blank">Editar</a>';
                                    }
                                    pinsHtml += '</div>';
                                } else {
                                    pinsHtml += '<div class="pin-preview-status" style="color: #ffb900;">⚠️ Solo temporal</div>';
                                    pinsHtml += '<div class="pin-preview-actions">';
                                    pinsHtml += '<a href="' + pin.image_url + '" target="_blank">Ver original</a>';
                                    pinsHtml += '</div>';
                                }
                                
                                pinsHtml += '</div>';
                            });
                            
                            pinsHtml += '</div>';
                            pinsHtml += '</div>';
                            $status.append(pinsHtml);
                        }
                    } else {
                        $status.removeClass().addClass('error').text('❌ ' + response.data);
                    }
                },
                error: function(xhr, status, error) {
                    $status.removeClass().addClass('error')
                        .text('❌ Error: ' + (status === 'timeout' ? 'Tiempo de espera agotado' : error));
                },
                complete: function() {
                    $button.prop('disabled', false).text('🎨 Generar Pines');
                }
            });
        });
        
        // Ocultar resultados al hacer clic fuera
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#pincraft-post-search, #search-results').length) {
                $('#search-results').hide();
            }
        });
    });
    
})(jQuery);

// Función global para copiar al portapapeles
function copyToClipboard(text, button, event) {
    // Prevenir propagación del evento
    if (event) {
        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();
    }
    
    navigator.clipboard.writeText(text).then(function() {
        // Cambiar temporalmente el texto del botón
        const originalText = button.innerHTML;
        button.innerHTML = '✅ Copiado';
        button.style.background = '#46b450';
        
        setTimeout(function() {
            button.innerHTML = originalText;
            button.style.background = '';
        }, 2000);
    }).catch(function(err) {
        console.error('Error al copiar: ', err);
        alert('No se pudo copiar al portapapeles');
    });
    
    return false;
}