<?php
/**
 * Single product — Furniture Basic.
 * Thiết kế theo mẫu dogocuongnga.com
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

  // Meta fields mở rộng
  $fb_sku       = get_post_meta( get_the_ID(), '_fb_sku', true );
  $fb_material  = get_post_meta( get_the_ID(), '_fb_material', true );
  $fb_warranty  = get_post_meta( get_the_ID(), '_fb_warranty', true );
  $fb_colors    = get_post_meta( get_the_ID(), '_fb_colors', true );
  $fb_sizes     = get_post_meta( get_the_ID(), '_fb_sizes', true );

  // Lượt xem (tăng mỗi lần load)
  $fb_views = (int) get_post_meta( get_the_ID(), '_fb_views', true );
  update_post_meta( get_the_ID(), '_fb_views', $fb_views + 1 );
  ?>
  <div class="container single-product">
    <div class="product-detail">

      <?php
      // Thu thập tất cả ảnh: ảnh chính + gallery phụ
      $fb_gallery_ids   = get_post_meta( get_the_ID(), '_fb_gallery', true );
      $fb_gallery_ids   = is_array( $fb_gallery_ids ) ? array_filter( array_map( 'intval', $fb_gallery_ids ) ) : array();
      $fb_has_thumbnail = has_post_thumbnail();
      $fb_all_images    = array();

      if ( $fb_has_thumbnail ) {
        $fb_all_images[] = array(
          'url'   => get_the_post_thumbnail_url( get_the_ID(), 'fb-product-lg' ),
          'thumb' => get_the_post_thumbnail_url( get_the_ID(), 'thumbnail' ),
          'alt'   => esc_attr( get_the_title() ),
        );
      }
      foreach ( $fb_gallery_ids as $fb_gid ) {
        $fb_full  = wp_get_attachment_image_url( $fb_gid, 'fb-product-lg' );
        $fb_thumb = wp_get_attachment_image_url( $fb_gid, 'thumbnail' );
        if ( $fb_full ) {
          $fb_all_images[] = array(
            'url'   => $fb_full,
            'thumb' => $fb_thumb ?: $fb_full,
            'alt'   => esc_attr( get_post_meta( $fb_gid, '_wp_attachment_image_alt', true ) ?: get_the_title() ),
          );
        }
      }
      $fb_img_count = count( $fb_all_images );
      ?>
      <div class="product-gallery" id="fb-gallery" data-count="<?php echo (int) $fb_img_count; ?>">

        <!-- Main image area -->
        <div class="pg-main-wrap">
          <div class="pg-main" id="fb-pg-main">
            <?php if ( $fb_img_count > 0 ) : ?>
              <?php foreach ( $fb_all_images as $fb_idx => $fb_img ) : ?>
                <div class="pg-slide<?php echo 0 === $fb_idx ? ' is-active' : ''; ?>" data-index="<?php echo (int) $fb_idx; ?>">
                  <img src="<?php echo esc_url( $fb_img['url'] ); ?>"
                       alt="<?php echo $fb_img['alt']; ?>"
                       class="pg-slide__img"
                       loading="<?php echo $fb_idx > 0 ? 'lazy' : 'eager'; ?>"
                       draggable="false">
                </div>
              <?php endforeach; ?>
            <?php else : ?>
              <div class="pg-slide is-active">
                <span class="thumb-fallback"><?php echo fb_icon( 'sofa', 72 ); ?></span>
              </div>
            <?php endif; ?>
          </div>

          <!-- Badge sale -->
          <?php if ( $fb_pct > 0 ) : ?>
            <span class="badge badge--sale">-<?php echo esc_html( $fb_pct ); ?>%</span>
          <?php endif; ?>

          <!-- Nút điều hướng trái/phải (khi có > 1 ảnh) -->
          <?php if ( $fb_img_count > 1 ) : ?>
            <button class="pg-nav pg-nav--prev" aria-label="Ảnh trước" id="fb-pg-prev">
              <?php echo fb_icon( 'chevron', 20 ); ?>
            </button>
            <button class="pg-nav pg-nav--next" aria-label="Ảnh sau" id="fb-pg-next">
              <?php echo fb_icon( 'chevron', 20 ); ?>
            </button>
          <?php endif; ?>

          <!-- Thanh action toolbar -->
          <?php if ( $fb_img_count > 0 ) : ?>
            <div class="pg-toolbar">
              <button class="pg-tool-btn" id="fb-pg-zoom-in" aria-label="Phóng to">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
              </button>
              <button class="pg-tool-btn" id="fb-pg-zoom-out" aria-label="Thu nhỏ">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
              </button>
              <button class="pg-tool-btn" id="fb-pg-reset" aria-label="Đặt lại zoom">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
              </button>
              <button class="pg-tool-btn pg-tool-btn--expand" id="fb-pg-fullscreen" aria-label="Xem toàn màn hình">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="15 3 21 3 21 9"/><polyline points="9 21 3 21 3 15"/><line x1="21" y1="3" x2="14" y2="10"/><line x1="3" y1="21" x2="10" y2="14"/></svg>
              </button>
            </div>
            <?php if ( $fb_img_count > 1 ) : ?>
              <div class="pg-counter" id="fb-pg-counter">1 / <?php echo (int) $fb_img_count; ?></div>
            <?php endif; ?>
          <?php endif; ?>
        </div>

        <!-- Thumbnails -->
        <?php if ( $fb_img_count > 1 ) : ?>
          <div class="pg-thumbs" id="fb-pg-thumbs" role="tablist" aria-label="Ảnh sản phẩm">
            <?php foreach ( $fb_all_images as $fb_idx => $fb_img ) : ?>
              <button class="pg-thumb<?php echo 0 === $fb_idx ? ' is-active' : ''; ?>"
                      data-index="<?php echo (int) $fb_idx; ?>"
                      role="tab"
                      aria-selected="<?php echo 0 === $fb_idx ? 'true' : 'false'; ?>"
                      aria-label="Ảnh <?php echo (int) ( $fb_idx + 1 ); ?>">
                <img src="<?php echo esc_url( $fb_img['thumb'] ); ?>"
                     alt="<?php echo $fb_img['alt']; ?>"
                     loading="lazy"
                     draggable="false">
              </button>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

      </div>

      <!-- Lightbox overlay -->
      <?php if ( $fb_img_count > 0 ) : ?>
        <div class="pg-lightbox" id="fb-lightbox" role="dialog" aria-modal="true" aria-label="Xem ảnh lớn" hidden>
          <div class="pg-lightbox__backdrop" id="fb-lb-backdrop"></div>
          <div class="pg-lightbox__box">
            <button class="pg-lb-close" id="fb-lb-close" aria-label="Đóng">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
            <?php if ( $fb_img_count > 1 ) : ?>
              <button class="pg-lb-nav pg-lb-nav--prev" id="fb-lb-prev" aria-label="Ảnh trước">
                <?php echo fb_icon( 'chevron', 24 ); ?>
              </button>
              <button class="pg-lb-nav pg-lb-nav--next" id="fb-lb-next" aria-label="Ảnh sau">
                <?php echo fb_icon( 'chevron', 24 ); ?>
              </button>
            <?php endif; ?>
            <div class="pg-lightbox__img-wrap" id="fb-lb-img-wrap">
              <img src="" alt="" id="fb-lb-img" draggable="false">
            </div>
            <?php if ( $fb_img_count > 1 ) : ?>
              <div class="pg-lightbox__counter"><?php esc_html_e( 'Ảnh', 'furniture-basic' ); ?> <span id="fb-lb-cur">1</span> / <?php echo (int) $fb_img_count; ?></div>
            <?php endif; ?>
          </div>
        </div>

        <!-- JSON data ảnh cho JS -->
        <script id="fb-gallery-data" type="application/json">
          <?php
          $fb_json_imgs = array_map( function( $img ) {
            return array( 'url' => $img['url'], 'thumb' => $img['thumb'], 'alt' => $img['alt'] );
          }, $fb_all_images );
          echo wp_json_encode( $fb_json_imgs );
          ?>
        </script>
      <?php endif; ?>

      <!-- ===== PRODUCT SUMMARY ===== -->
      <div class="product-summary">

        <!-- Mã sản phẩm -->
        <?php if ( $fb_sku ) : ?>
          <div class="product-sku">
            <span class="label">Mã sản phẩm:</span>
            <span class="value"><?php echo esc_html( $fb_sku ); ?></span>
          </div>
        <?php endif; ?>

        <!-- Tên sản phẩm -->
        <h1><?php the_title(); ?></h1>

        <!-- Giá + badge giảm -->
        <div class="product-price-box">
          <span class="label">Giá:</span>
          <?php echo wp_kses_post( fb_price_html() ); ?>
          <?php if ( $fb_pct > 0 ) : ?>
            <span class="discount-badge">-<?php echo esc_html( $fb_pct ); ?>%</span>
          <?php endif; ?>
        </div>

        <!-- Lượt xem -->
        <div class="product-views">
          <?php echo fb_icon( 'eye', 16 ); ?>
          <span>Lượt xem: <strong><?php echo number_format( $fb_views + 1, 0, ',', '.' ); ?></strong></span>
        </div>

        <!-- Mô tả ngắn / Thông số -->
        <div class="product-specs">
          <?php if ( has_excerpt() ) : ?>
            <div class="spec-desc"><?php echo wp_kses_post( wpautop( get_the_excerpt() ) ); ?></div>
          <?php endif; ?>

          <ul class="spec-list">
            <?php if ( $fb_material ) : ?>
              <li><span class="spec-label">Chất liệu:</span> <?php echo esc_html( $fb_material ); ?></li>
            <?php endif; ?>
            <?php if ( $fb_warranty ) : ?>
              <li><span class="spec-label">Bảo hành:</span> <?php echo esc_html( $fb_warranty ); ?></li>
            <?php endif; ?>
            <li><span class="spec-label">Tình trạng:</span> Sản phẩm chụp thực tế tại showroom</li>
            <li><span class="spec-label">Dịch vụ:</span> Chuyên sỉ, lẻ - Nhận đặt hàng theo yêu cầu</li>
          </ul>
        </div>

        <!-- Hotline nổi bật -->
        <div class="product-hotline">
          <span class="hotline-label">Hotline:</span>
          <a href="tel:<?php echo esc_attr( fb_tel() ); ?>" class="hotline-number"><?php echo esc_html( fb_field( 'phone' ) ); ?></a>
        </div>

        <!-- Chi nhánh -->
        <div class="product-branch">
          <span class="branch-label">Chi nhánh:</span>
          <span class="branch-value"><?php echo esc_html( fb_field( 'address' ) ); ?></span>
        </div>

        <!-- Màu sắc (nếu có) -->
        <?php
        $fb_color_arr = $fb_colors ? array_map( 'trim', explode( ',', $fb_colors ) ) : array();
        if ( ! empty( $fb_color_arr ) ) :
        ?>
          <div class="product-variant">
            <span class="variant-label">Màu sắc: <strong><?php echo esc_html( strtoupper( $fb_color_arr[0] ) ); ?></strong></span>
            <div class="variant-options" id="fb-color-options">
              <?php foreach ( $fb_color_arr as $idx => $color ) : ?>
                <button type="button" class="variant-chip<?php echo 0 === $idx ? ' is-active' : ''; ?>" data-value="<?php echo esc_attr( $color ); ?>">
                  <?php echo esc_html( $color ); ?>
                </button>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <!-- Kích thước (nếu có) -->
        <?php
        $fb_size_arr = $fb_sizes ? array_map( 'trim', explode( ',', $fb_sizes ) ) : array();
        if ( ! empty( $fb_size_arr ) ) :
        ?>
          <div class="product-variant">
            <span class="variant-label">Kích thước: <strong><?php echo esc_html( strtoupper( $fb_size_arr[0] ) ); ?></strong></span>
            <div class="variant-options" id="fb-size-options">
              <?php foreach ( $fb_size_arr as $idx => $size ) : ?>
                <button type="button" class="variant-chip<?php echo 0 === $idx ? ' is-active' : ''; ?>" data-value="<?php echo esc_attr( $size ); ?>">
                  <?php echo esc_html( $size ); ?>
                </button>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <!-- Số lượng -->
        <div class="product-quantity">
          <span class="qty-label">Số lượng:</span>
          <div class="qty-control">
            <button type="button" class="qty-btn qty-minus" aria-label="Giảm số lượng">-</button>
            <input type="number" class="qty-input" value="1" min="1" max="99" readonly>
            <button type="button" class="qty-btn qty-plus" aria-label="Tăng số lượng">+</button>
          </div>
        </div>

        <!-- Nút CTA -->
        <div class="product-cta">
          <a class="btn btn--primary btn--lg cta-buy" href="tel:<?php echo esc_attr( fb_tel() ); ?>">
            <?php echo fb_icon( 'phone', 18 ); ?> Mua Ngay
          </a>
          <?php if ( $fb_zalo ) : ?>
            <a class="btn btn--outline btn--lg cta-cart" href="<?php echo esc_url( $fb_zalo ); ?>" target="_blank" rel="noopener">
              <?php echo fb_icon( 'chat', 18 ); ?> Thêm Vào Giỏ Hàng
            </a>
          <?php else : ?>
            <a class="btn btn--outline btn--lg cta-cart" href="mailto:<?php echo esc_attr( fb_field( 'email' ) ); ?>?subject=<?php echo esc_attr( rawurlencode( 'Đặt hàng: ' . get_the_title() ) ); ?>">
              <?php echo fb_icon( 'mail', 18 ); ?> Thêm Vào Giỏ Hàng
            </a>
          <?php endif; ?>
        </div>

        <!-- Box để lại SĐT -->
        <div class="product-callback">
          <div class="callback-icon">
            <?php echo fb_icon( 'headset', 28 ); ?>
          </div>
          <div class="callback-content">
            <p class="callback-title">Hãy để lại số ĐT</p>
            <p class="callback-desc">chúng tôi sẽ gọi ngay tư vấn cho bạn <strong>Miễn Phí</strong></p>
          </div>
          <form class="callback-form" id="fb-callback-form">
            <input type="tel" name="phone" placeholder="Nhập số điện thoại của bạn" required>
            <button type="submit" class="btn btn--primary">GỬI</button>
          </form>
        </div>

        <!-- Nút gọi hotline nổi bật -->
        <a href="tel:<?php echo esc_attr( fb_tel() ); ?>" class="product-call-btn">
          <?php echo fb_icon( 'phone', 20 ); ?>
          <span>GỌI NGAY: <?php echo esc_html( fb_field( 'phone' ) ); ?></span>
        </a>

        <!-- Cam kết -->
        <ul class="product-assure">
          <li><?php echo fb_icon( 'truck', 19 ); ?> <span>Giao hàng &amp; lắp đặt tận nơi</span></li>
          <li><?php echo fb_icon( 'shield', 19 ); ?> <span>Cam kết gỗ tự nhiên, bảo hành dài hạn</span></li>
          <li><?php echo fb_icon( 'ruler', 19 ); ?> <span>Nhận đóng theo kích thước yêu cầu</span></li>
          <li><?php echo fb_icon( 'headset', 19 ); ?> <span>Hỗ trợ tư vấn miễn phí: <?php echo esc_html( fb_field( 'phone' ) ); ?></span></li>
        </ul>

        <!-- Danh mục -->
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

    <!-- ===== MÔ TẢ SẢN PHẨM ===== -->
    <?php if ( get_the_content() ) : ?>
      <div class="product-description-section">
        <h2 class="section-title">Mô tả sản phẩm</h2>
        <div class="product-description"><?php the_content(); ?></div>
      </div>
    <?php endif; ?>

    <!-- ===== SẢN PHẨM LIÊN QUAN ===== -->
    <?php
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
      <section class="related-products">
        <h2 class="section-title">Sản phẩm liên quan</h2>
        <div class="product-grid">
          <?php while ( $fb_related->have_posts() ) : $fb_related->the_post(); ?>
            <?php get_template_part( 'content', 'product' ); ?>
          <?php endwhile; ?>
        </div>
      </section>
      <?php
      wp_reset_postdata();
    endif;
    ?>
  </div>
  <?php
endwhile;

get_footer();
