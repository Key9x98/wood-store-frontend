<?php
/**
 * Furniture Basic — theme bootstrap.
 *
 * Template cửa hàng đồ gỗ. Giá trị nội dung lấy qua fb_field()
 * (CMS auto website builder đẩy xuống, hoặc default của template).
 */

defined( 'ABSPATH' ) || exit;

define( 'FB_VERSION', '2.0.0' );
define( 'FB_DIR', get_template_directory() );
define( 'FB_URI', get_template_directory_uri() );

/* =========================================================================
 * 1. Theme setup
 * ====================================================================== */
add_action( 'after_setup_theme', function () {
  add_theme_support( 'title-tag' );
  add_theme_support( 'post-thumbnails' );
  add_theme_support( 'automatic-feed-links' );
  add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
  add_theme_support( 'custom-logo', array(
    'height'      => 64,
    'width'       => 240,
    'flex-height' => true,
    'flex-width'  => true,
  ) );

  add_image_size( 'fb-product', 640, 640, true );
  add_image_size( 'fb-product-lg', 1100, 1100, true );

  register_nav_menus( array(
    'primary' => 'Menu chính',
    'footer'  => 'Menu chân trang',
  ) );
} );

/* =========================================================================
 * 2. Assets
 * ====================================================================== */
add_action( 'wp_enqueue_scripts', function () {
  wp_enqueue_style(
    'fb-fonts',
    'https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700;800&display=swap',
    array(),
    null
  );
  wp_enqueue_style( 'furniture-basic', get_stylesheet_uri(), array( 'fb-fonts' ), FB_VERSION );
  wp_add_inline_style( 'furniture-basic', fb_inline_css() );

  wp_enqueue_script( 'furniture-basic', FB_URI . '/assets/js/theme.js', array(), FB_VERSION, true );
} );

/**
 * Custom properties dựng từ màu thương hiệu (fb_field('primary_color')).
 */
function fb_inline_css() {
  $primary = fb_field( 'primary_color' );
  if ( ! preg_match( '/^#[0-9a-fA-F]{6}$/', (string) $primary ) ) {
    $primary = '#8b5a2b';
  }
  return sprintf(
    ':root{--wood:%1$s;--wood-dark:%2$s;--wood-light:%3$s;}',
    $primary,
    fb_shift_color( $primary, -42 ),
    fb_shift_color( $primary, 46 )
  );
}

/** Làm sáng/tối một mã màu hex. */
function fb_shift_color( $hex, $steps ) {
  $hex = ltrim( (string) $hex, '#' );
  if ( strlen( $hex ) !== 6 ) {
    return '#' . $hex;
  }
  $out = '#';
  for ( $i = 0; $i < 3; $i++ ) {
    $c    = hexdec( substr( $hex, $i * 2, 2 ) );
    $c    = max( 0, min( 255, $c + $steps ) );
    $out .= str_pad( dechex( $c ), 2, '0', STR_PAD_LEFT );
  }
  return $out;
}

/* =========================================================================
 * 3. Custom post type + taxonomy
 * ====================================================================== */
add_action( 'init', function () {
  register_post_type( 'product', array(
    'label'       => 'Sản phẩm',
    'labels'      => array(
      'name'               => 'Sản phẩm',
      'singular_name'      => 'Sản phẩm',
      'add_new'            => 'Thêm sản phẩm',
      'add_new_item'       => 'Thêm sản phẩm mới',
      'edit_item'          => 'Sửa sản phẩm',
      'all_items'          => 'Tất cả sản phẩm',
      'search_items'       => 'Tìm sản phẩm',
      'not_found'          => 'Chưa có sản phẩm nào.',
    ),
    'public'      => true,
    'has_archive' => true,
    'menu_icon'   => 'dashicons-store',
    'supports'    => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
    'rewrite'     => array( 'slug' => 'san-pham' ),
  ) );

  register_taxonomy( 'product_cat', 'product', array(
    'label'             => 'Danh mục sản phẩm',
    'labels'            => array(
      'name'          => 'Danh mục',
      'singular_name' => 'Danh mục',
      'add_new_item'  => 'Thêm danh mục',
    ),
    'hierarchical'      => true,
    'public'            => true,
    'show_admin_column' => true,
    'rewrite'           => array( 'slug' => 'danh-muc' ),
  ) );
} );

