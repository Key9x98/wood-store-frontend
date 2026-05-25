<?php
/**
 * Archive cho post-type `product` (shop + product_cat taxonomy).
 *
 * WooCommerce bind `template_include` để override `archive.php` của theme cho
 * mọi WC archive — cách duy nhất để theme tự control là cung cấp file này
 * (và `taxonomy-product_cat.php` nếu muốn split nhánh, nhưng ở đây 1 file
 * xử lý cả 2 nhánh là đủ).
 *
 * Layout: hero → chip-bar lọc nhanh → sidebar + grid sản phẩm + pagination.
 */
defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

$fb_is_tax = is_tax( 'product_cat' );
if ( $fb_is_tax ) {
  $fb_title    = single_term_title( '', false );
  $fb_desc     = trim( wp_strip_all_tags( (string) term_description() ) );
  $fb_cur_term = get_queried_object();
  $fb_count    = isset( $fb_cur_term->count ) ? (int) $fb_cur_term->count : 0;
} else {
  $fb_title    = 'Tất cả sản phẩm';
  // /san-pham/: không có description riêng → để rỗng, hero compact hơn.
  $fb_desc     = '';
  $fb_count    = (int) ( $GLOBALS['wp_query']->found_posts ?? 0 );
}
?>

<!-- ===== HERO (compact) ===== -->
<section class="page-hero page-hero--compact">
  <div class="container">
    <nav class="breadcrumb breadcrumb--on-hero" aria-label="Đường dẫn">
      <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Trang chủ</a>
      <span class="breadcrumb__sep">›</span>
      <?php if ( $fb_is_tax ) : ?>
        <a href="<?php echo esc_url( home_url( '/danh-muc/' ) ); ?>">Danh mục</a>
        <span class="breadcrumb__sep">›</span>
      <?php endif; ?>
      <span><?php echo esc_html( $fb_title ); ?></span>
    </nav>
    <div class="page-hero__title-row">
      <h1><?php echo esc_html( $fb_title ); ?></h1>
      <?php if ( $fb_count > 0 ) : ?>
        <span class="page-hero__count"><?php echo (int) $fb_count; ?> sản phẩm</span>
      <?php endif; ?>
    </div>
    <?php if ( $fb_desc !== '' ) : ?>
      <p><?php echo esc_html( $fb_desc ); ?></p>
    <?php endif; ?>
  </div>
</section>

<!-- ===== QUICK CHIP FILTER ===== -->
<?php
$fb_chip_terms = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => true ) );
if ( $fb_chip_terms && ! is_wp_error( $fb_chip_terms ) ) :
  $fb_cur_id = $fb_is_tax ? get_queried_object_id() : 0;
  ?>
  <div class="cat-chip-bar" aria-label="Lọc nhanh theo danh mục">
    <div class="container">
      <div class="cat-chip-bar__scroll">
        <a class="cat-chip<?php echo ! $fb_is_tax ? ' is-active' : ''; ?>" href="<?php echo esc_url( get_post_type_archive_link( 'product' ) ); ?>">Tất cả</a>
        <?php foreach ( $fb_chip_terms as $fb_chip ) : ?>
          <a class="cat-chip<?php echo $fb_chip->term_id === $fb_cur_id ? ' is-active' : ''; ?>" href="<?php echo esc_url( get_term_link( $fb_chip ) ); ?>">
            <?php echo esc_html( $fb_chip->name ); ?>
            <span class="cat-chip__count"><?php echo (int) $fb_chip->count; ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
<?php endif; ?>

<div class="container shop">
  <aside class="shop-sidebar">
    <div class="widget">
      <h3 class="widget__title">Danh mục sản phẩm</h3>
      <ul class="cat-list">
        <li<?php echo ! $fb_is_tax ? ' class="is-active"' : ''; ?>>
          <a href="<?php echo esc_url( get_post_type_archive_link( 'product' ) ); ?>">Tất cả sản phẩm</a>
        </li>
        <?php
        $fb_cats   = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => true ) );
        $fb_cur_id = $fb_is_tax ? get_queried_object_id() : 0;
        if ( $fb_cats && ! is_wp_error( $fb_cats ) ) :
          foreach ( $fb_cats as $fb_cat ) : ?>
            <li<?php echo $fb_cat->term_id === $fb_cur_id ? ' class="is-active"' : ''; ?>>
              <a href="<?php echo esc_url( get_term_link( $fb_cat ) ); ?>">
                <span><?php echo esc_html( $fb_cat->name ); ?></span>
                <span class="count"><?php echo esc_html( $fb_cat->count ); ?></span>
              </a>
            </li>
          <?php endforeach;
        endif; ?>
      </ul>
    </div>
    <div class="sidebar-cta">
      <?php echo fb_icon( 'headset', 30 ); ?>
      <strong>Cần tư vấn?</strong>
      <span style="font-size:13px;opacity:.85">Gọi để được hỗ trợ chọn mẫu &amp; báo giá.</span>
      <a class="btn btn--gold btn--block btn--sm" href="tel:<?php echo esc_attr( fb_tel() ); ?>">
        <?php echo fb_icon( 'phone', 16 ); ?> <?php echo esc_html( fb_field( 'phone' ) ); ?>
      </a>
    </div>
  </aside>

  <main class="shop-main">
    <?php if ( have_posts() ) : ?>
      <div class="shop-toolbar">
        <span class="result-count">
          <?php
          $fb_total = (int) $GLOBALS['wp_query']->found_posts;
          printf( wp_kses_post( 'Hiển thị <strong>%d</strong> sản phẩm' ), $fb_total );
          ?>
        </span>
        <label class="shop-sort">
          Sắp xếp:
          <?php $fb_ob = isset( $_GET['orderby'] ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : ''; ?>
          <select aria-label="Sắp xếp sản phẩm">
            <option value="" <?php selected( $fb_ob, '' ); ?>>Mới nhất</option>
            <option value="title" <?php selected( $fb_ob, 'title' ); ?>>Tên A → Z</option>
            <option value="price" <?php selected( $fb_ob, 'price' ); ?>>Giá thấp → cao</option>
            <option value="price-desc" <?php selected( $fb_ob, 'price-desc' ); ?>>Giá cao → thấp</option>
          </select>
        </label>
      </div>

      <div class="product-grid product-grid--3">
        <?php while ( have_posts() ) : the_post(); ?>
          <?php get_template_part( 'content', 'product' ); ?>
        <?php endwhile; ?>
      </div>

      <?php
      the_posts_pagination( array(
        'mid_size'  => 1,
        'prev_text' => '‹ Trước',
        'next_text' => 'Sau ›',
        'class'     => 'pagination',
      ) );
      ?>
    <?php else : ?>
      <div class="empty-state empty-state--lg">
        <?php echo fb_icon( 'sofa', 64 ); ?>
        <h3>Chưa có sản phẩm</h3>
        <p>Danh mục này hiện chưa có sản phẩm nào.<br>Mời bạn xem các bộ sưu tập khác hoặc liên hệ để được tư vấn riêng.</p>
        <div class="empty-state__cta">
          <a class="btn btn--primary btn--sm" href="<?php echo esc_url( get_post_type_archive_link( 'product' ) ); ?>">Xem tất cả sản phẩm</a>
          <a class="btn btn--outline btn--sm" href="<?php echo esc_url( home_url( '/danh-muc/' ) ); ?>">Khám phá danh mục</a>
        </div>
      </div>
    <?php endif; ?>
  </main>
</div>

<?php
get_footer( 'shop' );
