<?php
/**
 * Template para mostrar la tabla de RFQs del cliente.
 */
global $wpdb;
$table_name = $wpdb->prefix . 'rfqking_requests';
$user_id = get_current_user_id();
$rfqs = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE customer_id = %d ORDER BY created_at DESC", $user_id));
?>
<table class="shop_table shop_table_responsive my_account_orders">
    <thead>
        <tr>
            <th><?php echo esc_html__('Producto', 'rfqking'); ?></th>
            <th><?php echo esc_html__('Cantidad', 'rfqking'); ?></th>
            <th><?php echo esc_html__('Tipo de cantidad', 'rfqking'); ?></th>
            <th><?php echo esc_html__('Estado', 'rfqking'); ?></th>
            <th><?php echo esc_html__('Fecha límite', 'rfqking'); ?></th>
            <th><?php echo esc_html__('Acciones', 'rfqking'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($rfqs)) : ?>
            <?php foreach ($rfqs as $rfq) : ?>
                <tr>
                    <td><?php echo esc_html($rfq->product_name); ?></td>
                    <td><?php echo esc_html($rfq->quantity); ?></td>
                    <td><?php echo esc_html($rfq->quantity_unit); ?></td>
                    <td><?php echo esc_html($rfq->status); ?></td>
                    <td><?php echo esc_html($rfq->deadline); ?></td>
                    <td>
                        <a href="<?php echo wc_get_account_endpoint_url('rfq') . '?view_rfq=' . $rfq->id; ?>" class="button">
                            <?php echo esc_html__('Ver detalles', 'rfqking'); ?>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else : ?>
            <tr>
                <td colspan="5"><?php echo esc_html__('No has enviado ninguna solicitud de cotización.', 'rfqking'); ?></td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<? 
// <th><?php echo esc_html__('Detalles', 'rfqking');
//<td><?php echo esc_html($rfq->description);
