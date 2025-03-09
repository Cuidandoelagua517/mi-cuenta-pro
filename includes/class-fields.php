<?php
/**
 * Clase para manejar la definición y gestión de campos personalizados
 *
 * @package Mi_Cuenta_Mejorado
 */

if (!defined('WPINC')) {
    die;
}

/**
 * Clase para gestionar campos personalizados
 */
class MAM_Fields {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Inicializar hooks
        add_filter('woocommerce_checkout_fields', array($this, 'add_custom_checkout_fields'));
        add_filter('woocommerce_billing_fields', array($this, 'add_custom_billing_fields'));
        add_action('woocommerce_checkout_update_order_meta', array($this, 'save_custom_checkout_fields'));
        add_filter('woocommerce_customer_meta_fields', array($this, 'add_customer_meta_fields'));
    }
    
    /**
     * Obtener todos los campos disponibles
     */
    public function get_all_fields() {
        $default_fields = $this->get_default_fields();
        $custom_fields = $this->get_custom_fields();
        
        return array_merge($default_fields, $custom_fields);
    }
    
    /**
     * Obtener campos predeterminados
     */
    public function get_default_fields() {
        return [
            'billing_company' => [
                'label' => __('Nombre de empresa', 'my-account-enhanced'),
                'type' => 'text',
                'required' => true,
                'enabled' => true,
                'position' => 10,
                'validation' => 'text',
                'section' => 'billing',
                'class' => ['form-row-wide'],
                'priority' => 10
            ],
            'billing_cuit' => [
                'label' => __('CUIT', 'my-account-enhanced'),
                'type' => 'text',
                'required' => true,
                'enabled' => true,
                'position' => 20,
                'validation' => 'cuit',
                'section' => 'billing',
                'class' => ['form-row-wide'],
                'priority' => 20
            ],
            'billing_first_name' => [
                'label' => __('Nombre', 'my-account-enhanced'),
                'type' => 'text',
                'required' => true,
                'enabled' => true,
                'position' => 30,
                'validation' => 'text',
                'section' => 'billing',
                'class' => ['form-row-first'],
                'priority' => 30
            ],
            'billing_last_name' => [
                'label' => __('Apellido', 'my-account-enhanced'),
                'type' => 'text',
                'required' => true,
                'enabled' => true,
                'position' => 40,
                'validation' => 'text',
                'section' => 'billing',
                'class' => ['form-row-last'],
                'priority' => 40
            ],
            'billing_phone' => [
                'label' => __('Teléfono', 'my-account-enhanced'),
                'type' => 'tel',
                'required' => true,
                'enabled' => true,
                'position' => 50,
                'validation' => 'phone',
                'section' => 'billing',
                'class' => ['form-row-wide'],
                'priority' => 50
            ],
            'customer_birthday' => [
                'label' => __('Fecha de cumpleaños', 'my-account-enhanced'),
                'type' => 'date',
                'required' => false,
                'enabled' => true,
                'position' => 60,
                'validation' => 'date',
                'section' => 'account',
                'class' => ['form-row-wide'],
                'priority' => 60
            ],
            // Más campos predeterminados según sea necesario...
        ];
    }
    
    /**
     * Obtener campos personalizados
     */
    public function get_custom_fields() {
        $custom_fields = get_option('mam_custom_fields', []);
        return is_array($custom_fields) ? $custom_fields : [];
    }
    
    /**
     * Obtener campos activos
     */
    public function get_active_fields() {
        $all_fields = $this->get_all_fields();
        $field_settings = get_option('mam_field_settings', []);
        
        foreach ($all_fields as $field_id => &$field) {
            // Aplicar configuración guardada si existe
            if (isset($field_settings[$field_id])) {
                $field['enabled'] = $field_settings[$field_id]['enabled'];
                $field['required'] = $field_settings[$field_id]['required'];
                $field['position'] = $field_settings[$field_id]['position'];
            }
            
            // Eliminar campos desactivados
            if (!$field['enabled']) {
                unset($all_fields[$field_id]);
            }
        }
        
        // Ordenar por posición
        uasort($all_fields, function($a, $b) {
            return $a['position'] - $b['position'];
        });
        
        return $all_fields;
    }
    
    /**
     * Añadir campos personalizados al checkout
     */
    public function add_custom_checkout_fields($fields) {
        $active_fields = $this->get_active_fields();
        
        // Agregar solo los campos que pertenecen a la sección de facturación
        foreach ($active_fields as $field_id => $field) {
            if ($field['section'] === 'billing') {
                $fields['billing'][$field_id] = array(
                    'type' => $field['type'],
                    'label' => $field['label'],
                    'required' => $field['required'],
                    'class' => $field['class'],
                    'priority' => $field['position'],
                    'custom_attributes' => array(
                        'data-validate' => $field['validation']
                    )
                );
            }
        }
        
        return $fields;
    }
    
    /**
     * Añadir campos personalizados a la facturación
     */
    public function add_custom_billing_fields($fields) {
        $active_fields = $this->get_active_fields();
        
        // Agregar campos personalizados de facturación
        foreach ($active_fields as $field_id => $field) {
            if ($field['section'] === 'billing' && !isset($fields[$field_id])) {
                $fields[$field_id] = array(
                    'type' => $field['type'],
                    'label' => $field['label'],
                    'required' => $field['required'],
                    'class' => $field['class'],
                    'priority' => $field['position'],
                    'custom_attributes' => array(
                        'data-validate' => $field['validation']
                    )
                );
            }
        }
        
        return $fields;
    }
    
    /**
     * Guardar campos personalizados del checkout
     */
    public function save_custom_checkout_fields($order_id) {
        $active_fields = $this->get_active_fields();
        
        foreach ($active_fields as $field_id => $field) {
            if (isset($_POST[$field_id])) {
                // Validar el campo según su tipo de validación
                $valid = $this->validate_field($_POST[$field_id], $field['validation']);
                
                if ($valid) {
                    // Sanitizar y guardar el valor
                    $value = $this->sanitize_field_value($_POST[$field_id], $field['type']);
                    update_post_meta($order_id, '_' . $field_id, $value);
                    
                    // Si es un campo de usuario, también actualizarlo en los metadatos del usuario
                    if ($field['section'] === 'account' || $field['section'] === 'billing') {
                        $user_id = get_current_user_id();
                        if ($user_id > 0) {
                            update_user_meta($user_id, $field_id, $value);
                        }
                    }
                }
            }
        }
    }
    
    /**
     * Añadir campos personalizados a los metadatos del cliente
     */
    public function add_customer_meta_fields($fields) {
        $active_fields = $this->get_active_fields();
        
        // Agrupar campos por sección
        $account_fields = array();
        $billing_fields = array();
        
        foreach ($active_fields as $field_id => $field) {
            $field_data = array(
                'label' => $field['label'],
                'description' => '',
                'type' => $field['type'],
                'class' => '',
                'required' => $field['required'],
            );
            
            if ($field['section'] === 'account') {
                $account_fields[$field_id] = $field_data;
            } elseif ($field['section'] === 'billing' && !isset($fields['billing']['fields'][$field_id])) {
                $billing_fields[$field_id] = $field_data;
            }
        }
        
        // Añadir campos a las secciones correspondientes
        if (!empty($account_fields)) {
            $fields['account']['fields'] = array_merge($fields['account']['fields'], $account_fields);
        }
        
        if (!empty($billing_fields)) {
            $fields['billing']['fields'] = array_merge($fields['billing']['fields'], $billing_fields);
        }
        
        return $fields;
    }
    
    /**
     * Validar un campo según su tipo de validación
     */
    public function validate_field($value, $validation_type) {
        switch ($validation_type) {
            case 'cuit':
                return $this->validate_cuit($value);
            case 'phone':
                return $this->validate_phone($value);
            case 'date':
                return $this->validate_date($value);
            case 'email':
                return is_email($value);
            case 'text':
            default:
                return !empty($value);
        }
    }
    
    /**
     * Validar formato de CUIT argentino
     */
    public function validate_cuit($cuit) {
        // Eliminar guiones y espacios
        $cuit = preg_replace('/[^0-9]/', '', $cuit);
        
        // Verificar longitud
        if (strlen($cuit) != 11) {
            return false;
        }
        
        // Algoritmo de validación de CUIT argentino
        $acumulado = 0;
        $digitos = str_split($cuit);
        $digito_verificador = array_pop($digitos);
        
        for ($i = 0; $i < count($digitos); $i++) {
            $acumulado += $digitos[9 - $i] * (2 + ($i % 6));
        }
        
        $verif = 11 - ($acumulado % 11);
        if ($verif == 11) {
            $verif = 0;
        }
        
        return $digito_verificador == $verif;
    }
    
    /**
     * Validar formato de teléfono
     */
    public function validate_phone($phone) {
        // Simplemente verificamos que tenga al menos 6 dígitos
        $phone = preg_replace('/[^0-9]/', '', $phone);
        return strlen($phone) >= 6;
    }
    
    /**
     * Validar formato de fecha
     */
    public function validate_date($date) {
        $timestamp = strtotime($date);
        return $timestamp !== false && $timestamp > 0;
    }
    
    /**
     * Sanitizar valor del campo según su tipo
     */
    public function sanitize_field_value($value, $type) {
        switch ($type) {
            case 'email':
                return sanitize_email($value);
            case 'number':
                return intval($value);
            case 'tel':
                return preg_replace('/[^0-9+\-\s()]/', '', $value);
            case 'date':
                return date('Y-m-d', strtotime($value));
            case 'text':
            default:
                return sanitize_text_field($value);
        }
    }
    
    /**
     * Renderizar campos para el formulario de registro
     */
    public function render_registration_fields() {
        $active_fields = $this->get_active_fields();
        
        foreach ($active_fields as $field_id => $field) {
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
}
