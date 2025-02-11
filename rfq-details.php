<?php
/**
 * Template para ver los detalles de una solicitud de cotización.
 */
global $wpdb;
$table_name = $wpdb->prefix . 'rfqking_requests';
$rfq_id = isset($_GET['rfq_id']) ? intval($_GET['rfq_id']) : 0;
$rfq = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $rfq_id));

if (!$rfq) {
    echo '<div class="error"><p>' . __('Solicitud de cotización no encontrada.', 'rfqking') . '</p></div>';
    return;
}

// Procesar las notas internas
if (isset($_POST['notes'])) {
    if (!isset($_POST['rfqking_nonce']) || !wp_verify_nonce($_POST['rfqking_nonce'], 'rfqking_update_notes')) {
        wp_die(__('Error de seguridad: intento no autorizado.', 'rfqking'));
    }

    $rfq_id = intval($_POST['rfq_id']);
    $notes = sanitize_textarea_field($_POST['notes']);

    $wpdb->update(
        $table_name,
        ['notes' => $notes],
        ['id' => $rfq_id]
    );

    echo '<div class="updated"><p>' . __('Notas guardadas correctamente.', 'rfqking') . '</p></div>';
}
?>
<div class="wrap">
    <h1><?php echo esc_html__('Detalles de la solicitud de cotización', 'rfqking'); ?></h1>

    <table class="form-table">
        <tr>
            <th><label><?php echo esc_html__('Producto', 'rfqking'); ?>:</label></th>
            <td><?php echo esc_html($rfq->product_name); ?></td>
        </tr>
        <tr>
            <th><label><?php echo esc_html__('Cantidad', 'rfqking'); ?>:</label></th>
            <td><?php echo esc_html($rfq->quantity); ?></td>
        </tr>
        <tr>
            <th><label><?php echo esc_html__('Categoría', 'rfqking'); ?>:</label></th>
            <td><?php echo esc_html($rfq->category); ?></td>
        </tr>
        <tr>
            <th><label><?php echo esc_html__('Rango de precio', 'rfqking'); ?>:</label></th>
            <td><?php echo esc_html($rfq->price_range); ?></td>
        </tr>
        <tr>
            <th><label><?php echo esc_html__('Fecha límite', 'rfqking'); ?>:</label></th>
            <td><?php echo esc_html($rfq->deadline); ?></td>
        </tr>
        <tr>
            <th><label><?php echo esc_html__('Estado', 'rfqking'); ?>:</label></th>
            <td><?php echo esc_html($rfq->status); ?></td>
        </tr>
        <tr>
            <th><label><?php echo esc_html__('Archivos adjuntos', 'rfqking'); ?>:</label></th>
            <td>
                <?php
                $attachments = json_decode($rfq->attachments, true);
                if (!empty($attachments)) {
                    foreach ($attachments as $attachment) {
                        echo '<a href="' . esc_url($attachment) . '" target="_blank">' . basename($attachment) . '</a><br>';
                    }
                } else {
                    echo esc_html__('No hay archivos adjuntos.', 'rfqking');
                }
                ?>
            </td>
        </tr>
        <tr>
            <th><label><?php echo esc_html__('Notas internas', 'rfqking'); ?>:</label></th>
            <td>
                <form method="post" style="display:inline;">
                    <textarea name="notes" rows="3" style="width: 100%;"><?php echo esc_textarea($rfq->notes); ?></textarea>
                    <input type="hidden" name="rfq_id" value="<?php echo esc_attr($rfq->id); ?>">
                    <?php wp_nonce_field('rfqking_update_notes', 'rfqking_nonce'); ?>
                    <button type="submit" class="button"><?php echo esc_html__('Guardar notas', 'rfqking'); ?></button>
                </form>
            </td>
        </tr>
    </table>

    <a href="<?php echo admin_url('admin.php?page=rfqking-dashboard'); ?>" class="button"><?php echo esc_html__('Volver al dashboard', 'rfqking'); ?></a>
</div>