<?php
if (!defined('ABSPATH')) {
    exit; // Защита от прямого доступа
}

// Добавляем страницу настроек в админ-панель
add_action('admin_menu', 'cf7_to_telegram_add_admin_menu');
add_action('admin_init', 'cf7_to_telegram_settings_init');

function cf7_to_telegram_add_admin_menu() {
    add_options_page(
        'CF7 to Telegram', 
        'CF7 to Telegram', 
        'manage_options', 
        'cf7-to-telegram', 
        'cf7_to_telegram_options_page'
    );
}

function cf7_to_telegram_settings_init() {
    register_setting('cf7_to_telegram', 'cf7_to_telegram_settings');

    add_settings_section(
        'cf7_to_telegram_section',
        'Настройки Telegram API',
        'cf7_to_telegram_section_callback',
        'cf7_to_telegram'
    );

    add_settings_field(
        'bot_token',
        'Telegram Bot Token',
        'cf7_to_telegram_bot_token_render',
        'cf7_to_telegram',
        'cf7_to_telegram_section'
    );

    add_settings_field(
        'chat_id',
        'Telegram Chat ID',
        'cf7_to_telegram_chat_id_render',
        'cf7_to_telegram',
        'cf7_to_telegram_section'
    );

    add_settings_field(
        'message_template',
        'Шаблон сообщения',
        'cf7_to_telegram_message_template_render',
        'cf7_to_telegram',
        'cf7_to_telegram_section'
    );
}

// Поле для ввода токена бота
function cf7_to_telegram_bot_token_render() {
    $options = get_option('cf7_to_telegram_settings');
    ?>
    <input type="text" name="cf7_to_telegram_settings[bot_token]" value="<?php echo esc_attr($options['bot_token'] ?? ''); ?>" style="width: 300px;">
    <?php
}

// Поле для ввода chat ID
function cf7_to_telegram_chat_id_render() {
    $options = get_option('cf7_to_telegram_settings');
    ?>
    <input type="text" name="cf7_to_telegram_settings[chat_id]" value="<?php echo esc_attr($options['chat_id'] ?? ''); ?>" style="width: 300px;">
    <?php
}

// Поле для шаблона сообщения
function cf7_to_telegram_message_template_render() {
    $options = get_option('cf7_to_telegram_settings');
    $default_message = "New Contact Form Submission:\nName: {name}\nPhone: {phone}\nEmail: {email}\nService: {service}\nMessage: {message}\nPage URL: {page_url}";
    ?>
    <textarea name="cf7_to_telegram_settings[message_template]" style="width: 100%; height: 150px;"><?php echo esc_textarea($options['message_template'] ?? $default_message); ?></textarea>
    <p class="description">Используйте плейсхолдеры: <code>{name}</code>, <code>{phone}</code>, <code>{email}</code>, <code>{service}</code>, <code>{message}</code>, <code>{page_url}</code>.</p>
    <?php
}

// Описание секции
function cf7_to_telegram_section_callback() {
    echo 'Введите данные вашего Telegram бота и настройте шаблон сообщения.';
}

// Страница настроек в админке
function cf7_to_telegram_options_page() {
    ?>
    <div class="wrap">
        <h2>CF7 to Telegram - Настройки</h2>
		<p><strong>Важно.</strong> Поля должны назваться с приставкой your.</p>
        <p>Убедитесь, что плагин Contact Form 7 установлен и активирован.</p>
        <p>Плагин требует PHP 7.0 или выше. Убедитесь, что ваш сервер использует подходящую версию.</p>
        <form action="options.php" method="post">
            <?php
            settings_fields('cf7_to_telegram');
            do_settings_sections('cf7_to_telegram');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}
