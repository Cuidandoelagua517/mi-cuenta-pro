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
    // Interceptar clics en los enlaces de navegación del dashboard
    $(document).on('click', '.woocommerce-MyAccount-navigation-link a', function(e) {
        e.preventDefault();
        
        // Obtener URL del enlace
        var url = $(this).attr('href');
        
        // Mostrar indicador de carga
        $('.woocommerce-MyAccount-content').append('<div class="mam-loading">' + MAM_Data.i18n.loading + '</div>');
        
        // Cargar contenido vía AJAX
        $.ajax({
            url: url,
            dataType: 'html',
            success: function(response) {
                // Extraer solo el contenido de .woocommerce-MyAccount-content
                var content = $(response).find('.woocommerce-MyAccount-content').html();
                
                // Actualizar contenido
                $('.woocommerce-MyAccount-content').html(content);
                
                // Actualizar clase activa en la navegación
                $('.woocommerce-MyAccount-navigation-link').removeClass('is-active');
                var endpoint = url.split('/').filter(Boolean).pop();
                $('.woocommerce-MyAccount-navigation-link--' + endpoint).addClass('is-active');
                
                // Actualizar URL sin recargar la página
                history.pushState(null, null, url);
            },
            error: function() {
                // Mostrar mensaje de error
                $('.woocommerce-MyAccount-content').html('<p class="mam-error">' + MAM_Data.i18n.error + '</p>');
            }
        });
    });
    
    // Gestionar el botón atrás del navegador
    $(window).on('popstate', function() {
        location.reload();
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
        MAM.init();
    });
    
})(jQuery);
