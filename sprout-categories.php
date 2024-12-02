<?php
/**
 * Template Name: Категории кълнове
 *
 * @package kadence
 */

get_header();
?>

<div id="primary" class="content-area">
    <div class="content-container site-container">
        <main id="main" class="site-main">
            <?php
            // Получаване на главните категории
            $main_categories = get_terms(array(
                'taxonomy' => 'sprout_category',
                'hide_empty' => false,
                'parent' => 0,
                'orderby' => 'name',
                'order' => 'ASC'
            ));

            if (!empty($main_categories) && !is_wp_error($main_categories)):
            ?>
                <div class="sprouts-tabs">
                    <!-- Табове -->
                    <div class="sprouts-tabs-nav">
                        <?php foreach ($main_categories as $index => $category): ?>
                            <button class="sprouts-tab-button<?php echo $index === 0 ? ' active' : ''; ?>"
                                    data-tab="category-<?php echo $category->term_id; ?>">
                                <?php echo esc_html($category->name); ?>
                            </button>
                        <?php endforeach; ?>
                    </div>

                    <!-- Съдържание на табовете -->
                    <?php foreach ($main_categories as $index => $category):
                        $subcategories = get_terms(array(
                            'taxonomy' => 'sprout_category',
                            'hide_empty' => false,
                            'parent' => $category->term_id
                        ));
                    ?>
                        <div id="category-<?php echo $category->term_id; ?>"
                             class="sprouts-tab-content<?php echo $index === 0 ? ' active' : ''; ?>">

                            <?php if (!empty($subcategories)): ?>
                                <div class="subcategories-grid">
                                    <?php foreach ($subcategories as $subcategory):
                                        $category_description = term_description($subcategory->term_id);
                                    ?>
                                        <div class="subcategory-card">
                                            <a href="<?php echo get_term_link($subcategory); ?>"
                                               class="subcategory-title">
                                                <?php echo esc_html($subcategory->name); ?>
                                            </a>
                                            <?php if (!empty($category_description)): ?>
                                                <div class="subcategory-description">
                                                    <?php echo wp_kses_post($category_description); ?>
                                                </div>
                                            <?php endif; ?>
                                            <?php
                                            // Получаване на свързани публикации
                                            $posts = get_posts(array(
                                                'post_type' => 'sprout',
                                                'tax_query' => array(
                                                    array(
                                                        'taxonomy' => 'sprout_category',
                                                        'field'    => 'term_id',
                                                        'terms'    => $subcategory->term_id,
                                                    ),
                                                ),
                                                'posts_per_page' => -1,
                                            ));

                                            if (!empty($posts)): ?>
                                                <div class="subcategory-posts">
                                                    <?php foreach ($posts as $post): ?>
                                                        <a href="<?php echo get_permalink($post->ID); ?>"
                                                           class="subcategory-post-link">
                                                            <?php echo get_the_title($post->ID); ?>
                                                        </a>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="no-subcategories">Няма добавени подкатегории.</p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="no-categories">Няма добавени категории.</p>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php get_footer(); ?>
