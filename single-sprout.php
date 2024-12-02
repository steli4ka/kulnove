<?php
/**
 * The main single sprout template file.
 *
 * @package kadence
 */

get_header();
?>

<div id="primary" class="content-area">
    <div class="content-container site-container">
        <main id="main" class="site-main">
            <?php
            while ( have_posts() ) {
                the_post();
                ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class('entry content-bg single-entry'); ?>>
                    <div class="entry-content-wrap">
                        <!-- Progress Bar -->
                        <div class="reading-progress">
                            <div class="progress-bar"></div>
                        </div>

                        <div class="sprout-header">
                            <div class="container">
                                <!-- Breadcrumbs -->
                                <div class="sprout-breadcrumbs">
                                    <?php
                                    $categories = get_the_terms(get_the_ID(), 'sprout_category');
                                    if ($categories && !is_wp_error($categories)) {
                                        $parent_cat = null;
                                        $child_cat = null;

                                        foreach ($categories as $category) {
                                            if ($category->parent == 0) {
                                                $parent_cat = $category;
                                            } else {
                                                $child_cat = $category;
                                            }
                                        }

                                        if ($parent_cat) {
                                            echo '<a href="' . esc_url(get_term_link($parent_cat)) . '"><i class="fas fa-seedling"></i> ' . esc_html($parent_cat->name) . '</a>';
                                            echo ' <i class="fas fa-chevron-right"></i> ';
                                        }
                                        if ($child_cat) {
                                            echo '<a href="' . esc_url(get_term_link($child_cat)) . '"><i class="fas fa-leaf"></i> ' . esc_html($child_cat->name) . '</a>';
                                            echo ' <i class="fas fa-chevron-right"></i> ';
                                        }
                                    }
                                    ?>
                                    <span class="current-sprout"><?php the_title(); ?></span>
                                </div>

                                <h1 class="entry-title"><?php the_title(); ?></h1>

                                <?php if (has_post_thumbnail()): ?>
                                    <div class="sprout-featured-image">
                                        <?php the_post_thumbnail('large'); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="sprout-content-wrapper">
                            <div class="container">
                                <div class="sprout-main-content">
                                    <div class="entry-content">
                                        <?php the_content(); ?>
                                    </div>

                                    <?php
                                    // Етикети
                                    $post_tags = get_the_tags();
                                    if ($post_tags): ?>
                                        <div class="sprout-tags">
                                            <i class="fas fa-tags"></i>
                                            <?php foreach($post_tags as $tag): ?>
                                                <a href="<?php echo get_tag_link($tag->term_id); ?>" class="sprout-tag">
                                                    <?php echo $tag->name; ?>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php
                                    // Свързани публикации
                                    $related_args = array(
                                        'post_type' => 'sprout',
                                        'posts_per_page' => 3,
                                        'post__not_in' => array(get_the_ID()),
                                        'tax_query' => array(
                                            array(
                                                'taxonomy' => 'sprout_category',
                                                'field' => 'term_id',
                                                'terms' => wp_get_post_terms(get_the_ID(), 'sprout_category', array('fields' => 'ids'))
                                            )
                                        )
                                    );

                                    $related_posts = new WP_Query($related_args);

                                    if ($related_posts->have_posts()): ?>
                                        <div class="related-sprouts">
                                            <h2><i class="fas fa-seedling"></i> Подобни кълнове</h2>
                                            <div class="related-sprouts-grid">
                                                <?php while ($related_posts->have_posts()): $related_posts->the_post(); ?>
                                                    <a href="<?php the_permalink(); ?>" class="related-sprout-card">
                                                        <?php if (has_post_thumbnail()): ?>
                                                            <div class="related-sprout-image">
                                                                <?php the_post_thumbnail('medium'); ?>
                                                            </div>
                                                        <?php endif; ?>
                                                        <div class="related-sprout-content">
                                                            <h3><?php the_title(); ?></h3>
                                                            <div class="related-sprout-excerpt">
                                                                <?php echo wp_trim_words(get_the_excerpt(), 15); ?>
                                                            </div>
                                                            <span class="read-more">
                                                                Прочети повече <i class="fas fa-arrow-right"></i>
                                                            </span>
                                                        </div>
                                                    </a>
                                                <?php endwhile; ?>
                                            </div>
                                        </div>
                                        <?php
                                        wp_reset_postdata();
                                    endif;
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>
                <?php
            }
            ?>
        </main>
    </div>
</div>

<?php get_footer(); ?>
