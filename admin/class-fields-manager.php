<?php
/**
 * Clase para manejar la gestión de campos desde el admin
 *
 * @package Mi_Cuenta_Mejorado
 */

if (!defined('WPINC')) {
    die;
}

/**
 * Clase para gestionar campos desde el admin
 */
class MAM_Field_Manager {
    
    /**
     * Constructor
     */
    public function __construct() {
        // AJAX para gestionar campos
        add_action('wp_ajax_mam_add_custom_field', array($this, 'ajax_add_custom_field'));
        add_action('wp_ajax_mam_delete_custom_field', array($this, 'ajax_delete_custom_field'));
        add_action('wp_ajax_mam_get_field_options', array($this, 'ajax_get_field_options'));
    }
    
    /**
     * Obtener todos los campos disponibles
     */
    public function get_all_fields() {
        $fields = new MAM_Fields();
        return $fields->get_all_fields();
    }
    
    /**
     * Obtener campos activos
     */
    public function get_active_fields() {
        $fields = new MAM_Fields();
        return $fields->get_active_fields();
    }
    
    /**
     * AJAX para añadir campo personalizado
     */
    public function ajax_add_custom_field() {
        // Verificar nonce y permisos
        check_ajax_referer('mam-admin-nonce', 'security');
        
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => __('No tienes permisos para realizar esta acción.', 'my-account-enhanced')));
        }
        
        // Procesar datos del formulario
        parse_str($_POST['formData'], $form_data);
        
        if (empty($form_data['label'])) {
            wp_send_json_error(array('message' => __('La etiqueta del campo es obligatoria.', 'my-account-enhanced')));
        }
        
        // Crear identificador único para el campo
        $field_id = 'mam_custom_' . sanitize_key($form_data['label']) . '_' . time();
        
        // Configurar el nuevo campo
        $new_field = array(
            'label' => sanitize_text_field($form_data['label']),
            'type' => sanitize_key($form_data['type']),
            'required' => isset($form_data['required']) ? (bool) $form_data['required'] : false,
            'enabled' => true,
            'position' => 100, // Posición por defecto
            'validation' => sanitize_key($form_data['validation']),
            'section' => sanitize_key($form_data['section']),
            'class' => array('form-row-wide')
        );
        
        // Obtener campos personalizados existentes
        $custom_fields = get_option('mam_custom_fields', array());
        
        // Añadir el nuevo campo
        $custom_fields[$field_id] = $new_field;
        
        // Guardar campos personalizados
        update_option('mam_custom_fields', $custom_fields);
        
        // También añadir a la configuración de campos
        $field_settings = get_option('mam_field_settings', array());
        $field_settings[$field_id] = array(
            'enabled' => true,
            'required' => $new_field['required'],
            'position' => $new_field['position']
        );
        update_option('mam_field_settings', $field_settings);
        
        wp_send_json_success(array('message' => __('Campo personalizado añadido correctamente.', 'my-account-enhanced')));
    }
    
    /**
     * AJAX para eliminar campo personalizado
     */
    public function ajax_delete_custom_field() {
        // Verificar nonce y permisos
        check_ajax_referer('mam-admin-nonce', 'security');
        
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => __('No tienes permisos para realizar esta acción.', 'my-account-enhanced')));
        }
        
        $field_id = isset($_POST['field_id']) ? sanitize_key($_POST['field_id']) : '';
        
        if (empty($field_id) || strpos($field_id, 'mam_custom_') !== 0) {
            wp_send_json_error(array('message' => __('Identificador de campo inválido.', 'my-account-enhanced')));
        }
        
        // Obtener campos personalizados
        $custom_fields = get_option('mam_custom_fields', array());
        
        // Verificar si el campo existe
        if (!isset($custom_fields[$field_id])) {
            wp_send_json_error(array('message' => __('El campo personalizado no existe.', 'my-account-enhanced')));
        }
        
        // Eliminar el campo
        unset($custom_fields[$field_id]);
        update_option('mam_custom_fields', $custom_fields);
        
        // También eliminar de la configuración de campos
        $field_settings = get_option('mam_field_settings', array());
        if (isset($field_settings[$field_id])) {
            unset($field_settings[$field_id]);
            update_option('mam_field_settings', $field_settings);
        }
        
        wp_send_json_success(array('message' => __('Campo personalizado eliminado correctamente.', 'my-account-enhanced')));
    }
    
    /**
     * AJAX para gestionar el orden de los campos
     */
    public function ajax_save_fields_order() {
        // Verificar nonce y permisos
        check_ajax_referer('mam-admin-nonce', 'security');
        
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => __('No tienes permisos para realizar esta acción.', 'my-account-enhanced')));
        }
        
        $fields_order = isset($_POST['fields_order']) ? $_POST['fields_order'] : array();
        
        if (empty($fields_order)) {
            wp_send_json_error(array('message' => __('No se recibió información de orden.', 'my-account-enhanced')));
        }
        
        // Obtener configuración de campos
        $field_settings = get_option('mam_field_settings', array());
        
        // Actualizar posiciones
        foreach ($fields_order as $position => $field_id) {
            $field_id = sanitize_key($field_id);
            
            if (isset($field_settings[$field_id])) {
                $field_settings[$field_id]['position'] = intval($position) * 10;
            }
        }
        
        // Guardar configuración actualizada
        update_option('mam_field_settings', $field_settings);
        
        wp_send_json_success(array('message' => __('Orden de campos guardado correctamente.', 'my-account-enhanced')));
    }
    
    /**
     * AJAX para obtener opciones de un campo
     */
    public function ajax_get_field_options() {
        // Verificar nonce y permisos
        check_ajax_referer('mam-admin-nonce', 'security');
        
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => __('No tienes permisos para realizar esta acción.', 'my-account-enhanced')));
        }
        
        $field_id = isset($_POST['field_id']) ? sanitize_key($_POST['field_id']) : '';
        
        if (empty($field_id)) {
            wp_send_json_error(array('message' => __('Identificador de campo inválido.', 'my-account-enhanced')));
        }
        
        $fields = new MAM_Fields();
        $all_fields = $fields->get_all_fields();
        
        if (isset($all_fields[$field_id])) {
            $field = $all_fields[$field_id];
            
            // Si es un selector y tiene opciones
            if ($field['type'] === 'select' && isset($field['options'])) {
                wp_send_json_success(array(
                    'field' => $field,
                    'options' => $field['options']
                ));
            } else {
                wp_send_json_success(array(
                    'field' => $field,
                    'options' => array()
                ));
            }
        } else {
            wp_send_json_error(array('message' => __('Campo no encontrado.', 'my-account-enhanced')));
        }
    }
}
