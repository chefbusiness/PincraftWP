/**
 * JavaScript para el panel de administración de PincraftWP
 */

(function($) {
    'use strict';
    
    // Variables globales
    const PincraftAdmin = {
        currentPost: null,
        isGenerating: false,
        
        // Inicializar
        init: function() {
            this.bindEvents();
            this.initializeComponents();
        },
        
        // Vincular eventos
        bindEvents: function() {
            // Búsqueda de posts
            $(document).on('input', '#pincraft-post-search', this.handlePostSearch);
            $(document).on('click', '.search-result-item', this.selectPost);
            
            // Generación de pines
            $(document).on('submit', '#pincraft-generate-form', this.handleGeneration);
            
            // Controles de cantidad
            $(document).on('input', '#pincraft-pin-count', this.updatePinCountDisplay);
            
            // Validación de API Key
            $(document).on('blur', '#pincraft_api_key', this.validateApiKey);
            
            // Toggle para mostrar/ocultar API Key
            $(document).on('click', '#toggle-api-key', this.toggleApiKeyVisibility);
            
            // Cerrar mensajes
            $(document).on('click', '.notice-dismiss', this.dismissNotice);
        },
        
        // Inicializar componentes
        initializeComponents: function() {
            this.updatePinCountDisplay();
            this.checkApiKeyStatus();
        },
        
        // Búsqueda de posts
        handlePostSearch: function(e) {
            const query = $(e.target).val();
            const resultsContainer = $('#search-results');
            
            // Limpiar timeout anterior
            clearTimeout(PincraftAdmin.searchTimeout);
            
            if (query.length < 2) {
                resultsContainer.hide();
                return;
            }
            
            // Debounce search
            PincraftAdmin.searchTimeout = setTimeout(function() {
                PincraftAdmin.performSearch(query);
            }, 300);
        },
        
        // Realizar búsqueda
        performSearch: function(query) {
            const resultsContainer = $('#search-results');
            
            resultsContainer.html('<div class="search-result-item">🔄 Buscando...</div>').show();
            
            $.ajax({
                url: pincraftAdmin.ajaxUrl,
                method: 'POST',
                data: {
                    action: 'pincraft_search_posts',
                    query: query,
                    nonce: pincraftAdmin.nonce
                },
                success: function(response) {
                    if (response.success && response.data.posts.length > 0) {
                        let html = '';
                        response.data.posts.forEach(function(post) {
                            html += `
                                <div class="search-result-item" data-id="${post.ID}">
                                    <div class="search-result-title">${post.post_title}</div>
                                    <div class="search-result-date">${post.post_date}</div>
                                    <div class="search-result-excerpt">${post.post_excerpt || ''}</div>
                                </div>
                            `;
                        });
                        resultsContainer.html(html);
                    } else {
                        resultsContainer.html('<div class="search-result-item">📝 No se encontraron artículos</div>');
                    }
                },
                error: function() {
                    resultsContainer.html('<div class="search-result-item pincraft-error">❌ Error en la búsqueda</div>');
                }
            });
        },
        
        // Seleccionar post
        selectPost: function(e) {
            const $item = $(e.currentTarget);
            const postId = $item.data('id');
            const postTitle = $item.find('.search-result-title').text();
            
            if (!postId) return;
            
            // Actualizar interfaz
            $('#pincraft-post-id').val(postId);
            $('#pincraft-post-search').val(postTitle);
            $('#search-results').hide();
            
            // Guardar referencia
            PincraftAdmin.currentPost = { id: postId, title: postTitle };
            
            // Mostrar vista previa
            PincraftAdmin.showPostPreview(postId);
            
            // Animar selección
            $item.addClass('selected');
            setTimeout(() => $item.removeClass('selected'), 500);
        },
        
        // Mostrar vista previa del post
        showPostPreview: function(postId) {
            const previewContainer = $('#post-preview');
            
            $.ajax({
                url: pincraftAdmin.ajaxUrl,
                method: 'POST',
                data: {
                    action: 'pincraft_get_post_preview',
                    post_id: postId,
                    nonce: pincraftAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        const data = response.data;
                        $('#preview-title').text(data.title);
                        $('#preview-excerpt').text(data.excerpt);
                        $('#preview-url').text(data.url);
                        previewContainer.show().addClass('fadeIn');
                    }
                },
                error: function() {
                    previewContainer.hide();
                }
            });
        },
        
        // Manejar generación de pines
        handleGeneration: function(e) {
            e.preventDefault();
            
            if (PincraftAdmin.isGenerating) {
                return false;
            }
            
            // Validar formulario
            const validation = PincraftAdmin.validateForm();
            if (!validation.valid) {
                PincraftAdmin.showNotice(validation.message, 'error');
                return false;
            }
            
            // Iniciar generación
            PincraftAdmin.startGeneration();
        },
        
        // Validar formulario
        validateForm: function() {
            const postId = $('#pincraft-post-id').val();
            const sector = $('#pincraft-sector').val();
            
            if (!postId) {
                return { valid: false, message: 'Por favor busca y selecciona un artículo' };
            }
            
            if (!sector) {
                return { valid: false, message: 'Por favor selecciona un sector/nicho' };
            }
            
            return { valid: true };
        },
        
        // Iniciar generación
        startGeneration: function() {
            PincraftAdmin.isGenerating = true;
            
            // Actualizar UI
            const $button = $('#generate-pins-btn');
            const $status = $('#generation-status');
            
            $button.prop('disabled', true).text('🔄 Generando...');
            $status.removeClass().addClass('generating').text('Procesando con IA...');
            
            // Recopilar datos del formulario
            const formData = {
                action: 'pincraft_generate_pins',
                post_id: $('#pincraft-post-id').val(),
                sector: $('#pincraft-sector').val(),
                pin_count: $('#pincraft-pin-count').val(),
                with_text: $('#pincraft-with-text').is(':checked'),
                show_domain: $('#pincraft-show-domain').is(':checked'),
                nonce: pincraftAdmin.nonce
            };
            
            // Hacer petición AJAX
            $.ajax({
                url: pincraftAdmin.ajaxUrl,
                method: 'POST',
                data: formData,
                timeout: 120000, // 2 minutos
                success: function(response) {
                    if (response.success) {
                        PincraftAdmin.onGenerationSuccess(response.data);
                    } else {
                        PincraftAdmin.onGenerationError(response.data);
                    }
                },
                error: function(xhr, status, error) {
                    PincraftAdmin.onGenerationError(
                        status === 'timeout' ? 'Tiempo de espera agotado' : error
                    );
                },
                complete: function() {
                    PincraftAdmin.finishGeneration();
                }
            });
        },
        
        // Éxito en la generación
        onGenerationSuccess: function(data) {
            const $status = $('#generation-status');
            $status.removeClass().addClass('pincraft-success').text('✅ Pines generados exitosamente!');
            
            // Mostrar información adicional
            if (data.credits_used) {
                $status.append(`<br><small>💳 ${data.credits_used} créditos utilizados</small>`);
            }
            
            if (data.remaining_credits !== undefined) {
                $status.append(`<br><small>📊 ${data.remaining_credits} créditos restantes</small>`);
            }
            
            // Mostrar notificación
            PincraftAdmin.showNotice('¡Pines generados exitosamente! Revisa tu biblioteca de medios.', 'success');
            
            // Recargar historial si existe
            if (typeof PincraftAdmin.loadHistory === 'function') {
                PincraftAdmin.loadHistory();
            }
        },
        
        // Error en la generación
        onGenerationError: function(message) {
            const $status = $('#generation-status');
            $status.removeClass().addClass('pincraft-error').text('❌ ' + message);
            
            PincraftAdmin.showNotice('Error: ' + message, 'error');
        },
        
        // Finalizar generación
        finishGeneration: function() {
            PincraftAdmin.isGenerating = false;
            
            const $button = $('#generate-pins-btn');
            $button.prop('disabled', false).text('🎨 Generar Pines');
        },
        
        // Actualizar display de cantidad de pines
        updatePinCountDisplay: function() {
            const count = $('#pincraft-pin-count').val() || 4;
            $('#pin-count-display').text(count);
        },
        
        // Validar API Key
        validateApiKey: function(e) {
            const apiKey = $(e.target).val();
            const statusContainer = $('#api-key-status');
            
            if (!apiKey) {
                statusContainer.html('<span class="pincraft-warning">⚠️ API Key requerida</span>');
                return;
            }
            
            statusContainer.html('<span>🔄 Validando...</span>');
            
            $.ajax({
                url: pincraftAdmin.ajaxUrl,
                method: 'POST',
                data: {
                    action: 'pincraft_validate_api_key',
                    api_key: apiKey,
                    nonce: pincraftAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        statusContainer.html(`<span class="pincraft-success">✅ API Key válida - Plan: ${response.data.plan}</span>`);
                    } else {
                        statusContainer.html(`<span class="pincraft-error">❌ ${response.data}</span>`);
                    }
                },
                error: function() {
                    statusContainer.html('<span class="pincraft-error">❌ Error al validar</span>');
                }
            });
        },
        
        // Toggle visibilidad de API Key
        toggleApiKeyVisibility: function(e) {
            const $input = $('#pincraft_api_key');
            const $button = $(e.target);
            
            if ($input.attr('type') === 'password') {
                $input.attr('type', 'text');
                $button.text('🙈 Ocultar');
            } else {
                $input.attr('type', 'password');
                $button.text('👁️ Mostrar');
            }
        },
        
        // Verificar estado de API Key
        checkApiKeyStatus: function() {
            const apiKey = pincraftAdmin.apiKey;
            
            if (!apiKey) {
                PincraftAdmin.showNotice('Por favor configura tu API Key en la sección de Configuración.', 'warning');
            }
        },
        
        // Mostrar notificación
        showNotice: function(message, type = 'info') {
            const noticeClass = type === 'error' ? 'notice-error' : 
                               type === 'success' ? 'notice-success' : 
                               type === 'warning' ? 'notice-warning' : 'notice-info';
            
            const notice = $(`
                <div class="notice ${noticeClass} is-dismissible">
                    <p>${message}</p>
                    <button type="button" class="notice-dismiss">
                        <span class="screen-reader-text">Descartar este aviso.</span>
                    </button>
                </div>
            `);
            
            // Insertar notificación
            $('.wrap h1').after(notice);
            
            // Auto-remove después de 5 segundos
            setTimeout(function() {
                notice.fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
        },
        
        // Descartar notificación
        dismissNotice: function(e) {
            $(e.target).closest('.notice').fadeOut(function() {
                $(this).remove();
            });
        },
        
        // Utilidades
        utils: {
            // Formatear fecha
            formatDate: function(dateString) {
                const date = new Date(dateString);
                return date.toLocaleDateString('es-ES', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            },
            
            // Debounce
            debounce: function(func, wait, immediate) {
                let timeout;
                return function executedFunction() {
                    const context = this;
                    const args = arguments;
                    const later = function() {
                        timeout = null;
                        if (!immediate) func.apply(context, args);
                    };
                    const callNow = immediate && !timeout;
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                    if (callNow) func.apply(context, args);
                };
            },
            
            // Capitalizar primera letra
            capitalize: function(str) {
                return str.charAt(0).toUpperCase() + str.slice(1);
            }
        }
    };
    
    // Inicializar cuando el DOM esté listo
    $(document).ready(function() {
        PincraftAdmin.init();
    });
    
    // Exponer objeto globalmente para extensiones
    window.PincraftAdmin = PincraftAdmin;
    
})(jQuery);