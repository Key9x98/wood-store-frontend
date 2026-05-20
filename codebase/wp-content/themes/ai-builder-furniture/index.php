<?php
defined('ABSPATH') || exit;
get_header();
?>
<section class="aib-container aib-archive">
  <?php if (have_posts()): ?>
    <div class="aib-post-grid">
      <?php while (have_posts()): the_post(); ?>
        <article <?php post_class('aib-card'); ?>>
          <?php if (has_post_thumbnail()): ?>
            <a class="aib-card__media" href="<?php the_permalink(); ?>"><?php the_post_thumbnail('medium_large'); ?></a>
          <?php endif; ?>
          <div class="aib-card__body">
            <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
            <p><?php echo esc_html(get_the_excerpt()); ?></p>
          </div>
        </article>
      <?php endwhile; ?>
    </div>
    <?php the_posts_pagination(); ?>
  <?php else: ?>
    <p><?php esc_html_e('Chưa có nội dung.', 'ai-builder-furniture'); ?></p>
  <?php endif; ?>
</section>
<?php get_footer();
