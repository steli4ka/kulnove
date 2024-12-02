<?php
// Базови функции на дъщерната тема
function kadence_child_enqueue_styles() {
    wp_enqueue_style('kadence-child',
        get_stylesheet_uri(),
        array('kadence-global'),
        '1.0.0'
    );
}
add_action('wp_enqueue_scripts', 'kadence_child_enqueue_styles');

// Регистрация на Custom Post Type
function register_sprout_post_type() {
    $labels = array(
        'name'               => 'Кълнове',
        'singular_name'      => 'Кълн',
        'menu_name'          => 'Кълнове',
        'add_new'           => 'Добави нов',
        'add_new_item'      => 'Добави нов кълн',
        'edit_item'         => 'Редактирай кълн',
        'new_item'          => 'Нов кълн',
        'view_item'         => 'Виж кълн',
        'search_items'      => 'Търси кълнове',
        'not_found'         => 'Не са намерени кълнове',
        'not_found_in_trash'=> 'Няма кълнове в кошчето'
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'sprout'),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-seedling',
        'supports'           => array('title', 'editor', 'thumbnail', 'excerpt')
    );

    register_post_type('sprout', $args);
}
add_action('init', 'register_sprout_post_type');

// Регистрация на таксономия
function register_sprout_taxonomy() {
    $labels = array(
        'name'              => 'Категории кълнове',
        'singular_name'     => 'Категория кълнове',
        'search_items'      => 'Търси категории',
        'all_items'         => 'Всички категории',
        'parent_item'       => 'Родителска категория',
        'parent_item_colon' => 'Родителска категория:',
        'edit_item'         => 'Редактирай категория',
        'update_item'       => 'Обнови категория',
        'add_new_item'      => 'Добави нова категория',
        'new_item_name'     => 'Ново име на категория',
        'menu_name'         => 'Категории'
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'sprout-category')
    );

    register_taxonomy('sprout_category', 'sprout', $args);
}
add_action('init', 'register_sprout_taxonomy');

// Добавяне на стилове и скриптове
function enqueue_sprout_assets() {
    if (is_singular('sprout') || is_post_type_archive('sprout') || is_page_template('page-templates/sprout-categories.php')) {
        wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css');

        wp_enqueue_style(
            'sprout-styles',
            get_stylesheet_directory_uri() . '/assets/css/sprout-styles.css',
            array(),
            '1.0'
        );

        wp_enqueue_script(
            'sprout-scripts',
            get_stylesheet_directory_uri() . '/assets/js/sprout-scripts.js',
            array('jquery'),
            '1.0',
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'enqueue_sprout_assets');
