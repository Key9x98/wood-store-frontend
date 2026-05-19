<?php
/**
 * Footer — Furniture Basic.
 */
defined( 'ABSPATH' ) || exit;
?>
</div><!-- #content -->

<footer class="site-footer" id="lien-he">
  <div class="container footer-grid">

    <div class="footer-col">
      <div class="footer-brand">
        <span class="brand__mark"><?php echo fb_icon( 'sofa', 24 ); ?></span>
        <strong><?php echo esc_html( fb_field( 'shop_name' ) ); ?></strong>
      </div>
      <p class="footer-about"><?php echo esc_html( fb_field( 'intro' ) ); ?></p>
      <div class="footer-social">
        <?php if ( fb_field( 'facebook' ) ) : ?>
          <a href="<?php echo esc_url( fb_field( 'facebook' ) ); ?>" target="_blank" rel="noopener" aria-label="Facebook"><?php echo fb_icon( 'facebook', 18 ); ?></a>
        <?php endif; ?>
        <?php if ( fb_field( 'zalo' ) ) : ?>
          <a href="<?php echo esc_url( fb_field( 'zalo' ) ); ?>" target="_blank" rel="noopener" aria-label="Zalo"><?php echo fb_icon( 'chat', 18 ); ?></a>
        <?php endif; ?>
        <a href="tel:<?php echo esc_attr( fb_tel() ); ?>" aria-label="Gọi điện"><?php echo fb_icon( 'phone', 18 ); ?></a>
        <a href="mailto:<?php echo esc_attr( fb_field( 'email' ) ); ?>" aria-label="Email"><?php echo fb_icon( 'mail', 18 ); ?></a>
      </div>
    </div>

    <div class="footer-col">
      <h4>Khám phá</h4>
      <ul class="footer-links">
        <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Trang chủ</a></li>
        <li><a href="<?php echo esc_url( get_post_type_archive_link( 'product' ) ); ?>">Tất cả sản phẩm</a></li>
        <li><a href="#lien-he">Về chúng tôi</a></li>
        <li><a href="#lien-he">Liên hệ</a></li>
      </ul>
    </div>

    <div class="footer-col">
      <h4>Danh mục</h4>
      <ul class="footer-links">
        <?php
        $cats = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => true, 'number' => 6 ) );
        if ( $cats && ! is_wp_error( $cats ) ) :
          foreach ( $cats as $cat ) : ?>
            <li><a href="<?php echo esc_url( get_term_link( $cat ) ); ?>"><?php echo esc_html( $cat->name ); ?></a></li>
          <?php endforeach;
        else : ?>
          <li><a href="<?php echo esc_url( get_post_type_archive_link( 'product' ) ); ?>">Sản phẩm gỗ</a></li>
        <?php endif; ?>
      </ul>
    </div>

    <div class="footer-col">
      <h4>Liên hệ</h4>
      <ul class="footer-contact">
        <li><?php echo fb_icon( 'pin', 18 ); ?> <span><?php echo esc_html( fb_field( 'address' ) ); ?></span></li>
        <li><?php echo fb_icon( 'phone', 18 ); ?> <a href="tel:<?php echo esc_attr( fb_tel() ); ?>"><?php echo esc_html( fb_field( 'phone' ) ); ?></a></li>
        <li><?php echo fb_icon( 'mail', 18 ); ?> <a href="mailto:<?php echo esc_attr( fb_field( 'email' ) ); ?>"><?php echo esc_html( fb_field( 'email' ) ); ?></a></li>
        <li><?php echo fb_icon( 'clock', 18 ); ?> <span><?php echo esc_html( fb_field( 'working_hours' ) ); ?></span></li>
      </ul>
    </div>

  </div>

  <div class="footer-bottom">
    <div class="container">
      <span>&copy; <?php echo esc_html( date( 'Y' ) ); ?> <?php echo esc_html( fb_field( 'shop_name' ) ); ?>. Bảo lưu mọi quyền.</span>
      <span class="credit">Thiết kế nội thất gỗ tự nhiên · Giao lắp toàn quốc</span>
    </div>
  </div>
</footer>

<button class="to-top" aria-label="Lên đầu trang"><?php echo fb_icon( 'arrow', 20 ); ?></button>

<?php wp_footer(); ?>
</body>
</html>
