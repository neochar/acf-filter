<?php
/*
Plugin Name: ACF Filtering
Description: ACF Filtering
Author: neochar
Version: 0.0.1
Author URI: https://github.com/neochar
*/

ini_set('display_errors', 1);

global $cities;
$cities = [
    'Amsterdam',
    'New York',
    'Tokyo',
    'Moscow',
    'Cairo',
    'London',
    'Paris',
    'Muhosransk',
];

/**
 * @param $data
 */
function dump($data = null)
{
    if ($data) {
        echo '<pre>';
        var_dump($data);
        echo '</pre>';
    }
    exit();
}


function acf_filter_clear_posts()
{
    $start = microtime(true);
    /*
     * Clean posts.
     */
    ini_set('memory_limit', '1024M');
    ini_set('max_execution_time', 0);

    /* Some preparations */
    define('WP_IMPORTING', true);  // What is this?

    ignore_user_abort(true);

    global $wpdb;
    $wpdb->query('SET autocommit = 0;');

    register_shutdown_function(function () {
        global $wpdb;
        $wpdb->query('COMMIT;');
    });

    // Disable counting
    /* End of preparations */

    $posts = get_posts([
        'numberposts' => -1,
        'post_type' => 'post',
        'post_status' => 'publish',
        'orderby' => 'ID',
        'fields' => 'ids'
    ]);

    foreach ($posts as $key => $post_id) {
        wp_delete_post($post_id, true);
    }

    wp_defer_term_counting(false);
    wp_defer_comment_counting(false);

    // Enable counting
    $wpdb->query('COMMIT;');
    $wpdb->query('SET autocommit = 1;');

    wp_defer_term_counting(true);
    wp_defer_comment_counting(true);

    echo 'Total posts deleted: ' . count($posts) . '<br>';
    echo '<br>Time total on deletion: ' .
        (microtime(true) - $start);
}


function acf_filter_callback()
{
    global $cities;
    $choices_1 = get_field_object(
                     'field_5ce6eaf7d9b83'
                 )['choices'];
    require_once 'page.php';
}

function acf_filter_admin_menu()
{
    add_menu_page(
        'ACF Filter',
        'ACF Filter',
        'manage_options',
        'acf_filter',
        'acf_filter_callback'
    );
}

add_action('admin_menu', 'acf_filter_admin_menu');


function acf_filter_generate_demo_callback()
{
    acf_filter_clear_posts();

    $start = microtime(true);

    wp_defer_term_counting(false);
    wp_defer_comment_counting(false);

    global $cities;
    global $wpdb;
    $wpdb->query('SET autocommit = 0;');
    $posts_count = 6000;

    /*
     * Generate demo data.
     * Create posts, add random custom fields values.
     * Fields:
     * 1. City
     * 2. Phone
     * 3. Option 1
     * 4. Option 2
     */

    $choices_1 = get_field_object(
                     'field_5ce6eaf7d9b83'
                 )['choices'];
    $choices_2 = get_field_object(
                     'field_5ce6eb05d9b84'
                 )['choices'];

    foreach (range(1, $posts_count) as $i) {
        $post_id = wp_insert_post([
            'post_title' => "post-$i",
            'post_content' => 'Demo content',
            'post_status' => 'publish',
        ]);
        update_field(
            'city',
            $cities[array_rand($cities)],
            $post_id
        );
        update_field(
            'phone',
            rand(
                1111111111,
                9999999999
            ),
            $post_id
        );
        update_field(
            'option_1',
            array_rand($choices_1, rand(2, 5)),
            $post_id
        );
        update_field(
            'option_2',
            array_rand($choices_2, rand(2, 5)),
            $post_id
        );
    }

    echo '<br>Time total on query: ' .
        (microtime(true) - $start);

    $wpdb->query('COMMIT;');
    $wpdb->query('SET autocommit = 1;');

    wp_defer_term_counting(true);
    wp_defer_comment_counting(true);

    echo '<br>Time total on commit: ' .
        (microtime(true) - $start);

    exit();
}

add_action(
    'admin_post_nopriv_acf_filter_generate_demo',
    'acf_filter_generate_demo_callback'
);
add_action(
    'admin_post_acf_filter_generate_demo',
    'acf_filter_generate_demo_callback'
);

function acf_filter_test_filter_callback()
{
    // + Get filters from POST request
    // + Build get_posts args array
    // - Count posts
    // - Render first 10 posts

    $cities = (array)$_POST['cities'];
    $option_1 = (array)$_POST['option_1'];
    $option_1_query = [];
    if ($option_1) {
        $option_1 = array_keys($_POST['option_1']);

        $option_1_query = [
            'relation' => 'OR',
        ];
        foreach ($option_1 as $option) {
            $option_1_query[] = [
                'key' => 'option_1',
                'value' => $option,
                'compare' => 'LIKE'
            ];
        }

    }

    echo '<pre>';
    print_r([
        $cities,
        $option_1
    ]);
    echo '</pre><br>';
    if ($cities && $option_1_query) {
        $meta_query = [
            'relation' => 'AND',
            [
                'key' => 'city',
                'value' => $cities,
                'compare' => 'IN'
            ],
            $option_1_query
        ];
    } else if ($option_1_query) {
        $meta_query = $option_1_query;
    } else {
        dump('No filters provided');
        exit();
    }

    $posts = get_posts([
        'numberposts' => -1,
        'post_type' => 'post',
        'post_status' => 'publish',
        'meta_query' => $meta_query
    ]);

    $count = count($posts);
    echo "Total count: $count<br><br>";

    foreach (array_slice($posts, 0, 10) as $post) {
        echo "<a href='/wp-admin/post.php?action=edit&post={$post->ID}'>{$post->ID}</a><br>";
    }
    exit();
}

add_action(
    'admin_post_nopriv_acf_filter_test_filter',
    'acf_filter_test_filter_callback'
);
add_action(
    'admin_post_acf_filter_test_filter',
    'acf_filter_test_filter_callback'
);

