<?php
/*
Plugin Name: CF7 to Telegram
Plugin URI: https://github.com/Rad18/cf7-to-telegram
Description: Отправляет данные из формы Contact Form 7 в Telegram при успешной отправке формы.
Version: 1.0
Author: RAD
Author URI: https://github.com/Rad18/
License: GPL2
*/

if (!defined('ABSPATH')) {
    exit; // Защита от прямого доступа
}

// Подключаем файл с настройками
require_once plugin_dir_path(__FILE__) . 'includes/admin-settings.php';

// Основная функция отправки данных в Telegram
function cf7_to_telegram_send_message($contact_form) {
    $options = get_option('cf7_to_telegram_settings');
    $bot_token = $options['bot_token'] ?? '';
    $chat_id = $options['chat_id'] ?? '';
    $message_template = $options['message_template'] ?? "New Contact Form Submission:\n";

    if (empty($bot_token) || empty($chat_id)) {
        error_log('CF7 to Telegram: Bot Token или Chat ID не настроены.');
        return;
    }

    $submission = WPCF7_Submission::get_instance();
    if ($submission) {
        $form_data = $submission->get_posted_data();
        $referrer = $submission->get_meta('http_referer') ?: ($_SERVER['HTTP_REFERER'] ?? 'Unknown');

        $name = sanitize_text_field($form_data['your-name'] ?? '');
        $phone = sanitize_text_field($form_data['your-tel'] ?? '');
        $email = sanitize_email($form_data['your-email'] ?? '');
        $service = is_array($form_data['your-service'] ?? '') ? implode(", ", $form_data['your-service']) : ($form_data['your-service'] ?? '');
        $message_field = sanitize_textarea_field($form_data['textarea-message'] ?? '');

        // Подставляем данные в шаблон
        $message = str_replace(
            ['{name}', '{phone}', '{email}', '{service}', '{message}', '{page_url}'],
            [$name, $phone, $email, $service, $message_field, $referrer],
            $message_template
        );

        // Удаляем лишние пустые строки
        $message = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $message);

        // Отправка запроса в Telegram API
        $api_url = "https://api.telegram.org/bot{$bot_token}/sendMessage";
        $response = wp_remote_post($api_url, [
            'body' => [
                'chat_id' => $chat_id,
                'text' => $message,
                'parse_mode' => 'HTML',
            ],
        ]);

        if (is_wp_error($response)) {
            error_log("Telegram API Error: " . $response->get_error_message());
        }
    }
}

// Добавляем обработчик отправки формы CF7
add_action('wpcf7_mail_sent', 'cf7_to_telegram_send_message');