// Flush rewrite rules khi kích hoạt theme để archive /san-pham hoạt động.
add_action( 'after_switch_theme', 'flush_rewrite_rules' );

// Số sản phẩm mỗi trang + sắp xếp trên trang shop.
add_action( 'pre_get_posts', function ( $q ) {
  if ( is_admin() || ! $q->is_main_query() ) {
    return;
  }
  if ( $q->is_post_type_archive( 'product' ) || $q->is_tax( 'product_cat' ) ) {
    $q->set( 'posts_per_page', 12 );
    $orderby = isset( $_GET['orderby'] ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : '';
    switch ( $orderby ) {
      case 'title':
        $q->set( 'orderby', 'title' );
        $q->set( 'order', 'ASC' );
        break;
      case 'price_asc':
      case 'price_desc':
        // meta_query dạng OR để sản phẩm chưa đặt giá vẫn hiển thị.
        $dir = ( 'price_asc' === $orderby ) ? 'ASC' : 'DESC';
        $q->set( 'meta_query', array(
          'relation'     => 'OR',
          'fb_has_price' => array( 'key' => '_fb_price', 'type' => 'NUMERIC', 'compare' => 'EXISTS' ),
          'fb_no_price'  => array( 'key' => '_fb_price', 'compare' => 'NOT EXISTS' ),
        ) );
        $q->set( 'orderby', array( 'fb_has_price' => $dir ) );
        break;
      default:
        $q->set( 'orderby', 'date' );
        $q->set( 'order', 'DESC' );
    }
  }
} );

/* =========================================================================
 * 4. Product price — meta box
 * ====================================================================== */
add_action( 'add_meta_boxes', function () {
  add_meta_box( 'fb_product_price', 'Giá bán', 'fb_render_price_box', 'product', 'side', 'high' );
} );

function fb_render_price_box( $post ) {
  wp_nonce_field( 'fb_save_price', 'fb_price_nonce' );
  $regular = get_post_meta( $post->ID, '_fb_price', true );
  $sale    = get_post_meta( $post->ID, '_fb_sale_price', true );
  $badge   = get_post_meta( $post->ID, '_fb_badge', true );
  echo '<p><label for="fb_price"><strong>Giá gốc (₫)</strong></label><br>';
  echo '<input type="number" min="0" step="1000" id="fb_price" name="fb_price" value="' . esc_attr( $regular ) . '" style="width:100%"></p>';
  echo '<p><label for="fb_sale_price"><strong>Giá khuyến mãi (₫)</strong></label><br>';
  echo '<input type="number" min="0" step="1000" id="fb_sale_price" name="fb_sale_price" value="' . esc_attr( $sale ) . '" style="width:100%">';
  echo '<span style="color:#777;font-size:11px">Để trống nếu không giảm giá.</span></p>';
  echo '<p><label for="fb_badge"><strong>Nhãn nổi bật</strong></label><br>';
  echo '<input type="text" id="fb_badge" name="fb_badge" value="' . esc_attr( $badge ) . '" placeholder="VD: Bán chạy, Mới" style="width:100%"></p>';
}

add_action( 'save_post_product', function ( $post_id ) {
  if ( ! isset( $_POST['fb_price_nonce'] ) || ! wp_verify_nonce( $_POST['fb_price_nonce'], 'fb_save_price' ) ) {
    return;
  }
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
    return;
  }
  if ( ! current_user_can( 'edit_post', $post_id ) ) {
    return;
  }
  update_post_meta( $post_id, '_fb_price', isset( $_POST['fb_price'] ) ? (float) $_POST['fb_price'] : '' );
  update_post_meta( $post_id, '_fb_sale_price', isset( $_POST['fb_sale_price'] ) ? (float) $_POST['fb_sale_price'] : '' );
  update_post_meta( $post_id, '_fb_badge', isset( $_POST['fb_badge'] ) ? sanitize_text_field( wp_unslash( $_POST['fb_badge'] ) ) : '' );
} );

