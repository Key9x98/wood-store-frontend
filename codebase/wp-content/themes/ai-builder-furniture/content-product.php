<?php
defined('ABSPATH') || exit;

/** @var WC_Product|null $product */
global $product;
if (!$product || !is_a($product, 'WC_Product')) {
    return;
}
$salePercent = aib_furniture_sale_percent($product);
?>
<li <?php wc_product_class('aib-product-card', $product); ?>>
  <a class="aib-product-card__media" href="<?php echo esc_url(get_permalink($product->get_id())); ?>">
    <?php
    echo $product->get_image('woocommerce_thumbnail');
    if ($salePercent > 0): ?>
      <span class="aib-badge aib-badge--sale">-<?php echo esc_html((string) $salePercent); ?>%</span>
    <?php endif; ?>
  </a>
  <div class="aib-product-card__body">
    <?php
    $wood_terms = get_the_terms($product->get_id(), 'pa_wood');
    if (is_array($wood_terms) && !empty($wood_terms)): ?>
      <p class="aib-product-card__wood">
        <?php echo esc_html(reset($wood_terms)->name); ?>
      </p>
    <?php endif; ?>
    <h3 class="aib-product-card__title">
      <a href="<?php echo esc_url(get_permalink($product->get_id())); ?>">
        <?php echo esc_html($product->get_name()); ?>
      </a>
    </h3>
    <div class="aib-product-card__price"><?php echo $product->get_price_html(); ?></div>
  </div>
</li>
