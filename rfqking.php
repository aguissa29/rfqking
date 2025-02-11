<?php
/**
 * Plugin Name: RFQKing
 * Description: Un sistema de Cotización Abierta (RFQ) para WooCommerce inspirado en Global Sources.
 * Version: 1.0.0
 * Author: Tu Nombre
 * License: GPL-2.0+
 */

if (!defined('ABSPATH')) {
    exit; // Salir si se accede directamente
}

// Definir constantes
define('RFQKING_VERSION', '1.0.0');
define('RFQKING_PATH', plugin_dir_path(__FILE__));
define('RFQKING_URL', plugin_dir_url(__FILE__));

// Cargar archivos necesarios
require_once RFQKING_PATH . 'includes/class-rfqking.php';
require_once RFQKING_PATH . 'includes/class-rfq-form-handler.php';
require_once RFQKING_PATH . 'includes/class-rfq-notifications.php';
require_once RFQKING_PATH . 'includes/class-rfq-database.php';
require_once RFQKING_PATH . 'includes/class-rfq-export.php';

// Inicializar el plugin
function rfqking_init() {
    $rfqking = new RFQKing();
    $rfqking->run();
}
add_action('plugins_loaded', 'rfqking_init');

// Registrar endpoint personalizado
function rfqking_add_endpoint() {
    add_rewrite_endpoint('rfq', EP_ROOT | EP_PAGES);
}
add_action('init', 'rfqking_add_endpoint');

// Flushear reglas de reescritura al activar el plugin
function rfqking_flush_rewrite_rules() {
    rfqking_add_endpoint();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'rfqking_flush_rewrite_rules');

// Renderizar la página de configuración
function rfqking_render_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_POST['save_rfqking_units'])) {
        if (!isset($_POST['rfqking_nonce']) || !wp_verify_nonce($_POST['rfqking_nonce'], 'rfqking_save_units')) {
            wp_die(__('Error de seguridad: intento no autorizado.', 'rfqking'));
        }

        $units = isset($_POST['units']) ? array_map('sanitize_text_field', explode(',', $_POST['units'])) : [];
        update_option('rfqking_quantity_units', $units);

        echo '<div class="updated"><p>' . __('Unidades guardadas correctamente.', 'rfqking') . '</p></div>';
    }

    $units = get_option('rfqking_quantity_units', []);
    $units_list = implode(',', $units);
    ?>
    <div class="wrap">
        <h1><?php echo esc_html__('Configuración de RFQKing', 'rfqking'); ?></h1>
        <form method="post">
            <?php wp_nonce_field('rfqking_save_units', 'rfqking_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="units"><?php echo esc_html__('Tipos de cantidad', 'rfqking'); ?></label></th>
                    <td>
                        <textarea name="units" id="units" rows="5" style="width: 100%;"><?php echo esc_textarea($units_list); ?></textarea>
                        <p class="description"><?php echo esc_html__('Escribe cada tipo de cantidad separado por comas (por ejemplo: piezas, unidades, bolsas, toneladas).', 'rfqking'); ?></p>
                    </td>
                </tr>
            </table>
            <button type="submit" name="save_rfqking_units" class="button button-primary"><?php echo esc_html__('Guardar cambios', 'rfqking'); ?></button>
        </form>
    </div>
    <?php
}