/* =========================================================================
 * 5. Content fields (CMS auto builder)
 * ====================================================================== */

/**
 * Lấy giá trị một field nội dung.
 * Ưu tiên giá trị CMS đẩy xuống (wp_options['ai_builder_fields']),
 * fallback về default của template.
 */
function fb_field( $key, $fallback = '' ) {
  static $defaults = array(
    'shop_name'     => 'Nội Thất Gỗ Việt',
    'tagline'       => 'Đồ gỗ tự nhiên — bền đẹp với thời gian',
    'phone'         => '0900 123 456',
    'email'         => 'lienhe@noithatgoviet.vn',
    'address'       => '123 Đường Gỗ, Quận 1, TP. HCM',
    'working_hours' => '8:00 – 20:00 (Thứ 2 – Chủ nhật)',
    'intro'         => 'Chuyên tủ, bàn, ghế, sập gỗ tự nhiên — bền đẹp, giá tận xưởng. Giao lắp tận nơi, bảo hành dài hạn.',
    'about'         => 'Hơn 15 năm gắn bó với nghề mộc, xưởng chúng tôi chế tác đồ gỗ từ các loại gỗ quý: gụ, hương, sồi, xoan đào. Mỗi sản phẩm đều được người thợ chăm chút từng đường vân, mộng ghép — chắc chắn, tinh xảo và bền với thời gian.',
    'primary_color' => '#8b5a2b',
    'hotline_color' => '#8b5a2b',
    'hero_image'    => '',
    'about_image'   => '',
    'facebook'      => '',
    'zalo'          => '',
    'years'         => '15',
    'products_done' => '1.200+',
    'customers'     => '5.000+',
  );
  $fields = get_option( 'ai_builder_fields', array() );
  if ( is_array( $fields ) && isset( $fields[ $key ] ) && $fields[ $key ] !== '' ) {
    return (string) $fields[ $key ];
  }
  if ( isset( $defaults[ $key ] ) ) {
    return $defaults[ $key ];
  }
  return $fallback;
}

/** Shortcode [ai_field key="..."] dùng trong nội dung bài viết. */
add_shortcode( 'ai_field', function ( $atts ) {
  $atts = shortcode_atts( array( 'key' => '' ), $atts );
  return esc_html( fb_field( $atts['key'] ) );
} );

/* =========================================================================
 * 6. Price helpers
 * ====================================================================== */
function fb_get_price( $post_id = null ) {
  $post_id = $post_id ? $post_id : get_the_ID();
  return array(
    'regular' => (float) get_post_meta( $post_id, '_fb_price', true ),
    'sale'    => (float) get_post_meta( $post_id, '_fb_sale_price', true ),
  );
}

function fb_format_price( $amount ) {
  return number_format( (float) $amount, 0, ',', '.' ) . '₫';
}

/** True nếu sản phẩm đang giảm giá hợp lệ. */
function fb_is_on_sale( $post_id = null ) {
  $p = fb_get_price( $post_id );
  return $p['regular'] > 0 && $p['sale'] > 0 && $p['sale'] < $p['regular'];
}

function fb_sale_percent( $post_id = null ) {
  if ( ! fb_is_on_sale( $post_id ) ) {
    return 0;
  }
  $p = fb_get_price( $post_id );
  return (int) round( ( $p['regular'] - $p['sale'] ) / $p['regular'] * 100 );
}

/** HTML hiển thị giá. */
function fb_price_html( $post_id = null ) {
  $p = fb_get_price( $post_id );
  if ( $p['regular'] <= 0 ) {
    return '<span class="price price--ask">Liên hệ</span>';
  }
  if ( fb_is_on_sale( $post_id ) ) {
    return '<span class="price"><ins>' . esc_html( fb_format_price( $p['sale'] ) ) . '</ins>'
         . '<del>' . esc_html( fb_format_price( $p['regular'] ) ) . '</del></span>';
  }
  return '<span class="price">' . esc_html( fb_format_price( $p['regular'] ) ) . '</span>';
}

