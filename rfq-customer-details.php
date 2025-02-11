<?php
/**
 * Template para mostrar los detalles de un RFQ del cliente.
 */
global $wpdb;
$table_name = $wpdb->prefix . 'rfqking_requests';
$rfq_id = isset($_GET['view_rfq']) ? intval($_GET['view_rfq']) : 0;
$rfq = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d AND customer_id = %d", $rfq_id, get_current_user_id()));

if (!$rfq) {
    echo '<div class="error"><p>' . __('Solicitud de cotización no encontrada.', 'rfqking') . '</p></div>';
    return;
}
?>
<div class="wrap">
    <h2><?php echo esc_html__('Detalles de la solicitud de cotización', 'rfqking'); ?></h2>
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
    </table>
    <a href="<?php echo wc_get_account_endpoint_url('rfq'); ?>" class="button"><?php echo esc_html__('Volver', 'rfqking'); ?></a>
</div>