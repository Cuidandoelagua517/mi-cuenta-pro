/**
 * JavaScript de administración para Mi Cuenta Mejorado
 */
(function($) {
    'use strict';
    
    /**
     * Objeto principal de administración
     */
    var MAM_Admin = {
        /**
         * Inicializar
         */
        init: function() {
            this.initTabs();
            this.initColorPickers();
            this.initSortableFields();
            this.initSortableSections();
            this.initCustomFieldsManager();
            this.initTooltips();
        },
        
        /**
         * Inicializar sistema de pestañas
         */
        initTabs: function() {
            var $tabs = $('.nav-tab-wrapper .nav-tab');
            var $tabContents = $('.tab-content');
            
            // Establecer pestaña activa al cargar
            var activeTab = window.location.hash || $tabs.first().attr('href');
            
            // Si hay un hash en la URL, seleccionar esa pestaña
            if (activeTab) {
                $tabs.removeClass('nav-tab-active');
                $tabs.filter('[href="' + activeTab + '"]').addClass('nav-tab-active');
                $tabContents.hide();
                $(activeTab).show();
            }
            
            // Cambiar de pestaña al hacer clic
            $tabs.on('click', function(e) {
                e.preventDefault();
                var targetTab = $(this).attr('href');
                
                // Actualizar clases activas
                $tabs.removeClass('nav-tab-active');
                $(this).addClass('nav-tab-active');
                
                // Mostrar contenido de pestaña
                $tabContents.hide();
                $(targetTab).show();
                
                // Actualizar hash en la URL
                window.location.hash = targetTab;
            });
        },
        
        /**
         * Inicializar selectores de color
         */
        initColorPickers: function() {
            $('.mam-color-picker').wpColorPicker();
        },
        
        /**
         * Inicializar ordenación de campos
         */
        initSortableFields: function() {
            $('#mam-sortable-fields').sortable({
                items: 'tr',
                cursor: 'move',
                axis: 'y',
                handle: 'td:first',
                placeholder: 'mam-sortable-placeholder',
                start: function(e, ui) {
                    ui.placeholder.height(ui.item.height());
                },
                update: function(e, ui) {
                    // Actualizar posiciones después de reordenar
                    $('#mam-sortable-fields tr').each(function(index) {
                        $(this).find('input[name$="[position]"]').val(index * 10);
                    });
                    
                    // Opcionalmente, guardar el orden vía AJAX
                    MAM_Admin.saveFieldsOrder();
                }
            });
            
            // Añadir clase a las celdas que actúan como handle
            $('#mam-sortable-fields tr').each(function() {
                $(this).find('td:first').addClass('mam-sortable-handle');
            });
        },
        
        /**
         * Inicializar ordenación de secciones
         */
        initSortableSections: function() {
            $('#mam-sortable-sections').sortable({
                items: 'tr',
                cursor: 'move',
                axis: 'y',
                handle: 'td:first',
                placeholder: 'mam-sortable-placeholder',
                start: function(e, ui) {
                    ui.placeholder.height(ui.item.height());
                },
                update: function(e, ui) {
                    // Actualizar posiciones después de reordenar
                    $('#mam-sortable-sections tr').each(function(index) {
                        $(this).find('input[name$="[position]"]').val(index * 10);
                    });
                    
                    // Opcionalmente, guardar el orden vía AJAX
                    MAM_Admin.saveSectionsOrder();
                }
            });
            
            // Añadir clase a las celdas que actúan como handle
            $('#mam-sortable-sections tr').each(function() {
                $(this).find('td:first').addClass('mam-sortable-handle');
            });
        },
        
        /**
         * Guardar orden de campos vía AJAX
         */
        saveFieldsOrder: function() {
            var fieldsOrder = [];
            
            $('#mam-sortable-fields tr').each(function(index) {
                var fieldId = $(this).data('id');
                fieldsOrder.push(fieldId);
            });
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'mam_save_fields_order',
                    security: mamAdminData.security,
                    fields_order: fieldsOrder
                },
                success: function(response) {
                    if (response.success) {
                        // Mostrar notificación de éxito
                        MAM_Admin.showNotice(response.data.message, 'success');
                    } else {
                        // Mostrar notificación de error
                        MAM_Admin.showNotice(response.data.message, 'error');
                    }
                }
            });
        },
        
        /**
         * Guardar orden de secciones vía AJAX
         */
        saveSectionsOrder: function() {
            var sectionsOrder = [];
            
            $('#mam-sortable-sections tr').each(function(index) {
                var sectionId = $(this).data('id');
                sectionsOrder.push(sectionId);
            });
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'mam_save_sections_order',
                    security: mamAdminData.security,
                    sections_order: sectionsOrder
                },
                success: function(response) {
                    if (response.success) {
                        // Mostrar notificación de éxito
                        MAM_Admin.showNotice(response.data.message, 'success');
                    } else {
                        // Mostrar notificación de error
                        MAM_Admin.showNotice(response.data.message, 'error');
                    }
                }
            });
        },
        
        /**
         * Inicializar gestor de campos personalizados
         */
        initCustomFieldsManager: function() {
            // Abrir modal para añadir campo personalizado
            $('#mam-add-field').on('click', function(e) {
                e.preventDefault();
                $('#mam-add-field-modal').fadeIn(300);
            });
            
            // Cerrar modal
            $('.mam-close-modal').on('click', function() {
                $(this).closest('.mam-modal').fadeOut(200);
            });
            
            // Cerrar modal al hacer clic fuera
            $(window).on('click', function(e) {
                if ($(e.target).hasClass('mam-modal')) {
                    $('.mam-modal').fadeOut(200);
                }
            });
            
            // Enviar formulario de campo personalizado
            $('#mam-add-field-form').on('submit', function(e) {
                e.preventDefault();
                
                var formData = $(this).serialize();
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'mam_add_custom_field',
                        security: mamAdminData.security,
                        formData: formData
                    },
                    beforeSend: function() {
                        // Mostrar indicador de carga
                        $('#mam-add-field-form').append('<span class="spinner is-active"></span>');
                        $('#mam-add-field-form button').prop('disabled', true);
                    },
                    success: function(response) {
                        // Eliminar indicador de carga
                        $('#mam-add-field-form .spinner').remove();
                        $('#mam-add-field-form button').prop('disabled', false);
                        
                        if (response.success) {
                            // Cerrar modal
                            $('#mam-add-field-modal').fadeOut(200);
                            
                            // Mostrar notificación de éxito
                            MAM_Admin.showNotice(response.data.message, 'success');
                            
                            // Recargar la página para mostrar el nuevo campo
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        } else {
                            // Mostrar notificación de error
                            MAM_Admin.showNotice(response.data.message, 'error');
                        }
                    }
                });
            });
            
            // Eliminar campo personalizado
            $('.mam-delete-field').on('click', function(e) {
                e.preventDefault();
                
                var fieldId = $(this).data('id');
                
                if (!confirm(mamAdminData.i18n.confirmDelete)) {
                    return;
                }
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'mam_delete_custom_field',
                        security: mamAdminData.security,
                        field_id: fieldId
                    },
                    beforeSend: function() {
                        // Mostrar indicador de carga
                        $(e.target).after('<span class="spinner is-active" style="float: none;"></span>');
                        $(e.target).hide();
                    },
                    success: function(response) {
                        // Eliminar indicador de carga
                        $(e.target).next('.spinner').remove();
                        $(e.target).show();
                        
                        if (response.success) {
                            // Mostrar notificación de éxito
                            MAM_Admin.showNotice(response.data.message, 'success');
                            
                            // Eliminar fila de la tabla
                            $(e.target).closest('tr').fadeOut(300, function() {
                                $(this).remove();
                            });
                        } else {
                            // Mostrar notificación de error
                            MAM_Admin.showNotice(response.data.message, 'error');
                        }
                    }
                });
            });
            
            // Cambiar opciones según el tipo de campo
            $('#mam-field-type').on('change', function() {
                var fieldType = $(this).val();
                
                // Mostrar u ocultar opciones específicas según el tipo
                if (fieldType === 'select' || fieldType === 'radio' || fieldType === 'checkbox') {
                    if ($('#mam-field-options-wrapper').length === 0) {
                        // Añadir campo de opciones
                        var optionsHtml = '<p id="mam-field-options-wrapper">';
                        optionsHtml += '<label for="mam-field-options">' + mamAdminData.i18n.fieldOptions + ':</label>';
                        optionsHtml += '<textarea id="mam-field-options" name="options" rows="5" class="widefat"></textarea>';
                        optionsHtml += '<span class="description">' + mamAdminData.i18n.fieldOptionsDesc + '</span>';
                        optionsHtml += '</p>';
                        
                        $(this).closest('p').after(optionsHtml);
                    } else {
                        $('#mam-field-options-wrapper').show();
                    }
                } else {
                    $('#mam-field-options-wrapper').hide();
                }
                
                // Actualizar opciones de validación según el tipo
                MAM_Admin.updateValidationOptions(fieldType);
            });
        },
        
        /**
         * Actualizar opciones de validación según el tipo de campo
         */
        updateValidationOptions: function(fieldType) {
            var $validationSelect = $('#mam-field-validation');
            var currentValue = $validationSelect.val();
            
            // Guardar las opciones actuales
            var $options = $validationSelect.find('option');
            var optionsMap = {};
            
            $options.each(function() {
                optionsMap[$(this).val()] = $(this).text();
            });
            
            // Limpiar select
            $validationSelect.empty();
            
            // Añadir opciones según el tipo
            switch (fieldType) {
                case 'email':
                    $validationSelect.append($('<option>', {
                        value: 'email',
                        text: optionsMap['email'],
                        selected: currentValue === 'email'
                    }));
                    break;
                    
                case 'tel':
                    $validationSelect.append($('<option>', {
                        value: 'phone',
                        text: optionsMap['phone'],
                        selected: currentValue === 'phone'
                    }));
                    break;
                    
                case 'number':
                    $validationSelect.append($('<option>', {
                        value: 'number',
                        text: optionsMap['number'],
                        selected: currentValue === 'number'
                    }));
                    break;
                    
                case 'date':
                    $validationSelect.append($('<option>', {
                        value: 'date',
                        text: optionsMap['date'],
                        selected: currentValue === 'date'
                    }));
                    break;
                    
                default:
                    // Para text, textarea, select, radio, checkbox
                    $validationSelect.append($('<option>', {
                        value: 'text',
                        text: optionsMap['text'],
                        selected: currentValue === 'text'
                    }));
                    
                    // Para campos que podrían ser CUIT
                    if (fieldType === 'text') {
                        $validationSelect.append($('<option>', {
                            value: 'cuit',
                            text: optionsMap['cuit'],
                            selected: currentValue === 'cuit'
                        }));
                    }
                    break;
            }
        },
        
        /**
         * Inicializar tooltips
         */
        initTooltips: function() {
            $('.mam-tooltip').on({
                mouseenter: function() {
                    var $tooltip = $(this);
                    var tooltipText = $tooltip.data('tooltip');
                    
                    if (!tooltipText) {
                        return;
                    }
                    
                    // Crear tooltip si no existe
                    if ($tooltip.find('.mam-tooltip-text').length === 0) {
                        $tooltip.append('<span class="mam-tooltip-text">' + tooltipText + '</span>');
                    }
                    
                    // Mostrar tooltip
                    $tooltip.find('.mam-tooltip-text').fadeIn(200);
                },
                mouseleave: function() {
                    // Ocultar tooltip
                    $(this).find('.mam-tooltip-text').fadeOut(200);
                }
            });
        },
        
        /**
         * Mostrar notificación
         */
        showNotice: function(message, type) {
            // Eliminar notificaciones anteriores
            $('.mam-admin-notice').remove();
            
            // Crear nueva notificación
            var $notice = $('<div class="mam-admin-notice notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
            
            // Añadir notificación
            $('.mam-admin-wrap h1').after($notice);
            
            // Hacer que la notificación sea descartable
            $notice.append('<button type="button" class="notice-dismiss"><span class="screen-reader-text">Descartar esta notificación.</span></button>');
            
            // Manejar clic en botón de descarte
            $notice.find('.notice-dismiss').on('click', function() {
                $(this).closest('.mam-admin-notice').fadeOut(200, function() {
                    $(this).remove();
                });
            });
            
            // Desaparecer automáticamente después de 5 segundos
            setTimeout(function() {
                $notice.fadeOut(500, function() {
                    $(this).remove();
                });
            }, 5000);
        }
    };
    
    // Inicializar cuando el DOM esté listo
    $(document).ready(function() {
        MAM_Admin.init();
    });
    
})(jQuery);