/* =========================================================================
 * 7. Template helpers
 * ====================================================================== */

/** In ảnh sản phẩm, hoặc placeholder nếu chưa có ảnh. */
function fb_product_thumb( $size = 'fb-product' ) {
  if ( has_post_thumbnail() ) {
    the_post_thumbnail( $size, array( 'loading' => 'lazy' ) );
  } else {
    echo '<span class="thumb-fallback" aria-hidden="true">' . fb_icon( 'sofa', 56 ) . '</span>';
  }
}

/** Số điện thoại đã bỏ khoảng trắng, dùng cho href="tel:". */
function fb_tel( $phone = null ) {
  $phone = null === $phone ? fb_field( 'phone' ) : $phone;
  return preg_replace( '/[^0-9+]/', '', $phone );
}

/** Menu fallback khi chưa gán menu trong wp-admin. */
function fb_fallback_menu() {
  echo '<ul class="nav-menu">';
  echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">Trang chủ</a></li>';
  echo '<li><a href="' . esc_url( get_post_type_archive_link( 'product' ) ) . '">Sản phẩm</a></li>';
  $cats = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => true, 'number' => 4 ) );
  if ( $cats && ! is_wp_error( $cats ) ) {
    foreach ( $cats as $cat ) {
      echo '<li><a href="' . esc_url( get_term_link( $cat ) ) . '">' . esc_html( $cat->name ) . '</a></li>';
    }
  }
  echo '<li><a href="#lien-he">Liên hệ</a></li>';
  echo '</ul>';
}

/** Breadcrumb. */
function fb_breadcrumb() {
  $home  = '<a href="' . esc_url( home_url( '/' ) ) . '">Trang chủ</a>';
  $items = array( $home );

  if ( is_singular( 'product' ) ) {
    $items[] = '<a href="' . esc_url( get_post_type_archive_link( 'product' ) ) . '">Sản phẩm</a>';
    $terms   = get_the_terms( get_the_ID(), 'product_cat' );
    if ( $terms && ! is_wp_error( $terms ) ) {
      $items[] = '<a href="' . esc_url( get_term_link( $terms[0] ) ) . '">' . esc_html( $terms[0]->name ) . '</a>';
    }
    $items[] = '<span>' . esc_html( get_the_title() ) . '</span>';
  } elseif ( is_post_type_archive( 'product' ) ) {
    $items[] = '<span>Sản phẩm</span>';
  } elseif ( is_tax( 'product_cat' ) ) {
    $items[] = '<a href="' . esc_url( get_post_type_archive_link( 'product' ) ) . '">Sản phẩm</a>';
    $items[] = '<span>' . esc_html( single_term_title( '', false ) ) . '</span>';
  } elseif ( is_search() ) {
    $items[] = '<span>Kết quả tìm kiếm</span>';
  } elseif ( is_singular() ) {
    $items[] = '<span>' . esc_html( get_the_title() ) . '</span>';
  } else {
    $items[] = '<span>' . esc_html( wp_strip_all_tags( get_the_archive_title() ) ) . '</span>';
  }

  echo '<nav class="breadcrumb" aria-label="Breadcrumb"><div class="container">'
     . implode( ' <span class="breadcrumb__sep">›</span> ', $items )
     . '</div></nav>';
}

/**
 * Bộ icon SVG inline. Trả về chuỗi <svg>.
 */
