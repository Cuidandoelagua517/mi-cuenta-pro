<?php
/**
 * Clase para manejar el registro y autenticación personalizado
 *
 * @package Mi_Cuenta_Mejorado
 */

if (!defined('WPINC')) {
    die;
}

/**
 * Clase para gestionar el registro y autenticación
 */
class MAM_Registration {
    
    /**
     * Instancia de MAM_Fields
     *
     * @var MAM_Fields
     */
    private $fields;
    
    /**
     * Constructor
     *
     * @param MAM_Fields $fields Instancia de la clase de campos
     */
    public function __construct($fields) {
        $this->fields = $fields;
        
        // Hooks para personalizar el registro y el login
        add_action('woocommerce_register_form', array($this, 'add_custom_registration_fields'));
        add_filter('woocommerce_registration_errors', array($this, 'validate_custom_registration_fields'), 10, 3);
        add_action('woocommerce_created_customer', array($this, 'save_custom_registration_fields'));
        
        // Personalizar mensajes de error
        add_filter('woocommerce_add_error', array($this, 'customize_error_messages'));
        
        // Personalizar redirección después de login
        add_filter('woocommerce_login_redirect', array($this, 'custom_login_redirect'), 10, 2);
    }
    
    /**
     * Añadir campos personalizados al formulario de registro
     */
public function add_custom_registration_fields() {
    $active_fields = $this->fields->get_active_fields();
    
    // Filtrar los campos para el formulario de registro
    $registration_fields = array_filter($active_fields, function($field) {
        return in_array($field['section'], array('account', 'billing'));
    });
    
    // Renderizar campos
    foreach ($registration_fields as $field_id => $field) {
        woocommerce_form_field($field_id, [
            'type' => $field['type'],
            'label' => $field['label'],
            'required' => $field['required'],
            'class' => $field['class'],
            'custom_attributes' => [
                'data-validate' => $field['validation']
            ]
        ]);
    }
}
    
    /**
     * Validar campos personalizados en el registro
     *
     * @param WP_Error $errors Objeto de errores
     * @param string $username Nombre de usuario
     * @param string $email Email del usuario
     * @return WP_Error Objeto de errores actualizado
     */
    public function validate_custom_registration_fields($errors, $username, $email) {
        $active_fields = $this->fields->get_active_fields();
        
        foreach ($active_fields as $field_id => $field) {
            // Solo validar campos relevantes para el registro
            if (!in_array($field['section'], array('account', 'billing'))) {
                continue;
            }
            
            // Validar campos obligatorios
            if ($field['required'] && empty($_POST[$field_id])) {
                $errors->add($field_id . '_error', sprintf(__('El campo %s es obligatorio.', 'my-account-enhanced'), '<strong>' . $field['label'] . '</strong>'));
            } 
            // Validar formato según el tipo de validación
            elseif (!empty($_POST[$field_id])) {
                $is_valid = $this->fields->validate_field($_POST[$field_id], $field['validation']);
                
                if (!$is_valid) {
                    $errors->add($field_id . '_error', sprintf(__('El formato del campo %s no es válido.', 'my-account-enhanced'), '<strong>' . $field['label'] . '</strong>'));
                }
            }
        }
        
        return $errors;
    }
    
    /**
     * Guardar campos personalizados después de crear el usuario
     *
     * @param int $customer_id ID del usuario creado
     */
    public function save_custom_registration_fields($customer_id) {
        $active_fields = $this->fields->get_active_fields();
        
        foreach ($active_fields as $field_id => $field) {
            // Solo guardar campos relevantes para el registro
            if (!in_array($field['section'], array('account', 'billing'))) {
                continue;
            }
            
            if (isset($_POST[$field_id])) {
                // Sanitizar y guardar el valor
                $value = $this->fields->sanitize_field_value($_POST[$field_id], $field['type']);
                update_user_meta($customer_id, $field_id, $value);
            }
        }
    }
    
    /**
     * Personalizar mensajes de error
     *
     * @param string $error Mensaje de error original
     * @return string Mensaje de error personalizado
     */
    public function customize_error_messages($error) {
        // Personalizar mensajes de error comunes
        $custom_errors = array(
            'Error: An account is already registered with your email address.' => 
            __('Ya existe una cuenta registrada con esta dirección de correo electrónico. Por favor, inicia sesión.', 'my-account-enhanced'),
            
            'Error: Please enter an account password.' => 
            __('Por favor, ingresa una contraseña para tu cuenta.', 'my-account-enhanced'),
            
            'Error: Please provide a valid email address.' => 
            __('Por favor, proporciona una dirección de correo electrónico válida.', 'my-account-enhanced')
        );
        
        // Reemplazar el mensaje si existe en nuestro array de personalizaciones
        if (isset($custom_errors[$error])) {
            return $custom_errors[$error];
        }
        
        return $error;
    }
    
    /**
     * Personalizar la redirección después de iniciar sesión
     *
     * @param string $redirect URL de redirección
     * @param WP_User $user Usuario que ha iniciado sesión
     * @return string URL de redirección personalizada
     */
    public function custom_login_redirect($redirect, $user) {
        // Obtener configuración de redirección
        $settings = get_option('mam_login_settings', array());
        
        // Si hay una URL de redirección personalizada, usarla
        if (!empty($settings['redirect_url'])) {
            return $settings['redirect_url'];
        }
        
        // Si no, mantener la redirección original
        return $redirect;
    }
}
