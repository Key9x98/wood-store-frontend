<?php
/**
 * Plugin Name: Force Theme (TEMPLATE_NAME)
 * Description: Tự động kích hoạt theme được khai báo trong hằng TEMPLATE_NAME ở wp-config.php.
 * Version:     1.0.0
 *
 * Đây là must-use plugin: nạp tự động và rất sớm (trước khi WordPress chọn theme),
 * nên các filter dưới đây kịp ép theme đang chạy.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Không có hằng -> không làm gì, WordPress dùng theme lưu trong DB như bình thường.
if ( ! defined( 'TEMPLATE_NAME' ) || ! TEMPLATE_NAME ) {
	return;
}

// An toàn: chỉ ép khi thư mục theme thực sự tồn tại, tránh làm sập site.
if ( ! is_dir( WP_CONTENT_DIR . '/themes/' . TEMPLATE_NAME ) ) {
	return;
}

/**
 * Trả về slug theme cần ép. Dùng chung cho mọi filter bên dưới.
 *
 * @return string
 */
$wood_force_theme = static function () {
	return TEMPLATE_NAME;
};

// get_template() / get_stylesheet() áp 2 filter này -> ép theme đang render.
add_filter( 'template', $wood_force_theme, 99 );
add_filter( 'stylesheet', $wood_force_theme, 99 );

// Ép luôn giá trị đọc trực tiếp từ DB để đồng bộ ở trang admin (Appearance > Themes).
add_filter( 'pre_option_template', $wood_force_theme, 99 );
add_filter( 'pre_option_stylesheet', $wood_force_theme, 99 );
