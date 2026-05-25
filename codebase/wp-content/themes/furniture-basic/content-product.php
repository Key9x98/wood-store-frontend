<?php
/**
 * Product card — dùng trong các vòng lặp WP_Query post_type=product.
 * Gọi qua get_template_part( 'content', 'product' ).
 */
defined( 'ABSPATH' ) || exit;

$fb_pct   = fb_sale_percent();
$fb_badge = get_post_meta( get_the_ID(), '_fb_badge', true );
$fb_terms = get_the_terms( get_the_ID(), 'product_cat' );
?>
<article <?php post_class( 'product-card' ); ?>>
  <a class="product-card__media" href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr( get_the_title() ); ?>">
    <?php fb_product_thumb( 'fb-product' ); ?>
    <span class="product-card__media-gradient" aria-hidden="true"></span>
    <?php if ( $fb_pct > 0 ) : ?>
      <span class="badge badge--sale">-<?php echo esc_html( $fb_pct ); ?>%</span>
    <?php endif; ?>
    <?php if ( $fb_badge ) : ?>
      <span class="badge badge--tag"><?php echo esc_html( $fb_badge ); ?></span>
    <?php endif; ?>
    <span class="product-card__view"><?php echo fb_icon( 'arrow', 16 ); ?> Xem chi tiết</span>
  </a>
  <div class="product-card__body">
    <?php if ( $fb_terms && ! is_wp_error( $fb_terms ) ) : ?>
      <span class="product-card__cat"><?php echo esc_html( $fb_terms[0]->name ); ?></span>
    <?php endif; ?>
    <h3 class="product-card__title">
      <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
    </h3>
    <div class="product-card__price"><?php echo wp_kses_post( fb_price_html() ); ?></div>
    <a class="product-card__cta" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
      Xem chi tiết
      <?php echo fb_icon( 'arrow', 14 ); ?>
    </a>
  </div>
</article>
