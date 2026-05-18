<?php defined('ABSPATH') || exit; ?>
</main>
<footer class="aib-footer">
  <div class="aib-container aib-footer__cols">
    <div class="aib-footer__col">
      <h4><?php echo aib_field_escaped('shop_name', get_bloginfo('name')); ?></h4>
      <p><?php echo wp_kses_post((string) aib_field('about_short', '')); ?></p>
    </div>
    <div class="aib-footer__col">
      <h4><?php esc_html_e('Liên hệ', 'ai-builder-furniture'); ?></h4>
      <ul>
        <li>📍 <?php echo aib_field_escaped('address'); ?></li>
        <li>☎ <?php echo aib_field_escaped('phone'); ?></li>
        <?php if ($zalo = aib_field('zalo', '')): ?>
          <li>Zalo: <?php echo esc_html((string) $zalo); ?></li>
        <?php endif; ?>
        <?php if ($fb = aib_field('facebook_url', '')): ?>
          <li><a href="<?php echo esc_url((string) $fb); ?>" rel="nofollow noopener">Facebook</a></li>
        <?php endif; ?>
      </ul>
    </div>
    <div class="aib-footer__col">
      <h4><?php esc_html_e('Menu', 'ai-builder-furniture'); ?></h4>
      <?php wp_nav_menu(['theme_location' => 'footer', 'container' => false, 'fallback_cb' => '__return_empty_string']); ?>
    </div>
  </div>
  <div class="aib-footer__bottom">
    © <?php echo (int) date('Y'); ?> <?php echo aib_field_escaped('shop_name', get_bloginfo('name')); ?>. <?php esc_html_e('Tất cả các quyền được bảo lưu.', 'ai-builder-furniture'); ?>
  </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
