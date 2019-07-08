<?php
/**
 * Last Modified By class
 *
 * @package last_modified_by
 * @since   1.0.0
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
     * Admin actions.
     *
     * @access public
     * @return void
     */
    public function admin_actions() {
        foreach ( get_post_types() as $pt ) :
            add_filter( "manage_{$pt}_posts_columns", array( $this, 'column_heading' ), 10, 1 );
            add_action( "manage_{$pt}_posts_custom_column", array( $this, 'column_content' ), 10, 2 );
            add_action( "manage_edit-{$pt}_sortable_columns", array( $this, 'column_sort' ), 10, 2 );
        endforeach;
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

}

new Last_Modified_By();

