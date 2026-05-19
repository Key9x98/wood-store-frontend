<?php
/**
 * Archive — Furniture Basic.
 * Xử lý: archive sản phẩm (/san-pham), taxonomy danh mục, và archive thường.
 */
defined( 'ABSPATH' ) || exit;
get_header();

$fb_is_shop = is_post_type_archive( 'product' ) || is_tax( 'product_cat' );

if ( $fb_is_shop ) :
  fb_breadcrumb();

  if ( is_tax( 'product_cat' ) ) {
    $fb_title = single_term_title( '', false );
    $fb_desc  = term_description();
  } else {
    $fb_title = 'Tất cả sản phẩm';
    $fb_desc  = '';
  }
  ?>
  <div class="page-head">
    <div class="container">
      <h1><?php echo esc_html( $fb_title ); ?></h1>
      <p><?php echo $fb_desc ? wp_kses_post( $fb_desc ) : 'Đồ gỗ tự nhiên — bền đẹp, giá tận xưởng, giao lắp tận nơi.'; ?></p>
    </div>
  </div>

  <div class="container shop">
    <aside class="shop-sidebar">
      <div class="widget">
        <h3 class="widget__title">Danh mục sản phẩm</h3>
        <ul class="cat-list">
          <li<?php echo is_post_type_archive( 'product' ) ? ' class="is-active"' : ''; ?>>
            <a href="<?php echo esc_url( get_post_type_archive_link( 'product' ) ); ?>">Tất cả sản phẩm</a>
          </li>
          <?php
          $fb_cats   = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => true ) );
          $fb_cur_id = is_tax( 'product_cat' ) ? get_queried_object_id() : 0;
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
            printf( esc_html( '%d sản phẩm' ), $fb_total );
            ?>
          </span>
          <label class="shop-sort">
            Sắp xếp:
            <?php $fb_ob = isset( $_GET['orderby'] ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : ''; ?>
            <select aria-label="Sắp xếp sản phẩm">
              <option value="" <?php selected( $fb_ob, '' ); ?>>Mới nhất</option>
              <option value="title" <?php selected( $fb_ob, 'title' ); ?>>Tên A → Z</option>
              <option value="price_asc" <?php selected( $fb_ob, 'price_asc' ); ?>>Giá thấp → cao</option>
              <option value="price_desc" <?php selected( $fb_ob, 'price_desc' ); ?>>Giá cao → thấp</option>
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
        <div class="empty-state">
          <?php echo fb_icon( 'sofa', 44 ); ?>
          <h3>Chưa có sản phẩm</h3>
          <p>Danh mục này chưa có sản phẩm nào. Vui lòng quay lại sau.</p>
          <p><a class="btn btn--primary btn--sm" href="<?php echo esc_url( get_post_type_archive_link( 'product' ) ); ?>">Xem tất cả sản phẩm</a></p>
        </div>
      <?php endif; ?>
    </main>
  </div>

<?php else : // ----- Archive thường (blog, tag, ngày tháng…) ----- ?>

  <?php fb_breadcrumb(); ?>
  <div class="page-head">
    <div class="container">
      <h1><?php echo esc_html( wp_strip_all_tags( get_the_archive_title() ) ); ?></h1>
      <?php the_archive_description( '<p>', '</p>' ); ?>
    </div>
  </div>

  <div class="container" style="padding-top:48px;padding-bottom:48px;">
    <?php if ( have_posts() ) : ?>
      <div class="post-list">
        <?php while ( have_posts() ) : the_post(); ?>
          <article class="post-card">
            <a class="post-card__thumb" href="<?php the_permalink(); ?>">
              <?php
              if ( has_post_thumbnail() ) {
                the_post_thumbnail( 'medium_large', array( 'loading' => 'lazy' ) );
              }
              ?>
            </a>
            <div class="post-card__body">
              <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
              <p><?php echo esc_html( get_the_excerpt() ); ?></p>
            </div>
          </article>
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
      <div class="empty-state">
        <?php echo fb_icon( 'search', 44 ); ?>
        <h3>Không có nội dung</h3>
        <p>Chưa có bài viết nào trong mục này.</p>
      </div>
    <?php endif; ?>
  </div>

<?php endif; ?>

<?php
get_footer();
