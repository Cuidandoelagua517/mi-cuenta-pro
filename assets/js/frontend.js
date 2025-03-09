/**
 * JavaScript del frontend para Mi Cuenta Mejorado
 */
(function($) {
    'use strict';
    
    /**
     * Objeto principal del plugin
     */
(function($) {
    'use strict';
    
    /**
     * Objeto principal del plugin
     */
    var MAM = {
        /**
         * Inicializar
         */
        init: function() {
            // Inicializar todos los módulos
           this.initFormValidation();
    this.initEnhancedFields();
    this.initMobileMenu();
    this.initModernized();
    this.initDashboardNavigation(); // Añadir esta línea
},
        /**
 * Manejo de navegación AJAX en Mi Cuenta
 */
function initAccountNavigation() {
    // Selector preciso para todos los enlaces de navegación
    $(document).on('click', '.mam-nav-menu a, .woocommerce-MyAccount-navigation a', function(e) {
        // No interceptar enlaces de logout
        if ($(this).parent().hasClass('woocommerce-MyAccount-navigation-link--customer-logout') || 
            $(this).attr('href').indexOf('customer-logout') > -1) {
            return true; // Permitir comportamiento normal
        }
        
        e.preventDefault();
        var url = $(this).attr('href');
        
        // Mostrar indicador de carga
        if ($('.mam-ajax-loader').length === 0) {
            $('body').append('<div class="mam-ajax-loader"><div class="mam-loader-spinner"></div></div>');
        }
        $('.mam-ajax-loader').show();
        
        // Marcar elemento activo
        $('.mam-nav-menu li, .woocommerce-MyAccount-navigation li').removeClass('active is-active');
        $(this).closest('li').addClass('active is-active');
        
        // Cargar contenido mediante AJAX
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'html',
            success: function(response) {
                // Ocultar loader
                $('.mam-ajax-loader').hide();
                
                // Extraer contenido principal
                var $html = $(response);
                var $content = $html.find('.woocommerce-MyAccount-content');
                
                if ($content.length === 0) {
                    $content = $html.find('.mam-main-content');
                }
                
                // Actualizar contenido
                if ($content.length > 0) {
                    $('.mam-main-content, .woocommerce-MyAccount-content').html($content.html());
                    
                    // Actualizar URL sin recargar
                    if (window.history && window.history.pushState) {
                        window.history.pushState(null, null, url);
                    }
                } else {
                    // Si falla la extracción, redirigir
                    window.location.href = url;
                }
            },
            error: function() {
                $('.mam-ajax-loader').hide();
                window.location.href = url;
            }
        });
    });
}
        initDashboardNavigation: function() {
            console.log('MAM Dashboard Navigation initialized'); // Debug
            
            // Selector preciso para los enlaces de navegación
            $(document).on('click', '.mam-nav-menu a, .woocommerce-MyAccount-navigation-link a', function(e) {
                console.log('Navigation link clicked'); // Debug
                
                // Excluir explícitamente enlaces de logout
                if ($(this).parent().hasClass('woocommerce-MyAccount-navigation-link--customer-logout') || 
                    $(this).attr('href').indexOf('customer-logout') > -1) {
                    console.log('Logout link detected, allowing default behavior');
                    return true; // Permitir comportamiento por defecto
                }
                
                e.preventDefault();
                e.stopPropagation();
                
                // URL del enlace
                var url = $(this).attr('href');
                console.log('Loading URL:', url); // Debug
                
                // Mostrar indicador de carga
                if ($('.mam-ajax-loader').length === 0) {
                    $('body').append('<div class="mam-ajax-loader"><div class="mam-loader-spinner"></div></div>');
                }
                $('.mam-ajax-loader').show();
                
                // Marcar elemento activo (tanto en navegación personalizada como en WooCommerce nativa)
                $('.mam-nav-menu li, .woocommerce-MyAccount-navigation li').removeClass('is-active active current-menu-item');
                $(this).closest('li').addClass('is-active active current-menu-item');
                
                // Cargar contenido mediante AJAX
                $.ajax({
                    url: url,
                    type: 'GET',
                    dataType: 'html',
                    success: function(response) {
                        console.log('AJAX response received'); // Debug
                        
                        // Ocultar loader
                        $('.mam-ajax-loader').hide();
                        
                        // Analizar respuesta HTML
                        var $html = $(response);
                        
                        // Buscar el contenido específico con múltiples selectores posibles
                        var $content = $html.find('.woocommerce-MyAccount-content');
                        if ($content.length === 0) {
                            $content = $html.find('.mam-main-content');
                        }
                        if ($content.length === 0) {
                            $content = $html.find('#mam-content-area');
                        }
                        
                        console.log('Content found:', $content.length > 0); // Debug
                        
                        // Si encontramos contenido, actualizar la página
                        if ($content.length > 0) {
                            // Actualizar el área de contenido
                            $('.woocommerce-MyAccount-content, .mam-main-content').html($content.html());
                            
                            // Actualizar URL sin recargar
                            if (window.history && window.history.pushState) {
                                window.history.pushState(null, null, url);
                            }
                            
                            // Re-inicializar scripts específicos
                            MAM.initEnhancedFields();
                            
                            // Notificar a otros scripts que el contenido ha cambiado
                            $(document).trigger('mam_content_updated');
                        } else {
                            console.log('Content not found, redirecting'); // Debug
                            // Si no se encuentra contenido, redirigir
                            window.location.href = url;
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX error:', status, error); // Debug
                        $('.mam-ajax-loader').hide();
                        window.location.href = url; // Redirigir en caso de error
                    }
                });
            });
            
            // Botón atrás del navegador
            $(window).on('popstate', function() {
                console.log('Popstate event detected'); // Debug
                location.reload();
            });
        },
        // Añadir esta nueva función
        initModernized: function() {
            // Función para manejar el toggle del menú en móvil
            function setupMobileMenu() {
                if ($(window).width() <= 768) {
                    $('.mam-sidebar-toggle').show();
                    $('.mam-account-nav').removeClass('is-open');
                } else {
                    $('.mam-sidebar-toggle').hide();
                    $('.mam-account-nav').addClass('is-open');
                }
            }
            
            // Inicializar
            setupMobileMenu();
            
            // Detectar cambio de tamaño de ventana
            $(window).resize(function() {
                setupMobileMenu();
            });
            
            // Toggle de menú en móvil
            $(document).on('click', '.mam-sidebar-toggle', function() {
                $('.mam-account-nav').toggleClass('is-open');
            });
        },
  // Selector más preciso para los enlaces de navegación
    $(document).on('click', '.mam-nav-menu a, .mam-sidebar a, .woocommerce-MyAccount-navigation a', function(e) {
        // No intervenir en enlaces externos o de logout
        if ($(this).attr('href').indexOf('customer-logout') > -1 || 
            $(this).attr('href').indexOf('http') === 0 && 
            $(this).attr('href').indexOf(window.location.hostname) === -1) {
            return true; // Permitir comportamiento por defecto
        }
        
        e.preventDefault();
        
        // Obtener URL del enlace
        var url = $(this).attr('href');
        
        // Actualizar navegación visualmente
        $('.mam-nav-menu li, .woocommerce-MyAccount-navigation li').removeClass('active');
        $(this).closest('li').addClass('active');
        
        // Mostrar indicador de carga
        $('.mam-main-content, .woocommerce-MyAccount-content').append(
            '<div class="mam-loading">' + MAM_Data.i18n.loading + '</div>'
        );
        
        // Cargar contenido vía AJAX
        $.ajax({
            url: url,
            dataType: 'html',
            success: function(response) {
                // Intentar extraer contenido con diferentes selectores
                var $response = $(response);
                var content = $response.find('.mam-main-content').html();
                
                if (!content) {
                    content = $response.find('.woocommerce-MyAccount-content').html();
                }
                
                if (!content) {
                    // Último recurso: intentar encontrar cualquier contenedor principal
                    content = $response.find('.woocommerce-account').html();
                }
                
                // Actualizar contenido
                if (content) {
                    $('.mam-main-content, .woocommerce-MyAccount-content').html(content);
                    
                    // Inicializar funcionalidades específicas en el contenido cargado
                    MAM.initEnhancedFields();
                    
                    // Disparar evento para plugins externos
                    $(document).trigger('mam_content_updated', [url]);
                } else {
                    // Si todo falla, redirigir
                    window.location.href = url;
                    return;
                }
                
                // Actualizar URL sin recargar la página
                if (history.pushState) {
                    history.pushState(null, null, url);
                }
                
                // Scroll arriba
                $('html, body').animate({ scrollTop: 0 }, 300);
            },
            error: function() {
                // En caso de error, redirigir a la página
                window.location.href = url;
            }
        });
    });
    
    // Manejar botón atrás del navegador
    $(window).on('popstate', function() {
        // Cargar contenido para la URL actual después de cambio en el historial
        var currentUrl = window.location.href;
        
        $.ajax({
            url: currentUrl,
            dataType: 'html',
            success: function(response) {
                var $response = $(response);
                var content = $response.find('.mam-main-content').html() || 
                              $response.find('.woocommerce-MyAccount-content').html();
                
                if (content) {
                    $('.mam-main-content, .woocommerce-MyAccount-content').html(content);
                    MAM.initEnhancedFields();
                } else {
                    // Si todo falla, recargar
                    window.location.reload();
                }
            },
            error: function() {
                window.location.reload();
            }
        });
    });
    
    // Manejar envío de formularios dentro del dashboard
    $(document).on('submit', '.mam-main-content form, .woocommerce-MyAccount-content form', function(e) {
        // Excluir formularios específicos
        if ($(this).hasClass('login') || 
            $(this).hasClass('register') || 
            $(this).hasClass('woocommerce-checkout') ||
            $(this).attr('enctype') === 'multipart/form-data') {
            return true; // Comportamiento normal para estos formularios
        }
        
        e.preventDefault();
        
        var $form = $(this);
        var formData = $form.serialize();
        var formUrl = $form.attr('action') || window.location.href;
        var method = $form.attr('method') || 'POST';
        
        // Mostrar indicador de carga
        $('.mam-main-content, .woocommerce-MyAccount-content').append(
            '<div class="mam-loading">' + MAM_Data.i18n.loading + '</div>'
        );
        
        $.ajax({
            url: formUrl,
            type: method,
            data: formData,
            success: function(response) {
                var $response = $(response);
                var content = $response.find('.mam-main-content').html() || 
                              $response.find('.woocommerce-MyAccount-content').html();
                
                if (content) {
                    $('.mam-main-content, .woocommerce-MyAccount-content').html(content);
                    MAM.initEnhancedFields();
                    
                    // Si hay mensaje de éxito, mostrar notificación
                    if ($response.find('.woocommerce-message').length) {
                        var message = $response.find('.woocommerce-message').text();
                        MAM.showNotification(message, 'success');
                    }
                } else {
                    window.location.href = formUrl; // Fallar con gracia
                }
            },
            error: function() {
                MAM.showNotification(MAM_Data.i18n.error, 'error');
                $('.mam-loading').remove();
            }
        });
    });
},

// Añadir esta función para mostrar notificaciones
showNotification: function(message, type) {
    // Remover notificaciones existentes
    $('.mam-notification').remove();
    
    var $notification = $('<div class="mam-notification mam-notification-' + type + '">' + 
                         message + 
                         '<span class="mam-notification-close">&times;</span></div>');
    
    $('body').append($notification);
    
    setTimeout(function() {
        $notification.addClass('mam-show');
    }, 10);
    
    // Auto-ocultar después de 5 segundos
    setTimeout(function() {
        $notification.removeClass('mam-show');
        setTimeout(function() {
            $notification.remove();
        }, 300);
    }, 5000);
    
    // Permitir cerrar manualmente
    $(document).on('click', '.mam-notification-close', function() {
        $(this).parent().removeClass('mam-show');
        setTimeout(function() {
            $('.mam-notification').remove();
        }, 300);
    });
}
        /**
         * Inicializar validación de formularios
         */
        initFormValidation: function() {
            // Validar inputs con atributo data-validate
            $(document).on('blur', '[data-validate]', function() {
                var $field = $(this);
                var value = $field.val();
                var validationType = $field.data('validate');
                
                // Eliminar clase de error previa
                $field.closest('.form-row').removeClass('woocommerce-invalid woocommerce-validated');
                
                // Validar según el tipo
                if (value) {
                    var isValid = MAM.validateField(value, validationType);
                    if (isValid) {
                        $field.closest('.form-row').addClass('woocommerce-validated');
                    } else {
                        $field.closest('.form-row').addClass('woocommerce-invalid');
                    }
                } else if ($field.prop('required')) {
                    $field.closest('.form-row').addClass('woocommerce-invalid');
                }
            });
            
            // Validación de formularios al enviar
            $('form.woocommerce-form, form.woocommerce-checkout, form.woocommerce-EditAccountForm').on('submit', function() {
                var $form = $(this);
                var isValid = true;
                
                // Validar todos los campos con data-validate
                $form.find('[data-validate]').each(function() {
                    var $field = $(this);
                    var value = $field.val();
                    var validationType = $field.data('validate');
                    
                    // Si es requerido y está vacío
                    if ($field.prop('required') && !value) {
                        $field.closest('.form-row').addClass('woocommerce-invalid');
                        isValid = false;
                        return;
                    }
                    
                    // Si tiene valor, validar según el tipo
                    if (value) {
                        var fieldIsValid = MAM.validateField(value, validationType);
                        if (!fieldIsValid) {
                            $field.closest('.form-row').addClass('woocommerce-invalid');
                            isValid = false;
                        } else {
                            $field.closest('.form-row').removeClass('woocommerce-invalid').addClass('woocommerce-validated');
                        }
                    }
                });
                
                return isValid;
            });
        },
        
           
        /**
         * Inicializar campos mejorados
         */
        initEnhancedFields: function() {
            // Mejorar campos de fecha con datepicker si está disponible
            if ($.fn.datepicker && $('input[type="date"]').length) {
                $('input[type="date"]').each(function() {
                    var $dateField = $(this);
                    
                    // Solo si no es un campo nativo de tipo date (para navegadores antiguos)
                    if ($dateField[0].type !== 'date') {
                        $dateField.datepicker({
                            dateFormat: 'yy-mm-dd',
                            changeMonth: true,
                            changeYear: true,
                            yearRange: '1900:' + (new Date().getFullYear() + 10)
                        });
                    }
                });
            }
            
            // Mejorar campo de CUIT con máscara si está disponible
            if ($.fn.mask && $('[data-validate="cuit"]').length) {
                $('[data-validate="cuit"]').mask('99-99999999-9', {
                    placeholder: '__-________-_'
                });
            }
            
            // Mejorar campo de teléfono con máscara si está disponible
            if ($.fn.mask && $('[data-validate="phone"]').length) {
                $('[data-validate="phone"]').mask('(999) 9999-9999', {
                    placeholder: '(___) ____-____'
                });
            }
        },
        
        /**
         * Inicializar menú móvil
         */
        initMobileMenu: function() {
            // Si estamos en dispositivo móvil
            if ($(window).width() <= 768) {
                // Añadir botón de toggle para el menú
                var $nav = $('.woocommerce-MyAccount-navigation');
                if ($nav.length && !$('.mam-mobile-toggle').length) {
                    $nav.before('<button class="mam-mobile-toggle">' + MAM_Data.i18n.menu + '</button>');
                    
                    // Ocultar menú inicialmente
                    $nav.hide();
                    
                    // Toggle del menú al hacer click
                    $(document).on('click', '.mam-mobile-toggle', function() {
                        $nav.slideToggle();
                    });
                }
            }
        },
        
        /**
         * Validar campo según el tipo
         */
        validateField: function(value, type) {
            switch (type) {
                case 'cuit':
                    return MAM.validateCUIT(value);
                    
                case 'phone':
                    return MAM.validatePhone(value);
                    
                case 'date':
                    return MAM.validateDate(value);
                    
                case 'email':
                    return MAM.validateEmail(value);
                    
                case 'number':
                    return MAM.validateNumber(value);
                    
                case 'text':
                default:
                    return value.trim() !== '';
            }
        },
        
        /**
         * Validar CUIT
         */
        validateCUIT: function(cuit) {
            // Eliminar guiones y espacios
            cuit = cuit.replace(/[^0-9]/g, '');
            
            // Verificar longitud
            if (cuit.length !== 11) {
                return false;
            }
            
            // Algoritmo de validación de CUIT argentino
            var acumulado = 0;
            var digitos = cuit.split('');
            var digito = parseInt(digitos.pop());
            
            for (var i = 0; i < digitos.length; i++) {
                acumulado += parseInt(digitos[9 - i]) * (2 + (i % 6));
            }
            
            var verif = 11 - (acumulado % 11);
            if (verif === 11) {
                verif = 0;
            }
            
            return digito === verif;
        },
        
        /**
         * Validar teléfono
         */
        validatePhone: function(phone) {
            // Eliminar todo excepto números
            phone = phone.replace(/[^0-9]/g, '');
            
            // Verificar longitud mínima
            return phone.length >= 6;
        },
        
        /**
         * Validar fecha
         */
        validateDate: function(date) {
            // Verificar si es una fecha válida
            var d = new Date(date);
            return !isNaN(d.getTime());
        },
        
        /**
         * Validar email
         */
        validateEmail: function(email) {
            var re = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            return re.test(email);
        },
        
        /**
         * Validar número
         */
        validateNumber: function(number) {
            return !isNaN(parseFloat(number)) && isFinite(number);
        },
        
    };
    
    // Inicializar cuando el DOM esté listo
  $(document).ready(function() {
        console.log('MAM Frontend initialized'); // Debug
        MAM.init();
    });
    
})(jQuery);
