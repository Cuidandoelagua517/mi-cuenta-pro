<?php
/**
 * Clase para manejar las configuraciones del plugin (versión simplificada)
 *
 * @package Mi_Cuenta_Mejorado
 */

if (!defined('WPINC')) {
    die;
}

/**
 * Clase para gestionar configuraciones
 */
class MAM_Settings {
    
    /**
     * Constructor
     */
    public function __construct() {

    
    /**
     * Registrar configuraciones del plugin
     */
    public function register_settings() {
        register_setting('mam_options_group', 'mam_field_settings');
        register_setting('mam_options_group', 'mam_section_settings');
        register_setting('mam_options_group', 'mam_appearance_settings');
        register_setting('mam_options_group', 'mam_login_settings');
    }
    
    /**
     * Obtener configuración específica
     *
     * @param string $setting_type Tipo de configuración
     * @return array Configuración solicitada
     */
    public function get_settings($setting_type) {
        switch ($setting_type) {
            case 'fields':
                return get_option('mam_field_settings', array());
                
            case 'sections':
                return get_option('mam_section_settings', array());
                
            case 'brands':
                return get_option('mam_brands_settings', array());
                
            case 'appearance':
                return get_option('mam_appearance_settings', array());
                
            case 'login':
                return get_option('mam_login_settings', array());
                
            default:
                return array();
        }
    }
    
    /**
     * Obtener opciones predeterminadas de campos
     */
    public function get_default_field_settings() {
        return array(
            'billing_company' => array(
                'enabled' => true,
                'required' => true,
                'position' => 10
            ),
            'billing_cuit' => array(
                'enabled' => true,
                'required' => true,
                'position' => 20
            ),
            'billing_first_name' => array(
                'enabled' => true,
                'required' => true,
                'position' => 30
            ),
            'billing_last_name' => array(
                'enabled' => true,
                'required' => true,
                'position' => 40
            ),
            'billing_phone' => array(
                'enabled' => true,
                'required' => true,
                'position' => 50
            ),
            'customer_birthday' => array(
                'enabled' => true,
                'required' => false,
                'position' => 60
            )
        );
    }
}
