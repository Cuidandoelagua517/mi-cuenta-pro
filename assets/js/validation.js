jQuery(document).ready(function($) {
    // Validación básica del formulario
    $('.woocommerce-form-register').on('submit', function(e) {
        var isValid = true;
        
        // Validar email
        var emailField = $('#reg_email');
        if (emailField.length && !validateEmail(emailField.val())) {
            showFieldError(emailField, 'Por favor ingresa un email válido');
            isValid = false;
        }
        
        // Validar empresa (campo obligatorio)
        var companyField = $('#billing_company');
        if (companyField.length && companyField.val().trim() === '') {
            showFieldError(companyField, 'El nombre de empresa es obligatorio');
            isValid = false;
        }
        
        // Validar CUIT (campo obligatorio)
        var cuitField = $('#billing_cuit');
        if (cuitField.length) {
            var cuit = cuitField.val().replace(/[^0-9]/g, '');
            if (cuit === '') {
                showFieldError(cuitField, 'El CUIT es obligatorio');
                isValid = false;
            } else if (cuit.length !== 11) {
                showFieldError(cuitField, 'El CUIT debe tener 11 dígitos');
                isValid = false;
            }
        }
        
        // Validar política de privacidad
        var privacyField = $('#privacy_policy');
        if (privacyField.length && !privacyField.is(':checked')) {
            showFieldError(privacyField, 'Debes aceptar la política de privacidad');
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });
    
    // Función para validar email
    function validateEmail(email) {
        var re = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        return re.test(email);
    }
    
    // Función para mostrar errores
    function showFieldError(field, message) {
        // Eliminar error previo
        removeFieldError(field);
        
        // Crear y mostrar mensaje de error
        var errorElement = $('<div class="mam-field-error">' + message + '</div>');
        field.after(errorElement);
        
        // Destacar campo con error
        field.addClass('input-error');
    }
    
    // Función para eliminar errores
    function removeFieldError(field) {
        field.removeClass('input-error');
        field.next('.mam-field-error').remove();
    }
    
    // Limpiar errores al cambiar los campos
    $('.woocommerce-form-register input, .woocommerce-form-register select').on('change', function() {
        removeFieldError($(this));
    });
    
    // Formato para el CUIT
    $('#billing_cuit').on('input', function() {
        var value = $(this).val().replace(/[^0-9]/g, '');
        var formattedValue = '';
        
        if (value.length > 0) {
            // Dar formato XX-XXXXXXXX-X
            if (value.length <= 2) {
                formattedValue = value;
            } else if (value.length <= 10) {
                formattedValue = value.substr(0, 2) + '-' + value.substr(2);
            } else {
                formattedValue = value.substr(0, 2) + '-' + value.substr(2, 8) + '-' + value.substr(10, 1);
            }
            
            $(this).val(formattedValue);
        }
    });
});
