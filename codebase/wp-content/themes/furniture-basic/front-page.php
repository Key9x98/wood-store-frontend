<?php
/**
 * Front page — Furniture Basic.
 * Trang chủ cửa hàng đồ gỗ. Nội dung lấy qua fb_field() (CMS / default).
 */
defined( 'ABSPATH' ) || exit;
get_header();

$hero_image  = fb_field( 'hero_image' );
$about_image = fb_field( 'about_image' );
$shop_url    = get_post_type_archive_link( 'product' );
?>

<!-- ============================ HERO ============================ -->
<section class="hero<?php echo $hero_image ? ' hero--has-image' : ''; ?>"
  <?php if ( $hero_image ) : ?>style="background-image: linear-gradient(115deg, rgba(40,27,14,.92), rgba(60,40,20,.55)), url('<?php echo esc_url( $hero_image ); ?>');"<?php endif; ?>>
  <div class="container hero__inner">
    <div class="hero__content">
      <span class="hero__eyebrow"><?php echo fb_icon( 'leaf', 16 ); ?> Gỗ tự nhiên 100% · Giá tận xưởng</span>
      <h1><?php echo esc_html( fb_field( 'shop_name' ) ); ?> — <em>nội thất</em> cho tổ ấm Việt</h1>
      <p class="hero__lead"><?php echo esc_html( fb_field( 'intro' ) ); ?></p>
      <div class="hero__actions">
        <a class="btn btn--gold btn--lg" href="<?php echo esc_url( $shop_url ); ?>">
          Xem sản phẩm <?php echo fb_icon( 'arrow', 18 ); ?>
        </a>
        <a class="btn btn--ghost btn--lg" href="tel:<?php echo esc_attr( fb_tel() ); ?>">
          <?php echo fb_icon( 'phone', 18 ); ?> Tư vấn miễn phí
        </a>
      </div>
      <div class="hero__stats">
        <div class="hero__stat">
          <strong><?php echo esc_html( fb_field( 'years' ) ); ?>+</strong>
          <span>Năm kinh nghiệm</span>
        </div>
        <div class="hero__stat">
          <strong><?php echo esc_html( fb_field( 'products_done' ) ); ?></strong>
          <span>Sản phẩm hoàn thiện</span>
        </div>
        <div class="hero__stat">
          <strong><?php echo esc_html( fb_field( 'customers' ) ); ?></strong>
          <span>Khách hàng tin chọn</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ======================== FEATURE BAR ========================= -->
<div class="feature-bar">
  <div class="container">
    <div class="feature-bar__grid">
      <?php
      $features = array(
        array( 'truck',   'Giao lắp tận nơi', 'Miễn phí trong nội thành' ),
        array( 'shield',  'Bảo hành dài hạn', 'Cam kết gỗ thật, chắc bền' ),
        array( 'tag',     'Giá tận xưởng',    'Không qua trung gian' ),
        array( 'headset', 'Tư vấn 24/7',      'Hỗ trợ chọn mẫu, đo đạc' ),
      );
      foreach ( $features as $f ) : ?>
        <div class="feature">
          <span class="feature__icon"><?php echo fb_icon( $f[0], 24 ); ?></span>
          <div>
            <div class="feature__title"><?php echo esc_html( $f[1] ); ?></div>
            <div class="feature__text"><?php echo esc_html( $f[2] ); ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- ========================= CATEGORIES ========================= -->
