<?php

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound
namespace WPcomSpecialProjects\Qllm;

defined( 'ABSPATH' ) || exit;

/**
 * Main plugin class.
 *
 * @since   1.0.0
 * @version 1.0.0
 */
class Plugin {

	/**
	 * Plugin constructor.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	protected function __construct() {
		/* Empty on purpose. */
	}

	/**
	 * Prevent cloning.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  void
	 */
	private function __clone() {
		/* Empty on purpose. */
	}

	/**
	 * Prevent unserializing.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  void
	 */
	public function __wakeup() {
		/* Empty on purpose. */
	}

	/**
	 * Returns the singleton instance of the plugin.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  Plugin
	 */
	public static function get_instance(): self {
		static $instance = null;

		if ( null === $instance ) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * Initializes the plugin components.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  void
	 */
	public function initialize(): void {
		add_filter( 'register_block_type_args', array( $this, 'block_meta' ), 10, 2 );
		add_action( 'enqueue_block_editor_assets', array( $this, 'editor_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'assets' ) );
	}

	/**
	 * Enqueue assets for the plugin.
	 *
	 * @return void
	 */
	public function assets(): void {
		$deps = wpcomsp_qllm_get_asset_meta( WPCOMSP_QLLM_PATH . 'assets/js/build/frontend.asset.php' );

		wp_enqueue_style(
			'wpcomsp-qllm',
			WPCOMSP_QLLM_URL . 'assets/js/build/style-index.css',
			array(),
			$deps['version']
		);

		wp_enqueue_script(
			'wpcomsp-qllm',
			WPCOMSP_QLLM_URL . 'assets/js/build/frontend.js',
			array(),
			$deps['version'],
			true
		);
	}

	/**
	 * Enqueue assets for the plugin block editor view.
	 *
	 * @return void
	 */
	public function editor_assets(): void {
		$deps = wpcomsp_qllm_get_asset_meta( WPCOMSP_QLLM_PATH . 'assets/js/build/index.asset.php' );

		wp_enqueue_style(
			'wpcomsp-qllm',
			WPCOMSP_QLLM_URL . 'assets/js/build/index.css',
			array(),
			$deps['version']
		);

		wp_enqueue_script(
			'wpcomsp-qllm',
			WPCOMSP_QLLM_URL . 'assets/js/build/index.js',
			$deps['dependencies'],
			$deps['version'],
			true
		);
	}

	/**
	 * Filter the pagination block to add a new attribute and render callback.
	 *
	 * @param array $settings
	 * @param array $metadata
	 * @return array
	 */
	public function block_meta( array $settings, string $name ): array {

		// Check this is the query pagination block, and that the standard WP function exists for a fallback.
		if ( 'core/query-pagination' !== $name || ! function_exists( 'render_block_core_query_pagination' ) ) {
			return $settings;
		}

		// Add a new render callback function to the pagination block.
		$settings['render_callback'] = array( $this, 'render' );

		// Add a "load more" attribute that we can toggle in the editor.
		$settings['attributes']['loadMore'] = array(
			'type'    => 'boolean',
			'default' => false,
		);

		// Add a "infinite scroll" attribute that we can toggle in the editor.
		$settings['attributes']['infiniteScroll'] = array(
			'type'    => 'boolean',
			'default' => false,
		);

		// Set the infiniteScroll animation color.
		$settings['attributes']['infiniteScrollColor'] = array(
			'type'    => 'string',
			'default' => '#000',
		);

		// Button text attribute.
		$settings['attributes']['loadMoreText'] = array(
			'type'    => 'string',
			'default' => __( 'Load More', 'query-loop-load-more' ),
		);

		// Loading text attribute.
		$settings['attributes']['loadingText'] = array(
			'type'    => 'string',
			'default' => __( 'Loading...', 'query-loop-load-more' ),
		);

		return $settings;
	}


	/**
	 * Renders the `core/query-pagination` block on the server.
	 *
	 * @param array  $attributes Block attributes.
	 * @param string $content    Block default content.
	 * @param WP_Block $block      Block instance.
	 * @return string Returns the wrapper for the Query pagination.
	 */
	public function render( array $attributes, string $content, \WP_Block $block ): string {

		// If load more isn't set to be on, return the core pagination.
		if ( false === $attributes['loadMore'] ) {
			return render_block_core_query_pagination( $attributes, $content );
		}

		$arrow_map = array(
			'none'    => '',
			'arrow'   => '→',
			'chevron' => '»',
		);

		$infinite_scroll_markup = '';
		if ( $attributes['infiniteScroll'] ) {
			$infinite_scroll_markup = '
				<div class="wp-load-more__infinite-scroll">
					<div class="animation-wrapper" style="border-color: ' . esc_attr( $attributes['infiniteScrollColor'] ) . '">
						<div></div>
						<div></div>
					</div>
				</div>
			';
		}

		// Get query context for current page number and query Id.
		$page_key         = isset( $block->context['queryId'] ) ? 'query-' . $block->context['queryId'] . '-page' : 'query-page';
		$inherit          = isset( $block->context['query']['inherit'] ) ? $block->context['query']['inherit'] : false;
		$page             = empty( $_GET[ $page_key ] ) ? 1 : (int) $_GET[ $page_key ]; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$block_query      = new \WP_Query( build_query_vars_from_query_block( $block, $page ) );
		$buttons          = '';
		$pagination_arrow = $attributes['paginationArrow'] ? '<span class="wp-block-query-pagination__arrow">' . $arrow_map[ $attributes['paginationArrow'] ] . '</span>' : '';

		// Build list of load more links.
		for ( $i = $page + 1; $i <= $block_query->max_num_pages; $i++ ) {
			$buttons .= sprintf(
				$inherit ? '<a class="%s" href="%s/page/%d/" data-loading-text="%s">%s</a>' : '<a class="%s" href="?%s=%d" data-loading-text="%s">%s</a>',
				'wp-block-button__link wp-element-button wp-load-more__button',
				$inherit ? '' : esc_html( $page_key ),
				(int) $i,
				esc_html( $attributes['loadingText'] ),
				esc_html( $attributes['loadMoreText'] ) . $pagination_arrow
			);
		}

		return $infinite_scroll_markup . '
			<div class="is-layout-flex wp-block-buttons">
				<div class="wp-block-button aligncenter">
					' . wp_kses_post( $buttons ) . '
				</div>
			</div>
		';
	}
}
