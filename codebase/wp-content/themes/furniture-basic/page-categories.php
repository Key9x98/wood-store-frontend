<?php
/**
 * Trang index "Danh mục sản phẩm" — phục vụ URL /danh-muc/ (không có slug).
 *
 * Không phải template của 1 WP Page cụ thể: file này được include trực tiếp
 * bởi hook `template_redirect` trong functions.php khi WP bị 404 ở URL
 * `/danh-muc/` raw (vì `danh-muc` chỉ là rewrite prefix cho taxonomy
 * product_cat, không có term gốc nào ngụ tại đó).
 */

defined( 'ABSPATH' ) || exit;

get_header();

// Lấy tất cả product categories có sản phẩm. Loại "uncategorized" (slug mặc định)
// vì gần như không bao giờ là danh mục có ý nghĩa cho khách.
$fb_cats = get_terms(
  array(
    'taxonomy'   => 'product_cat',
    'hide_empty' => true,
    'orderby'    => 'count',
    'order'      => 'DESC',
    'exclude'    => array( (int) get_option( 'default_product_cat' ) ),
  )
);
if ( is_wp_error( $fb_cats ) ) {
  $fb_cats = array();
}
$fb_total_cats = count( $fb_cats );
$fb_shop_url   = get_post_type_archive_link( 'product' );
?>

<!-- ===== HERO ===== -->
<section class="page-hero page-hero--cats">
  <div class="container">
    <nav class="breadcrumb breadcrumb--on-hero" aria-label="Đường dẫn">
      <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Trang chủ</a>
      <span class="breadcrumb__sep">›</span>
      <span>Danh mục sản phẩm</span>
    </nav>
    <span class="eyebrow">Bộ sưu tập</span>
    <h1>Danh mục sản phẩm</h1>
    <p>Khám phá từng dòng đồ gỗ tự nhiên — tủ thờ, vách ngăn, giường ngủ và nhiều hơn nữa.</p>
    <?php if ( $fb_total_cats > 0 ) : ?>
      <p class="page-hero__meta">
        <?php echo esc_html( $fb_total_cats ); ?> danh mục · cập nhật liên tục
      </p>
    <?php endif; ?>
  </div>
</section>

<div class="container categories-page">
  <?php if ( $fb_cats ) : ?>
    <div class="cat-grid cat-grid--lg">
      <?php foreach ( $fb_cats as $fb_cat ) :
        $fb_thumb_id   = fb_cat_thumb_id( $fb_cat );
        $fb_term_link  = get_term_link( $fb_cat );
        ?>
        <a class="cat-card cat-card--lg" href="<?php echo esc_url( $fb_term_link ); ?>">
          <?php
          if ( $fb_thumb_id ) {
            echo wp_get_attachment_image( $fb_thumb_id, 'fb-product', false, array( 'loading' => 'lazy' ) );
          } else {
            echo '<span class="cat-card__fallback"></span>';
          }
          ?>
          <span class="cat-card__go" aria-hidden="true"><?php echo fb_icon( 'arrow', 18 ); ?></span>
          <span class="cat-card__body">
            <span class="cat-card__name"><?php echo esc_html( $fb_cat->name ); ?></span>
            <span class="cat-card__count"><?php echo (int) $fb_cat->count; ?> sản phẩm</span>
            <?php if ( $fb_cat->description ) : ?>
              <span class="cat-card__desc"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $fb_cat->description ), 14 ) ); ?></span>
            <?php endif; ?>
          </span>
        </a>
      <?php endforeach; ?>
    </div>

    <div class="categories-page__foot">
      <p>Hoặc xem nhanh toàn bộ sản phẩm trong cùng một trang.</p>
      <a class="btn btn--primary" href="<?php echo esc_url( $fb_shop_url ); ?>">
        <?php echo fb_icon( 'arrow', 18 ); ?> Tất cả sản phẩm
      </a>
    </div>
  <?php else : ?>
    <div class="empty-state empty-state--lg">
      <?php echo fb_icon( 'sofa', 64 ); ?>
      <h3>Chưa có danh mục</h3>
      <p>Hệ thống đang cập nhật. Vui lòng quay lại sau, hoặc xem danh sách sản phẩm hiện có.</p>
      <a class="btn btn--primary btn--sm" href="<?php echo esc_url( $fb_shop_url ); ?>">Xem tất cả sản phẩm</a>
    </div>
  <?php endif; ?>
</div>

<?php
get_footer();
