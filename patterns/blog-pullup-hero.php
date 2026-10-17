<?php
/**
 * Title: Cinematic Blog Video Hero & Pull-Up Content Sheet
 * Slug: carinena-kebab-fse/blog-pullup-hero
 * Categories: carinena-kebab-blog
 * Description: 100vh pinned video cinema hero with white contrast pull-up sheet, matching fotovmag.ro blog post reference.
 */

$video_url = get_template_directory_uri() . '/assets/video/hero-bg.mp4';
$poster_url = get_template_directory_uri() . '/assets/frames/frame_001.webp';
?>
<!-- wp:group {"tagName":"article","className":"kebab-cinematic-post","metadata":{"name":"Cinematic Blog Post"}} -->
<article class="kebab-cinematic-post">
  <!-- Pinned 100vh Video Cinema Hero -->
  <div class="kebab-cinema-hero">
    <div class="kebab-cinema-media-wrap" aria-hidden="true">
      <video autoplay muted loop playsinline poster="<?php echo esc_url($poster_url); ?>">
        <source src="<?php echo esc_url($video_url); ?>" type="video/mp4">
      </video>
      <div class="kebab-cinema-vignette"></div>
    </div>
    
    <div class="kebab-cinema-hero-content">
      <div class="kebab-cinema-category-pill">
        <!-- wp:post-terms {"term":"category"} /-->
      </div>
      <!-- wp:post-title {"level":1,"className":"kebab-cinema-title"} /-->
      <div class="kebab-cinema-meta-row">
        <span class="kebab-cinema-meta-item">
          <!-- wp:post-date /-->
        </span>
        <span>•</span>
        <span class="kebab-cinema-meta-item">
          <!-- wp:post-author {"showAvatar":false} /-->
        </span>
        <span>•</span>
        <span class="kebab-cinema-meta-item">5 min citire</span>
      </div>
    </div>

    <div class="kebab-scroll-down-hint" aria-hidden="true">
      <span>Derulează</span>
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M7 13l5 5 5-5M7 6l5 5 5-5"></path>
      </svg>
    </div>
  </div>

  <!-- White Contrast Pull-Up Content Sheet -->
  <div class="kebab-content-sheet-container">
    <div class="kebab-sheet-inner">
      <!-- wp:post-content {"layout":{"type":"constrained"}} /-->
    </div>
  </div>
</article>
<!-- /wp:group -->
