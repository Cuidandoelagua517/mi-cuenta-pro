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
    initDashboardNavigation: function() {
        // Añadir esto dentro de initDashboardNavigation
// Manejar envío de formularios dentro del dashboard
$(document).on('submit', '.mam-main-content form:not(.login)', function(e) {
    e.preventDefault();
    
    var $form = $(this);
    var formData = $form.serialize();
    var formUrl = $form.attr('action');
    
    // Mostrar indicador de carga
    $('.mam-main-content').append('<div class="mam-loading">' + MAM_Data.i18n.loading + '</div>');
    
    $.ajax({
        url: formUrl,
        type: 'POST',
        data: formData,
        success: function(response) {
            // Actualizar contenido con la respuesta
            var content = $(response).find('.mam-main-content').html();
            if (!content) {
                content = $(response).find('.woocommerce-MyAccount-content').html();
            }
            $('.mam-main-content').html(content);
        },
        error: function() {
            // Mostrar mensaje de error
            $('.mam-main-content').append('<div class="mam-error">' + MAM_Data.i18n.error + '</div>');
        }
    });
});
    // Interceptar clics en los enlaces de navegación del dashboard
    $(document).on('click', '.mam-nav-menu a', function(e) {
        e.preventDefault();
        
        // Obtener URL del enlace
        var url = $(this).attr('href');
        
        // Actualizar el menú (marcar elemento activo)
        $('.mam-nav-menu li').removeClass('active');
        $(this).closest('li').addClass('active');
        
        // Mostrar indicador de carga
        $('.mam-main-content').append('<div class="mam-loading">' + MAM_Data.i18n.loading + '</div>');
        
        // Cargar contenido vía AJAX
        $.ajax({
            url: url,
            dataType: 'html',
            success: function(response) {
                // Extraer solo el contenido principal
                var content = $(response).find('.mam-main-content').html();
                
                // Si no se encuentra el contenido, intentar con otro selector
                if (!content) {
                    content = $(response).find('.woocommerce-MyAccount-content').html();
                }
                
                // Actualizar contenido
                $('.mam-main-content').html(content);
                
                // Actualizar URL sin recargar la página
                history.pushState(null, null, url);
            },
            error: function() {
                // Mostrar mensaje de error
                $('.mam-main-content').html('<div class="mam-error">' + MAM_Data.i18n.error + '</div>');
            }
        });
    });
    
    // Gestionar el botón atrás del navegador
    $(window).on('popstate', function() {
        location.reload(); // Opción simple: recargar la página cuando se usa el botón atrás
    });
    
    // Excluir ciertos enlaces que deben cargar la página completa
    $(document).on('click', '.mam-nav-menu a[href*="customer-logout"]', function(e) {
        // No prevenir el comportamiento por defecto para enlaces de cierre de sesión
        return true;
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
    $('.woocommerce-MyAccount-navigation-link--downloads a').on('click', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: $(this).attr('href'),
            dataType: 'html',
            success: function(response) {
                $('.woocommerce-MyAccount-content').html(
                    $(response).find('.woocommerce-MyAccount-content').html()
                );
            }
        });
    });
});
    
})(jQuery);
