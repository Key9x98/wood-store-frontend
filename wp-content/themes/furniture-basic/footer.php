<?php
/**
 * Footer — Furniture Basic.
 */
?>
<footer class="site-footer">
  <p class="shop"><?php echo esc_html( fb_field( 'shop_name' ) ); ?></p>
  <p class="addr"><?php echo esc_html( fb_field( 'address' ) ); ?></p>
  <p class="addr">☎ <?php echo esc_html( fb_field( 'phone' ) ); ?></p>
  <p class="copy">&copy; <?php echo esc_html( date( 'Y' ) ); ?> · <?php echo esc_html( fb_field( 'shop_name' ) ); ?></p>
</footer>
<?php wp_footer(); ?>
</body>
</html>
