<?php
/**
 * Template: Trang "Tin tức" — liệt kê bài viết (post_type=post).
 *
 * Auto-active cho WP Page slug `tin-tuc` (page-{slug}.php template hierarchy).
 * Page content (nếu có) hiển thị ở phần intro phía trên grid bài viết —
 * cho phép user thêm copy tuỳ chỉnh từ admin mà không phải sửa code.
 */
defined( 'ABSPATH' ) || exit;

get_header();

// Page content do user edit trong wp-admin (intro). Render trước grid bài viết.
the_post();
$fb_page_content = trim( wp_strip_all_tags( (string) get_the_content() ) );
$fb_page_title   = get_the_title();

// Query bài viết, hỗ trợ pagination.
$fb_paged = max( 1, (int) ( get_query_var( 'paged' ) ?: get_query_var( 'page' ) ) );
$fb_news  = new WP_Query( array(
  'post_type'      => 'post',
  'post_status'    => 'publish',
  'posts_per_page' => 9,
  'paged'          => $fb_paged,
  'ignore_sticky_posts' => true,
) );
$fb_total = (int) $fb_news->found_posts;
?>

<!-- ===== HERO (compact — đồng nhất với /san-pham/) ===== -->
<section class="page-hero page-hero--compact">
  <div class="container">
    <nav class="breadcrumb breadcrumb--on-hero" aria-label="Đường dẫn">
      <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Trang chủ</a>
      <span class="breadcrumb__sep">›</span>
      <span><?php echo esc_html( $fb_page_title ); ?></span>
    </nav>
    <div class="page-hero__title-row">
      <h1><?php echo esc_html( $fb_page_title ); ?></h1>
      <?php if ( $fb_total > 0 ) : ?>
        <span class="page-hero__count"><?php echo (int) $fb_total; ?> bài viết</span>
      <?php endif; ?>
    </div>
    <?php if ( $fb_page_content !== '' ) : ?>
      <p><?php echo esc_html( wp_trim_words( $fb_page_content, 22 ) ); ?></p>
    <?php endif; ?>
  </div>
</section>

<div class="container news-page">
  <?php if ( $fb_news->have_posts() ) : ?>
    <div class="post-list post-list--horizontal">
      <?php while ( $fb_news->have_posts() ) : $fb_news->the_post(); ?>
        <article class="post-card">
          <a class="post-card__thumb" href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr( get_the_title() ); ?>">
            <?php
            if ( has_post_thumbnail() ) {
              the_post_thumbnail( 'medium_large', array( 'loading' => 'lazy' ) );
            } elseif ( $fb_inline_img = fb_first_content_image( get_post() ) ) {
              // Ảnh đầu tiên trong post_content — thường đủ để làm hero card.
              echo '<img src="' . esc_url( $fb_inline_img ) . '" alt="' . esc_attr( get_the_title() ) . '" loading="lazy">';
            } else {
              // Branded fallback: chữ in hoa đầu tiên của title trên gradient nâu.
              // Trông như card cover có chủ ý thay vì "ảnh bị thiếu".
              $fb_initial = mb_substr( wp_strip_all_tags( get_the_title() ?: 'N' ), 0, 1, 'UTF-8' );
              ?>
              <span class="post-card__thumb-fallback" aria-hidden="true">
                <span class="post-card__thumb-initial"><?php echo esc_html( mb_strtoupper( $fb_initial, 'UTF-8' ) ); ?></span>
              </span>
              <?php
            }
            ?>
            <span class="post-card__thumb-gradient" aria-hidden="true"></span>
          </a>
          <div class="post-card__body">
            <div class="post-card__meta">
              <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
                <?php echo esc_html( get_the_date( 'd/m/Y' ) ); ?>
              </time>
              <?php
                // Ẩn category placeholder mặc định của WP — slug `uncategorized`
                // hoặc `chua-phan-loai` (Vietnamese install). Khi bạn rename
                // category VÀ đổi slug sang slug có nghĩa (vd `tin-tuc`), chip
                // sẽ tự hiện lại — không phải sửa theme.
                $fb_skip_slugs = array( 'uncategorized', 'chua-phan-loai' );
                $fb_cats = array_filter(
                  (array) get_the_category(),
                  static fn( $c ) => ! in_array( $c->slug, $fb_skip_slugs, true )
                );
                if ( $fb_cats ) :
                  $fb_first_cat = reset( $fb_cats );
              ?>
                <span class="post-card__cat"><?php echo esc_html( $fb_first_cat->name ); ?></span>
              <?php endif; ?>
            </div>
            <h3 class="post-card__title">
              <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
            </h3>
            <?php
              // Excerpt → 28 từ. Nếu post có manual excerpt → ưu tiên (head do user viết);
              // không có → WP tự sinh từ post_content, vẫn cắt đầu (lead) của bài.
              $fb_excerpt = get_the_excerpt();
              if ( $fb_excerpt ) :
            ?>
              <p class="post-card__excerpt"><?php echo esc_html( wp_trim_words( $fb_excerpt, 28 ) ); ?></p>
            <?php endif; ?>
            <a class="post-card__more" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
              Đọc tiếp <?php echo fb_icon( 'arrow', 14 ); ?>
            </a>
          </div>
        </article>
      <?php endwhile; ?>
    </div>

    <?php
    // Pagination tự custom vì $fb_news là phụ query, không phải $wp_query global.
    $fb_pag = paginate_links( array(
      'base'      => add_query_arg( 'paged', '%#%' ),
      'format'    => '?paged=%#%',
      'current'   => $fb_paged,
      'total'     => (int) $fb_news->max_num_pages,
      'mid_size'  => 1,
      'prev_text' => '‹ Trước',
      'next_text' => 'Sau ›',
      'type'      => 'plain',
    ) );
    if ( $fb_pag ) : ?>
      <nav class="pagination" aria-label="Phân trang">
        <div class="nav-links"><?php echo wp_kses_post( $fb_pag ); ?></div>
      </nav>
    <?php endif; ?>

  <?php else : ?>
    <div class="empty-state empty-state--lg">
      <?php echo fb_icon( 'search', 64 ); ?>
      <h3>Chưa có bài viết</h3>
      <p>Xưởng sẽ sớm chia sẻ những bài viết hữu ích về nghề mộc, mẫu mới và mẹo chọn đồ gỗ.<br>Mời bạn quay lại sau.</p>
      <div class="empty-state__cta">
        <a class="btn btn--primary btn--sm" href="<?php echo esc_url( get_post_type_archive_link( 'product' ) ); ?>">Xem sản phẩm</a>
        <a class="btn btn--outline btn--sm" href="<?php echo esc_url( home_url( '/' ) ); ?>">Về trang chủ</a>
      </div>
    </div>
  <?php endif;
  wp_reset_postdata();
  ?>
</div>

<?php
get_footer();