<?php
$cats = get_terms( array(
  'taxonomy'   => 'product_cat',
  'hide_empty' => true,
  'number'     => 8,
  'orderby'    => 'count',
  'order'      => 'DESC',
  'exclude'    => array( (int) get_option( 'default_product_cat' ) ), // "Uncategorized"
) );
if ( $cats && ! is_wp_error( $cats ) ) : ?>
<section class="section">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">Danh mục</span>
      <h2 class="section-title">Bộ sưu tập đồ gỗ</h2>
      <p class="section-sub">Từ phòng khách đến phòng ngủ — mỗi món đồ là một tác phẩm thủ công.</p>
    </div>
    <div class="cat-grid">
      <?php foreach ( $cats as $cat ) :
        $thumb_id = fb_cat_thumb_id( $cat ); ?>
        <a class="cat-card" href="<?php echo esc_url( get_term_link( $cat ) ); ?>">
          <?php
          if ( $thumb_id ) {
            echo wp_get_attachment_image( $thumb_id, 'fb-product', false, array( 'loading' => 'lazy' ) );
          } else {
            echo '<span class="cat-card__fallback"></span>';
          }
          ?>
          <span class="cat-card__go"><?php echo fb_icon( 'arrow', 18 ); ?></span>
          <span class="cat-card__body">
            <span class="cat-card__name"><?php echo esc_html( $cat->name ); ?></span>
            <span class="cat-card__count"><?php echo esc_html( $cat->count ); ?> sản phẩm</span>
          </span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ====================== FEATURED PRODUCTS ===================== -->
<section class="section section--sand">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">Bán chạy</span>
      <h2 class="section-title">Sản phẩm nổi bật</h2>
      <p class="section-sub">Những mẫu được khách hàng yêu thích và đặt nhiều nhất.</p>
    </div>
    <?php
    $loop = new WP_Query( array(
      'post_type'      => 'product',
      'posts_per_page' => 8,
      'no_found_rows'  => true,
    ) );
    if ( $loop->have_posts() ) : ?>
      <div class="product-grid">
        <?php while ( $loop->have_posts() ) : $loop->the_post(); ?>
          <?php get_template_part( 'content', 'product' ); ?>
        <?php endwhile; ?>
      </div>
      <div class="section-foot">
        <a class="btn btn--primary btn--lg" href="<?php echo esc_url( $shop_url ); ?>">
          Xem tất cả sản phẩm <?php echo fb_icon( 'arrow', 18 ); ?>
        </a>
      </div>
      <?php wp_reset_postdata(); ?>
    <?php else : ?>
      <div class="empty-state">
        <?php echo fb_icon( 'sofa', 44 ); ?>
        <h3>Chưa có sản phẩm nào</h3>
        <p>Thêm sản phẩm trong <strong>wp-admin → Sản phẩm</strong> để hiển thị tại đây.</p>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- =========================== ABOUT ============================ -->
<section id="about" class="section">
  <div class="container about__row">
    <div class="about__media">
      <?php if ( $about_image ) : ?>
        <img src="<?php echo esc_url( $about_image ); ?>" alt="<?php echo esc_attr( fb_field( 'shop_name' ) ); ?>" loading="lazy" />
      <?php endif; ?>
      <div class="about__badge">
        <?php echo fb_icon( 'award', 30 ); ?>
        <div>
          <strong><?php echo esc_html( fb_field( 'years' ) ); ?>+ năm</strong>
          <span>Giữ nghề mộc truyền thống</span>
        </div>
      </div>
    </div>
    <div class="about__copy">
      <span class="eyebrow">Về xưởng gỗ</span>
      <h2 class="section-title section-head--left">Tinh hoa nghề mộc trong từng sản phẩm</h2>
      <p><?php echo esc_html( fb_field( 'about' ) ); ?></p>
      <ul class="feature-list">
        <li><?php echo fb_icon( 'check', 16 ); ?><div><strong>Gỗ tự nhiên tuyển chọn</strong><small>Gụ, hương, sồi, xoan đào — đã qua xử lý chống mối mọt, cong vênh.</small></div></li>
        <li><?php echo fb_icon( 'check', 16 ); ?><div><strong>Thợ lành nghề chế tác</strong><small>Mộng ghép chắc chắn, hoàn thiện tỉ mỉ từng chi tiết.</small></div></li>
        <li><?php echo fb_icon( 'check', 16 ); ?><div><strong>Đặt đóng theo yêu cầu</strong><small>Tuỳ chỉnh kích thước, màu sơn theo không gian của bạn.</small></div></li>
      </ul>
      <a class="btn btn--primary" href="<?php echo esc_url( $shop_url ); ?>">Khám phá sản phẩm <?php echo fb_icon( 'arrow', 18 ); ?></a>
    </div>
  </div>
