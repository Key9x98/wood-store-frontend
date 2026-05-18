<?php
/**
 * Furniture Basic — theme bootstrap.
 */

add_action( 'after_setup_theme', function () {
  add_theme_support( 'title-tag' );
  add_theme_support( 'post-thumbnails' );
  register_nav_menus( array( 'primary' => 'Menu chính' ) );
} );

// Custom post type cho sản phẩm đồ gỗ.
add_action( 'init', function () {
  register_post_type( 'product', array(
    'label'        => 'Sản phẩm',
    'public'       => true,
    'has_archive'  => true,
    'menu_icon'    => 'dashicons-store',
    'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
    'rewrite'      => array( 'slug' => 'san-pham' ),
  ) );
} );

/**
 * Lấy giá trị một field nội dung.
 * Ưu tiên giá trị CMS đẩy xuống (wp_options['ai_builder_fields']),
 * fallback về default của template nếu chưa có.
 */
function fb_field( $key ) {
  static $defaults = array(
    'shop_name'     => 'Nội Thất Gỗ Việt',
    'phone'         => '0900 123 456',
    'address'       => '123 Đường Gỗ, Quận 1, TP. HCM',
    'intro'         => 'Chuyên tủ, bàn, ghế, sập gỗ tự nhiên — bền đẹp, giá tận xưởng.',
    'hotline_color' => '#8b5a2b',
  );
  $fields = get_option( 'ai_builder_fields', array() );
  if ( is_array( $fields ) && isset( $fields[ $key ] ) && $fields[ $key ] !== '' ) {
    return (string) $fields[ $key ];
  }
  return isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';
}

/**
 * Shortcode [ai_field key="..."] — dùng được trong nội dung bài viết.
 * Trong file template PHP thì gọi thẳng fb_field().
 */
add_shortcode( 'ai_field', function ( $atts ) {
  $atts = shortcode_atts( array( 'key' => '' ), $atts );
  return esc_html( fb_field( $atts['key'] ) );
} );
