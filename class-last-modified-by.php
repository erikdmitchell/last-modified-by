<?php
/**
 * Last Modified By class
 *
 * @package last_modified_by
 * @since   0.1.0
 */

/**
 * Final Last_Modified_By class.
 *
 * @final
 */
final class Last_Modified_By {

    /**
     * Version
     *
     * @var string
     * @access public
     */
    public $version = '0.1.1';

    /**
     * Construct function.
     *
     * @access public
     * @return void
     */
    public function __construct() {
        $this->define_constants();
        $this->init();
    }

    /**
     * Define constants function.
     *
     * @access private
     * @return void
     */
    private function define_constants() {
        $this->define( 'LAST_MODIFIED_BY_PATH', plugin_dir_path( __FILE__ ) );
        $this->define( 'LAST_MODIFIED_BY_URL', plugin_dir_url( __FILE__ ) );
        $this->define( 'LAST_MODIFIED_BY_VERSION', $this->version );
    }

    /**
     * Define function.
     *
     * @access private
     * @param mixed $name (name).
     * @param mixed $value (value).
     * @return void
     */
    private function define( $name, $value ) {
        if ( ! defined( $name ) ) {
            define( $name, $value );
        }
    }

    /**
     * Init function.
     *
     * @access public
     * @return void
     */
    public function init() {
        add_action( 'admin_init', array( $this, 'admin_actions' ), 1 );
    }

    /**
     * Check if last-modified-timestamp plugin is active.
     *
     * @access public
     * @return bool
     */
    public function is_last_modified_timestamp_active() {
        return is_plugin_active( 'last-modified-timestamp/last-modified-timestamp.php' ) ||
                function_exists( 'last_modified_timestamp' ) ||
                class_exists( 'Last_Modified_Timestamp' );
    }

    /**
     * Admin actions.
     *
     * @access public
     * @return void
     */
    public function admin_actions() {
        // Include plugin.php if not already included for is_plugin_active function.
        if ( ! function_exists( 'is_plugin_active' ) ) {
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        // Only add columns if last-modified-timestamp plugin is not active.
        if ( ! $this->is_last_modified_timestamp_active() ) {
            foreach ( get_post_types() as $pt ) :
                add_filter( "manage_{$pt}_posts_columns", array( $this, 'column_heading' ), 10, 1 );
                add_action( "manage_{$pt}_posts_custom_column", array( $this, 'column_content' ), 10, 2 );
                add_action( "manage_edit-{$pt}_sortable_columns", array( $this, 'column_sort' ), 10, 2 );
            endforeach;
        } else {
            $this->init_alternative_functionality();
        }
    }

    /**
     * Initialize alternative functionality when last-modified-timestamp plugin is active.
     *
     * @access public
     * @return void
     */
    public function init_alternative_functionality() {
        add_filter( 'last_modified_timestamp_output', array( $this, 'enhance_timestamp_with_author' ), 10, 2 );
    }

    /**
     * Append the new column to the columns array.
     *
     * @access public
     * @param mixed $columns array.
     * @return array
     */
    public function column_heading( $columns ) {
        $columns['modified-by'] = _x( 'Modified By', 'column heading', 'last-modified-by' );

        return $columns;
    }

    /**
     * Put the last modified date in the content area.
     *
     * @access public
     * @param mixed $column_name string.
     * @param mixed $id post id.
     * @return void
     */
    public function column_content( $column_name, $id ) {
        if ( 'modified-by' == $column_name ) :
            echo $this->construct_modified_by( $id ); // phpcs:ignore
        endif;
    }

    /**
     * Register the column as sortable.
     *
     * @access public
     * @param mixed $columns array.
     * @return array
     */
    public function column_sort( $columns ) {
        $columns['modified-by'] = 'modified';

        return $columns;
    }

    /**
     * Get last modified by.
     *
     * @access protected
     * @param int $post_id (default: 0).
     * @return html
     */
    protected function construct_modified_by( $post_id = 0 ) {
        $output = '<span class="last-modified-by">' . $this->get_the_modified_author( $post_id ) . '</span>';

        return apply_filters( 'last_modified_by_output', $output, $post_id );
    }

    /**
     * Get modified autohr by post.
     *
     * @access protected
     * @param int $post_id (default: 0).
     * @return string
     */
    protected function get_the_modified_author( $post_id = 0 ) {
        $last_id = get_post_meta( $post_id, '_edit_last', true );

        if ( $last_id ) :
            $last_user = get_userdata( $last_id );

            return apply_filters( 'the_modified_author', $last_user->display_name );
        endif;

        return '';
    }

    /**
     * Enhance timestamp output with author information when last-modified-timestamp plugin is active.
     *
     * @access public
     * @param string $timestamp The original timestamp output.
     * @param mixed  $context The context (could be post ID or other context data).
     * @return string Enhanced output with author information.
     */
    public function enhance_timestamp_with_author( $timestamp, $context ) {
        // Try to get post ID from context.
        $post_id = 0;

        if ( is_numeric( $context ) ) {
            $post_id = intval( $context );
        } elseif ( is_array( $context ) && isset( $context['post_id'] ) ) {
            $post_id = intval( $context['post_id'] );
        } elseif ( is_object( $context ) && isset( $context->ID ) ) {
            $post_id = intval( $context->ID );
        } else {
            // Try to get current post ID if we're in a post context.
            global $post;

            if ( $post && isset( $post->ID ) ) {
                $post_id = $post->ID;
            }
        }

        if ( $post_id > 0 ) {
            $author = $this->get_the_modified_author( $post_id );

            if ( ! empty( $author ) ) {
                $timestamp .= ' <span class="last-modified-by-author">by ' . esc_html( $author ) . '</span>';
            }
        }

        return $timestamp;
    }
}

new Last_Modified_By();