</section>

<!-- =========================== STATS ============================ -->
<section class="stats section--tight">
  <div class="container">
    <div class="stats__grid">
      <div class="stat"><strong><?php echo esc_html( fb_field( 'years' ) ); ?>+</strong><span>Năm kinh nghiệm</span></div>
      <div class="stat"><strong><?php echo esc_html( fb_field( 'products_done' ) ); ?></strong><span>Sản phẩm bàn giao</span></div>
      <div class="stat"><strong><?php echo esc_html( fb_field( 'customers' ) ); ?></strong><span>Khách hàng hài lòng</span></div>
      <div class="stat"><strong>4.9/5</strong><span>Điểm đánh giá trung bình</span></div>
    </div>
  </div>
</section>

<!-- ======================== TESTIMONIALS ======================== -->
<section class="section">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">Cảm nhận khách hàng</span>
      <h2 class="section-title">Khách hàng nói gì về chúng tôi</h2>
    </div>
    <div class="testi-grid">
      <?php
      $reviews = array(
        array( 'Bộ bàn ghế gỗ gụ rất chắc và đẹp, vân gỗ tự nhiên. Giao lắp đúng hẹn, thợ làm cẩn thận.', 'Chị Hương', 'Quận 7, TP.HCM' ),
        array( 'Đặt đóng tủ quần áo theo kích thước phòng, vừa khít. Giá hợp lý hơn ngoài showroom nhiều.', 'Anh Tuấn', 'Hà Đông, Hà Nội' ),
        array( 'Tư vấn nhiệt tình, gửi ảnh thực tế trước khi mua. Sập gỗ dùng 2 năm vẫn như mới.', 'Cô Lan', 'TP. Biên Hòa' ),
      );
      foreach ( $reviews as $r ) : ?>
        <div class="testi">
          <span class="testi__quote"><?php echo fb_icon( 'quote', 40 ); ?></span>
          <div class="testi__stars">
            <?php for ( $i = 0; $i < 5; $i++ ) { echo fb_icon( 'star', 16 ); } ?>
          </div>
          <p class="testi__text">“<?php echo esc_html( $r[0] ); ?>”</p>
          <div class="testi__author">
            <span class="testi__avatar"><?php echo esc_html( mb_substr( $r[1], 0, 1 ) ); ?></span>
            <div>
              <div class="testi__name"><?php echo esc_html( $r[1] ); ?></div>
              <div class="testi__role"><?php echo esc_html( $r[2] ); ?></div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ========================= CTA BANNER ========================= -->
<section class="section section--tight">
  <div class="container">
    <div class="cta-banner">
      <div class="cta-banner__text">
        <h2>Cần tư vấn chọn đồ gỗ cho ngôi nhà của bạn?</h2>
        <p>Gọi ngay hotline — đội ngũ của chúng tôi sẽ hỗ trợ chọn mẫu, đo đạc và báo giá miễn phí.</p>
      </div>
      <div class="cta-banner__actions">
        <a class="btn btn--gold btn--lg" href="tel:<?php echo esc_attr( fb_tel() ); ?>">
          <?php echo fb_icon( 'phone', 18 ); ?> <?php echo esc_html( fb_field( 'phone' ) ); ?>
        </a>
        <?php if ( fb_field( 'zalo' ) ) : ?>
          <a class="btn btn--ghost btn--lg" href="<?php echo esc_url( fb_field( 'zalo' ) ); ?>" target="_blank" rel="noopener">
            <?php echo fb_icon( 'chat', 18 ); ?> Nhắn Zalo
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<?php
get_footer();
