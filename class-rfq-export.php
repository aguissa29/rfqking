<?php
if (!class_exists('RFQ_Export')) {
    class RFQ_Export {
        public static function init() {
            add_action('admin_post_export_rfq_csv', array(__CLASS__, 'export_csv'));
        }

        public static function export_csv() {
            global $wpdb;
            $table_name = $wpdb->prefix . 'rfqking_requests';
            $rfqs = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC");

            // Encabezados para descargar el archivo CSV
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="rfqking-requests.csv"');
            header('Pragma: no-cache');
            header('Expires: 0');

            // Abrir flujo de salida
            $output = fopen('php://output', 'w');

            // Escribir encabezados
            fputcsv($output, ['ID', 'Producto', 'Cantidad', 'Categoría', 'Rango de Precio', 'Fecha Límite', 'Estado', 'Archivos Adjuntos', 'Fecha de Creación']);

            // Escribir datos
            foreach ($rfqs as $rfq) {
                $attachments = json_decode($rfq->attachments, true);
                $attachment_links = !empty($attachments) ? implode(', ', $attachments) : '';
                fputcsv($output, [
                    $rfq->id,
                    $rfq->product_name,
                    $rfq->quantity,
                    $rfq->category,
                    $rfq->price_range,
                    $rfq->deadline,
                    $rfq->status,
                    $attachment_links,
                    $rfq->created_at,
                ]);
            }

            fclose($output);
            exit;
        }
    }
}
RFQ_Export::init();