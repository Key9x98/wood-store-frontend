<?php
/**
 * Single product — Furniture Basic.
 */
defined( 'ABSPATH' ) || exit;
get_header();
fb_breadcrumb();

while ( have_posts() ) :
  the_post();

  $fb_price = fb_get_price();
  $fb_pct   = fb_sale_percent();
  $fb_terms = get_the_terms( get_the_ID(), 'product_cat' );
  $fb_zalo  = fb_field( 'zalo' );
  ?>
  <div class="container single-product">
    <div class="product-detail">

      <div class="product-gallery">
        <?php
        if ( has_post_thumbnail() ) {
          the_post_thumbnail( 'fb-product-lg' );
        } else {
          echo '<span class="thumb-fallback">' . fb_icon( 'sofa', 72 ) . '</span>';
        }
        if ( $fb_pct > 0 ) :
          ?><span class="badge badge--sale">-<?php echo esc_html( $fb_pct ); ?>%</span><?php
        endif;
        ?>
      </div>

      <div class="product-summary">
        <?php if ( $fb_terms && ! is_wp_error( $fb_terms ) ) : ?>
          <a class="product-cat-line" href="<?php echo esc_url( get_term_link( $fb_terms[0] ) ); ?>">
            <?php echo esc_html( $fb_terms[0]->name ); ?>
          </a>
        <?php endif; ?>

        <h1><?php the_title(); ?></h1>

        <div class="product-rating">
          <?php for ( $i = 0; $i < 5; $i++ ) { echo fb_icon( 'star', 17 ); } ?>
          <span>(Sản phẩm gỗ tự nhiên cao cấp)</span>
        </div>

        <div class="product-price-box">
          <?php echo wp_kses_post( fb_price_html() ); ?>
          <?php if ( $fb_pct > 0 ) : ?>
            <span class="save-tag">Tiết kiệm <?php echo esc_html( fb_format_price( $fb_price['regular'] - $fb_price['sale'] ) ); ?></span>
          <?php endif; ?>
        </div>

        <?php if ( has_excerpt() ) : ?>
          <p class="lead"><?php echo esc_html( get_the_excerpt() ); ?></p>
        <?php endif; ?>

        <div class="product-actions">
          <a class="btn btn--primary btn--lg" href="tel:<?php echo esc_attr( fb_tel() ); ?>">
            <?php echo fb_icon( 'phone', 18 ); ?> Gọi đặt hàng
          </a>
          <?php if ( $fb_zalo ) : ?>
            <a class="btn btn--gold btn--lg" href="<?php echo esc_url( $fb_zalo ); ?>" target="_blank" rel="noopener">
              <?php echo fb_icon( 'chat', 18 ); ?> Tư vấn qua Zalo
            </a>
          <?php else : ?>
            <a class="btn btn--outline btn--lg" href="mailto:<?php echo esc_attr( fb_field( 'email' ) ); ?>?subject=<?php echo esc_attr( rawurlencode( 'Đặt hàng: ' . get_the_title() ) ); ?>">
              <?php echo fb_icon( 'mail', 18 ); ?> Gửi yêu cầu
            </a>
          <?php endif; ?>
        </div>

        <ul class="product-assure">
          <li><?php echo fb_icon( 'truck', 19 ); ?> <span>Giao hàng &amp; lắp đặt tận nơi</span></li>
          <li><?php echo fb_icon( 'shield', 19 ); ?> <span>Cam kết gỗ tự nhiên, bảo hành dài hạn</span></li>
          <li><?php echo fb_icon( 'ruler', 19 ); ?> <span>Nhận đóng theo kích thước yêu cầu</span></li>
          <li><?php echo fb_icon( 'headset', 19 ); ?> <span>Hỗ trợ tư vấn miễn phí: <?php echo esc_html( fb_field( 'phone' ) ); ?></span></li>
        </ul>

        <?php if ( $fb_terms && ! is_wp_error( $fb_terms ) ) : ?>
          <p class="product-meta">Danh mục:
            <?php
            $fb_links = array();
            foreach ( $fb_terms as $fb_t ) {
              $fb_links[] = '<a href="' . esc_url( get_term_link( $fb_t ) ) . '">' . esc_html( $fb_t->name ) . '</a>';
            }
            echo implode( ', ', $fb_links );
            ?>
          </p>
        <?php endif; ?>
      </div>
    </div>

    <?php if ( get_the_content() ) : ?>
      <div class="product-tabs">
        <h2>Mô tả chi tiết</h2>
        <div class="product-description"><?php the_content(); ?></div>
      </div>
    <?php endif; ?>

    <?php
    // ----- Sản phẩm liên quan -----
    $fb_rel_args = array(
      'post_type'           => 'product',
      'posts_per_page'      => 4,
      'post__not_in'        => array( get_the_ID() ),
      'no_found_rows'       => true,
      'ignore_sticky_posts' => true,
      'orderby'             => 'rand',
    );
    if ( $fb_terms && ! is_wp_error( $fb_terms ) ) {
      $fb_rel_args['tax_query'] = array( array(
        'taxonomy' => 'product_cat',
        'field'    => 'term_id',
        'terms'    => wp_list_pluck( $fb_terms, 'term_id' ),
      ) );
    }
    $fb_related = new WP_Query( $fb_rel_args );
    if ( $fb_related->have_posts() ) :
      ?>
      <div class="product-tabs">
        <h2>Sản phẩm liên quan</h2>
        <div class="product-grid" style="margin-top:24px;">
          <?php while ( $fb_related->have_posts() ) : $fb_related->the_post(); ?>
            <?php get_template_part( 'content', 'product' ); ?>
          <?php endwhile; ?>
        </div>
      </div>
      <?php
      wp_reset_postdata();
    endif;
    ?>
  </div>
  <?php
endwhile;

get_footer();