function fb_icon( $name, $size = 24 ) {
  $stroke = array(
    'truck'    => '<rect x="1" y="4" width="14" height="12" rx="1.5"/><path d="M15 8h4l3 3v5h-7z"/><circle cx="6" cy="18.5" r="2"/><circle cx="18" cy="18.5" r="2"/>',
    'shield'   => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 11.5l2 2 4-4.5"/>',
    'tag'      => '<path d="M20.6 13.4l-7.2 7.2a2 2 0 0 1-2.8 0L2 12V2h10l8.6 8.6a2 2 0 0 1 0 2.8z"/><circle cx="7" cy="7" r="1"/>',
    'headset'  => '<path d="M4 17v-5a8 8 0 0 1 16 0v5"/><path d="M20 18a2 2 0 0 1-2 2h-2v-6h2a2 2 0 0 1 2 2z"/><path d="M4 18a2 2 0 0 0 2 2h2v-6H6a2 2 0 0 0-2 2z"/>',
    'phone'    => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7A2 2 0 0 1 22 16.9z"/>',
    'mail'     => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 7 10 6 10-6"/>',
    'clock'    => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/>',
    'pin'      => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="2.6"/>',
    'search'   => '<circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>',
    'arrow'    => '<path d="M5 12h14"/><path d="m13 5 7 7-7 7"/>',
    'check'    => '<path d="M20 6 9 17l-5-5"/>',
    'chevron'  => '<path d="m9 6 6 6-6 6"/>',
    'sofa'     => '<path d="M5 11V8a3 3 0 0 1 3-3h8a3 3 0 0 1 3 3v3"/><path d="M3 16v-3a2 2 0 0 1 4 0v2h10v-2a2 2 0 0 1 4 0v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M6 18v2"/><path d="M18 18v2"/>',
    'award'    => '<circle cx="12" cy="9" r="6"/><path d="M8.2 13.5 7 22l5-2.8L17 22l-1.2-8.5"/>',
    'leaf'     => '<path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.5 19 2c1 2 2 4.2 2 8 0 5.5-4.8 10-10 10z"/><path d="M2 21c0-3 1.9-5.4 5.1-6"/>',
    'ruler'    => '<rect x="2" y="8" width="20" height="8" rx="1.5"/><path d="M7 8v3M12 8v4M17 8v3"/>',
    'chat'     => '<path d="M21 11.5a8.4 8.4 0 0 1-8.5 8.5 8.5 8.5 0 0 1-3.8-.9L3 21l1.9-5.7A8.4 8.4 0 0 1 4 11.5 8.5 8.5 0 0 1 12.5 3 8.4 8.4 0 0 1 21 11.5z"/>',
    'menu'     => '<path d="M3 6h18M3 12h18M3 18h18"/>',
    'gift'     => '<rect x="3" y="9" width="18" height="12" rx="1.5"/><path d="M3 13h18M12 9v12"/><path d="M12 9S10 3 7 5s5 4 5 4zm0 0s2-6 5-4-5 4-5 4z"/>',
  );
  $fill = array(
    'star'     => '<path d="M12 2.5l2.9 6 6.6.9-4.8 4.6 1.2 6.5L12 18.4 6.1 20.5l1.2-6.5L2.5 9.4l6.6-.9z"/>',
    'facebook' => '<path d="M17 2h-3a5 5 0 0 0-5 5v3H6v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>',
    'quote'    => '<path d="M10 7H6a4 4 0 0 0-4 4v6h8v-7H5a3 3 0 0 1 3-3zm12 0h-4a4 4 0 0 0-4 4v6h8v-7h-3a3 3 0 0 1 3-3z"/>',
  );

  if ( isset( $stroke[ $name ] ) ) {
    return sprintf(
      '<svg class="icon icon--%1$s" width="%2$d" height="%2$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">%3$s</svg>',
      esc_attr( $name ), (int) $size, $stroke[ $name ]
    );
  }
  if ( isset( $fill[ $name ] ) ) {
    return sprintf(
      '<svg class="icon icon--%1$s" width="%2$d" height="%2$d" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">%3$s</svg>',
      esc_attr( $name ), (int) $size, $fill[ $name ]
    );
  }
  return '';
}

/* =========================================================================
 * 8. Excerpt tweaks
 * ====================================================================== */
add_filter( 'excerpt_length', function ( $len ) {
  return is_singular( 'product' ) || is_post_type_archive( 'product' ) ? 18 : $len;
} );
add_filter( 'excerpt_more', function () {
  return '…';
} );
