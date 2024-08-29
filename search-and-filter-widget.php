<?php
/**
 * Plugin Name: Search and Filter Plugin
 * Author: Hawana Tamang
 * Description: This is a search and filter plugin.
 * Version: 1.0.0
 */

// Creating custom widget
class sf_widget extends WP_Widget {
    function __construct() {
        parent::__construct(
            // Base ID of widget
            'sf_widget',
            // Widget name that appears in UI
            __('Search and Filter Widget', 'textdomain'),
            // Widget Description
            array('description' => __('A widget to search and filter movies by categories and name', 'textdomain'))
        );
    }

    // This function outputs the content of the widget on the front-end.
    public function widget($args, $instance) {
        echo $args['before_widget'];
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }

        ob_start(); // Start output buffering
        ?>
<div class="container">
    <h1>Movie Search and Filter</h1>
    <form action="" method="get">
        <input type="text" placeholder="Movie Name" name="Name"
            value="<?php echo isset($_GET['Name']) ? esc_attr($_GET['Name']) : ''; ?>">
        <select name="category_name">
            <option value="">Select Category</option>
            <?php
                $categories = get_categories();
                foreach ($categories as $category) {
                    echo '<option value="' . $category->slug . '" ' . selected($_GET['category_name'], $category->slug, false) . '>' . $category->name . '</option>';
                }
                ?>
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
                    'terms' => $_GET['category_name'],
                ]
            ];
        }

        if (!empty($_GET['Name'])) {
            $query_args['s'] = sanitize_text_field($_GET['Name']);
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
        echo ob_get_clean(); // Output the buffered content
        echo $args['after_widget'];
    }

    public function form($instance) {
        if (isset($instance['title'])) {
            $title = $instance['title'];
        } else {
            $title = __('Search and Filter', 'textdomain');
        }
        ?>
<p>
    <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
    <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
        name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
</p>
<?php
    }

    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        return $instance;
    }
}

// Register the widget
function register_movie_search_filter_widget() {
    register_widget('sf_widget');
}
add_action('widgets_init', 'register_movie_search_filter_widget');