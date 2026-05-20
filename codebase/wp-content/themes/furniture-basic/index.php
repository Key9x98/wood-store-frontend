<?php
/**
 * Index — Furniture Basic.
 * Fallback chung: kết quả tìm kiếm, danh sách bài viết…
 */
defined( 'ABSPATH' ) || exit;
get_header();
fb_breadcrumb();

if ( is_search() ) {
  $fb_head_title = 'Kết quả tìm kiếm';
  $fb_head_sub   = sprintf( 'Từ khoá: “%s” — %d kết quả', get_search_query(), (int) $GLOBALS['wp_query']->found_posts );
} else {
  $fb_head_title = is_home() ? ( single_post_title( '', false ) ?: 'Bài viết mới' ) : 'Nội dung';
  $fb_head_sub   = 'Tin tức, mẹo chọn và bảo quản đồ gỗ.';
}
?>
<div class="page-head">
  <div class="container">
    <h1><?php echo esc_html( $fb_head_title ); ?></h1>
    <p><?php echo esc_html( $fb_head_sub ); ?></p>
  </div>
</div>

<div class="container" style="padding-top:48px;padding-bottom:48px;">
  <?php if ( have_posts() ) : ?>

    <?php if ( is_search() ) : ?>
      <div class="product-grid product-grid--3">
        <?php while ( have_posts() ) : the_post(); ?>
          <?php
          if ( 'product' === get_post_type() ) {
            get_template_part( 'content', 'product' );
          } else {
            ?>
            <article class="product-card">
              <a class="product-card__media" href="<?php the_permalink(); ?>"><?php fb_product_thumb( 'fb-product' ); ?></a>
              <div class="product-card__body">
                <h3 class="product-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                <p class="product-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 16 ) ); ?></p>
              </div>
            </article>
            <?php
          }
          ?>
        <?php endwhile; ?>
      </div>
    <?php else : ?>
      <div class="post-list">
        <?php while ( have_posts() ) : the_post(); ?>
          <article class="post-card">
            <a class="post-card__thumb" href="<?php the_permalink(); ?>">
              <?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'medium_large', array( 'loading' => 'lazy' ) ); } ?>
            </a>
            <div class="post-card__body">
              <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
              <p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
            </div>
          </article>
        <?php endwhile; ?>
      </div>
    <?php endif; ?>

    <?php
    the_posts_pagination( array(
      'mid_size'  => 1,
      'prev_text' => '‹ Trước',
      'next_text' => 'Sau ›',
      'class'     => 'pagination',
    ) );
    ?>

  <?php else : ?>
    <div class="empty-state">
      <?php echo fb_icon( 'search', 44 ); ?>
      <h3>Không tìm thấy kết quả</h3>
      <p>Hãy thử từ khoá khác, hoặc xem toàn bộ sản phẩm của chúng tôi.</p>
      <p><a class="btn btn--primary btn--sm" href="<?php echo esc_url( get_post_type_archive_link( 'product' ) ); ?>">Xem tất cả sản phẩm</a></p>
    </div>
  <?php endif; ?>
</div>

<?php
get_footer();
