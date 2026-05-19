<?php
/**
 * Search form — tìm trong sản phẩm.
 */
defined( 'ABSPATH' ) || exit;
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
  <label class="screen-reader-text" for="fb-search">Tìm sản phẩm</label>
  <input type="search" id="fb-search" name="s" placeholder="Tìm tủ, bàn, ghế gỗ…" value="<?php echo esc_attr( get_search_query() ); ?>" />
  <input type="hidden" name="post_type" value="product" />
  <button type="submit" aria-label="Tìm kiếm"><?php echo fb_icon( 'search', 20 ); ?></button>
</form>
