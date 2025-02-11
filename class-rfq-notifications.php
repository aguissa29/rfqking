<?php
if (!class_exists('RFQ_Notifications')) {
    class RFQ_Notifications {
        public function send_notification($to, $subject, $message) {
            $headers = ['Content-Type: text/html; charset=UTF-8'];
            wp_mail($to, $subject, $message, $headers);
        }
    }
}