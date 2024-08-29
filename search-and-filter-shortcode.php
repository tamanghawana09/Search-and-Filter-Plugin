<?php 
/* <-- Add Plugin information here-->*/


// Enqueue the CSS for styling
function movie_search_filter_styles() {
    wp_enqueue_style('movie-search-filter-styles', plugins_url('css/movie-search-filter.css', __FILE__));
}
add_action('wp_enqueue_scripts', 'movie_search_filter_styles');

// Creating a shortcode to display the search form and results
function movie_search_filter_shortcode() {
    ob_start(); // Start output buffering ?>
<div class="movie-search-filter-container">
    <h1>Movie Search and Filter</h1>
    <form class="movie-search-form" action="" method="get">
        <input type="text" placeholder="Movie Name" name="Name"
            value="<?php echo isset($_GET['Name']) ? esc_attr($_GET['Name']) : ''; ?>">
        <select name="category_name">
            <option value="">Select Category</option>
            <?php
                    $categories = get_categories();
                    foreach ($categories as $category) {
                        echo '<option value="' . $category->slug . '" ' . selected(isset($_GET['category_name']) ? $_GET['category_name'] : '', $category->slug, false) . '>' . $category->name . '</option>';
                    }
                ?>
        </select>
        <select name="sort_order">
            <option value="">Sort Order according to date of post creation</option>
            <option value="ASC"
                <?php echo isset($_GET['sort_order']) ? selected($_GET['sort_order'], 'ASC', false) : ''; ?>>Ascending
            </option>
            <option value="DESC"
                <?php echo isset($_GET['sort_order']) ? selected($_GET['sort_order'], 'DESC', false) : ''; ?>>Descending
            </option>
        </select>
        <input type="submit" value="Search">
    </form>

    <?php
            $query_args = [
                'post_type' => 'post',
                'posts_per_page' => -1,  // Show all posts
            ];

            if (!empty($_GET['category_name'])) {
                $query_args['tax_query'] = [
                    [
                        'taxonomy' => 'category',
                        'field' => 'slug',
                        'terms' => sanitize_text_field($_GET['category_name']),
                    ]
                ];
            }

            if (!empty($_GET['Name'])) {
                $query_args['s'] = sanitize_text_field($_GET['Name']);
            }

            if (isset($_GET['sort_order']) && in_array($_GET['sort_order'], ['ASC', 'DESC'])) {
                $query_args['order'] = sanitize_text_field($_GET['sort_order']);
            }

            $movie_query = new WP_Query($query_args);

            if ($movie_query->have_posts()) {
                echo '<div class="movie-cards">';
                while ($movie_query->have_posts()) {
                    $movie_query->the_post();
                    echo '<div class="movie-card">';
                    if (has_post_thumbnail()) {
                        echo '<div class="movie-thumbnail">' . get_the_post_thumbnail() . '</div>';
                    }
                    echo '<div class="movie-title"><a href="' . get_permalink() . '">' . get_the_title() . '</a></div>';
                    echo '<div class="movie-description">' . get_the_excerpt() . '</div>';
                    echo '</div>';
                }
                echo '</div>';
            } else {
                echo '<p>No movies found.</p>';
            }

            wp_reset_postdata();
        ?>
</div>
<?php

    return ob_get_clean(); // Return the buffered content
}

add_shortcode('movie_search_filter', 'movie_search_filter_shortcode');