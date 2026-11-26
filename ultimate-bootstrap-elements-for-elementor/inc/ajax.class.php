<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UBE_Ajax {
	private static $_instance = null;

	public static function get_instance() {
		return self::$_instance === null ? self::$_instance = new self() : self::$_instance;
	}

	/**
	 * Init Ajax
	 *
	 * @since 1.0.0
	 */
	public function init() {
		add_action( 'wp_ajax_ube_result_search_box', array( $this, 'get_search_result_callback' ) );
		add_action( 'wp_ajax_nopriv_ube_result_search_box', array( $this, 'get_search_result_callback' ) );

		add_action( 'wp_ajax_ube_load_more', array( $this, 'post_load_more_callback' ) );
		add_action( 'wp_ajax_nopriv_ube_load_more', array( $this, 'post_load_more_callback' ) );

		// Editor-only control: logged-in users with edit capability only.
		add_action( 'wp_ajax_ube_control_autocomplete', array( $this, 'control_autocomplete_callback' ) );
	}

	/**
	 * Ajax data for search box
	 *
	 * @since 1.0.0
	 */
	public function get_search_result_callback() {
		$keyword = isset( $_POST['keyword'] ) ? sanitize_text_field( $_POST['keyword'] ) : '';
		if ( $_POST['keyword'] === '' ) {
			die();
		}

		$allowed_orderby = array( 'none', 'ID', 'author', 'title', 'name', 'type', 'date', 'modified', 'parent', 'rand', 'comment_count', 'menu_order', 'relevance' );
		$orderby         = isset( $_POST['orderby'] ) ? sanitize_text_field( wp_unslash( $_POST['orderby'] ) ) : 'date';
		$order           = isset( $_POST['order'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_POST['order'] ) ) ) : 'DESC';
		$posts_per_page  = isset( $_POST['data_post_per_page'] ) ? (int) $_POST['data_post_per_page'] : 0;
		if ( $posts_per_page < 1 ) {
			$posts_per_page = (int) get_option( 'posts_per_page', 10 ); // same as an empty value in WP_Query
		}
		$posts_per_page = min( 50, $posts_per_page );

		$args = array(
			's'                   => $keyword,
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true,
			'orderby'             => in_array( $orderby, $allowed_orderby, true ) ? $orderby : 'date',
			'order'               => 'ASC' === $order ? 'ASC' : 'DESC',
			'posts_per_page'      => $posts_per_page,
		);

		$result_date = isset( $_POST['result_date'] ) ? sanitize_text_field( wp_unslash( $_POST['result_date'] ) ) : '';
		$the_query   = new WP_Query( $args );
		if ( $the_query->have_posts() ) :
			while ( $the_query->have_posts() ): $the_query->the_post(); ?>
                <li class="ube-search-box-item-result d-flex align-items-center">
					<?php $thumb = get_the_post_thumbnail( get_the_ID(), 'thumbnail' ); ?>
					<?php if ( ! empty( $thumb ) ): ?>
                        <div class="ube-search-box-result-thumbnail">
                            <a href="<?php the_permalink() ?>"
                               title="<?php the_title_attribute() ?>"><?php echo get_the_post_thumbnail( get_the_ID(), 'thumbnail' ); ?>
                            </a>
                        </div>
					<?php endif; ?>
                    <div class="ube-search-box-result-content">
                        <a class="ube-search-box-result-title" href="<?php the_permalink() ?>"
                           title="<?php the_title_attribute() ?>"><?php the_title(); ?>
                        </a>
						<?php if ( $result_date === 'yes' ): ?>
                            <div class="ube-search-box-result-meta">
								<?php echo get_the_date( get_option( 'date_format' ) ); ?>
                            </div>
						<?php endif; ?>
                    </div>
                </li>
			<?php endwhile;
			wp_reset_postdata();
		else:
			?>
            <li class="ube-search-box-result-nothing"><?php echo esc_html__( 'Sorry, but nothing matched your search terms. Please try again with different keywords.', 'ube' ); ?></li>
		<?php endif;
		die();
	}

	/**
	 * Ajax data for post load more
	 *
	 * @since 1.0.0
	 */
	public function post_load_more_callback() {
		if ( ! isset( $_REQUEST['nonceField'] )
		     || ! wp_verify_nonce( sanitize_text_field( $_REQUEST['nonceField'] ), 'ube_load_post' ) ) {
			return;
		}

		parse_str( ube_kses_post( $_REQUEST['args'] ), $args );
		parse_str( ( ube_kses_post( $_REQUEST['settings'] ) ), $settings );

		// Never trust query args coming from the request: rebuild them from an allow-list
		// and always restrict to published, non password-protected, public content.
		$args = $this->sanitize_load_more_query_args( $args );

		$page = sanitize_text_field( $_REQUEST['page'] );

		$args['offset'] = (int) $args['offset'] + ( ( (int) $page - 1 ) * (int) $args['posts_per_page'] );
		if ( isset( $_REQUEST['taxonomy'] ) ) {
			$taxonomy = ube_recursive_sanitize_text_field( $_REQUEST['taxonomy'] );

			if ( isset( $taxonomy['taxonomy'] ) && $taxonomy['taxonomy'] != 'all'
			     && taxonomy_exists( $taxonomy['taxonomy'] ) && is_taxonomy_viewable( $taxonomy['taxonomy'] ) ) {
				$field             = isset( $taxonomy['field'] ) && in_array( $taxonomy['field'], array( 'term_id', 'slug', 'name', 'term_taxonomy_id' ), true ) ? $taxonomy['field'] : 'term_id';
				$args['tax_query'] = [
					array(
						'taxonomy' => $taxonomy['taxonomy'],
						'field'    => $field,
						'terms'    => isset( $taxonomy['terms'] ) ? $taxonomy['terms'] : array(),
					),
				];
			}


		}
		$handler = ube_recursive_sanitize_text_field( $_REQUEST['handler'] );

		if ( isset( $settings['orderby'] ) && $settings['orderby'] === 'rand' && ! empty( $args['post__not_in'] ) && isset( $_REQUEST['post__not_in'] ) ) {
			$args['post__not_in'] = array_values( array_unique( array_filter( array_map( 'absint', (array) $_REQUEST['post__not_in'] ) ) ) );
		}
		$html        = ube_render_template_post( $args, $settings );
		$pagination  = '';
		$total_page  = intval( sanitize_text_field( $_REQUEST['totalPage'] ) );
		$nonce_field = wp_create_nonce( 'ube_load_post' );
		$paging      = '';
		if ( isset( $settings['paging'] ) && $settings['paging'] != '' ) {
			$paging = $settings['paging'];
			if ( $total_page > intval( $page ) ) {
				ob_start();
				if ( 'load_more' == $settings['paging'] ) {
					ube_get_template( 'post/pagination/load-more.php', array(
						'args'                => $args,
						'settings_array'      => $settings,
						'total_page'          => $total_page,
						'show_load_more_text' => $settings['show_load_more_text'],
						'nonce_field'         => $nonce_field
					) );
				} elseif ( 'pagination' == $settings['paging'] ) {
					ube_get_template( 'post/pagination/pagination.php', array(
						'args'           => $args,
						'settings_array' => $settings,
						'total_page'     => $total_page,
						'nonce_field'    => $nonce_field
					) );
				} elseif ( 'next_prev' == $settings['paging'] ) {
					ube_get_template( 'post/pagination/next-prev.php', array(
						'args'           => $args,
						'settings_array' => $settings,
						'total_page'     => $total_page,
						'nonce_field'    => $nonce_field
					) );
				} elseif ( 'scroll' == $settings['paging'] ) {
					ube_get_template( 'post/pagination/infinitive-scroll.php', array(
						'args'           => $args,
						'settings_array' => $settings,
						'total_page'     => $total_page,
						'nonce_field'    => $nonce_field
					) );
				}
				$pagination = ob_get_clean();
			}
		}


		$data_arr = array(
			'content'      => $html,
			'paging'       => $paging,
			'current_page' => $page,
			'pagination'   => $pagination,
			'target'       => '#ube-post-list-' . $settings['id'],
			'widget_id'    => $settings['id']
		);
		if ( $handler == 'category_filter' ) {
			$data_arr['filter_category'] = 'yes';
			$data_arr['paging']          = '';
		}

		wp_send_json_success( $data_arr );
		die();
	}

	/**
	 * Rebuild WP_Query args for the load more request from an allow-list.
	 *
	 * @param array $raw Args parsed from the request.
	 *
	 * @return array
	 */
	private function sanitize_load_more_query_args( $raw ) {
		$raw = is_array( $raw ) ? $raw : array();

		$public_types = array_values( get_post_types( array( 'public' => true ) ) );
		$post_type    = isset( $raw['post_type'] ) ? $raw['post_type'] : 'post';
		if ( 'any' !== $post_type ) {
			$post_type = array_values( array_intersect( array_map( 'sanitize_key', (array) $post_type ), $public_types ) );
			if ( empty( $post_type ) ) {
				$post_type = 'post';
			}
		}

		$allowed_orderby = array( 'none', 'ID', 'author', 'title', 'name', 'type', 'date', 'modified', 'parent', 'rand', 'comment_count', 'menu_order', 'post__in' );
		$orderby         = isset( $raw['orderby'] ) && in_array( $raw['orderby'], $allowed_orderby, true ) ? $raw['orderby'] : 'date';
		$order           = isset( $raw['order'] ) && 'asc' === strtolower( $raw['order'] ) ? 'ASC' : 'DESC';

		// -1 = "all posts" (used when the widget's posts per page is empty), same as before.
		$posts_per_page = isset( $raw['posts_per_page'] ) ? (int) $raw['posts_per_page'] : (int) get_option( 'posts_per_page', 10 );
		if ( 0 === $posts_per_page || $posts_per_page < -1 ) {
			$posts_per_page = (int) get_option( 'posts_per_page', 10 );
		}

		// Password protected posts stay listed (like WordPress archives and the widget's first page);
		// their content is already hidden by WordPress.
		$args = array(
			'post_type'           => $post_type,
			'post_status'         => 'publish',
			'perm'                => 'readable',
			'ignore_sticky_posts' => 1,
			'orderby'             => $orderby,
			'order'               => $order,
			'posts_per_page'      => $posts_per_page,
			'offset'              => isset( $raw['offset'] ) ? absint( $raw['offset'] ) : 0,
		);

		foreach ( array( 'post__in', 'post__not_in', 'author__in' ) as $key ) {
			if ( ! empty( $raw[ $key ] ) ) {
				$args[ $key ] = array_values( array_filter( array_map( 'absint', (array) $raw[ $key ] ) ) );
				if ( 'post__in' === $key && empty( $args[ $key ] ) ) {
					$args[ $key ] = array( 0 );
				}
			}
		}

		if ( ! empty( $raw['tax_query'] ) && is_array( $raw['tax_query'] ) ) {
			$tax_query = array();
			foreach ( $raw['tax_query'] as $k => $clause ) {
				if ( 'relation' === $k ) {
					continue;
				}
				if ( ! is_array( $clause ) || empty( $clause['taxonomy'] ) || ! taxonomy_exists( $clause['taxonomy'] ) || ! is_taxonomy_viewable( $clause['taxonomy'] ) ) {
					continue;
				}
				$field       = isset( $clause['field'] ) && in_array( $clause['field'], array( 'term_id', 'slug', 'name', 'term_taxonomy_id' ), true ) ? $clause['field'] : 'term_id';
				$terms       = isset( $clause['terms'] ) ? (array) $clause['terms'] : array();
				$terms       = 'term_id' === $field || 'term_taxonomy_id' === $field ? array_map( 'absint', $terms ) : array_map( 'sanitize_text_field', $terms );
				$tax_query[] = array(
					'taxonomy' => $clause['taxonomy'],
					'field'    => $field,
					'terms'    => $terms,
				);
			}
			if ( ! empty( $tax_query ) ) {
				$tax_query['relation'] = 'AND';
				$args['tax_query']     = $tax_query;
			}
		}

		return $args;
	}

	public function control_autocomplete_callback() {
		if ( ! isset( $_REQUEST['ube_autocomplete_nonce'] )
		     || ! wp_verify_nonce( sanitize_text_field( $_REQUEST['ube_autocomplete_nonce'] ), 'ube_autocomplete_action' ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( null, 403 );
		}
		$type          = sanitize_text_field( $_REQUEST['type'] );
		$search        = sanitize_text_field( $_REQUEST['search'] );
		$page          = sanitize_text_field( $_REQUEST['page'] );
		$raw_args  = isset( $_REQUEST['data_args'] ) ? ube_recursive_sanitize_text_field( (array) $_REQUEST['data_args'] ) : array();
		$data_args = array( 'post_status' => 'publish' );
		if ( ! empty( $raw_args['post_type'] ) ) {
			$data_args['post_type'] = array_values( array_intersect( (array) $raw_args['post_type'], get_post_types( array( 'public' => true ) ) ) );
			if ( empty( $data_args['post_type'] ) ) {
				$data_args['post_type'] = array( 'post' );
			}
		}
		if ( ! empty( $raw_args['taxonomy'] ) ) {
			$data_args['taxonomy'] = array_values( array_filter( (array) $raw_args['taxonomy'], 'is_taxonomy_viewable' ) );
		}
		if ( isset( $raw_args['posts_per_page'] ) ) {
			$data_args['posts_per_page'] = min( 100, max( 1, (int) $raw_args['posts_per_page'] ) );
		}
		$current_value = isset( $_REQUEST['currentValue'] ) ? ube_recursive_sanitize_text_field( $_REQUEST['currentValue'] ) : '';

		$init_control = isset( $_REQUEST['initControl'] );
		if ( ! $init_control ) {
			$current_value = - 1;
		}

		echo json_encode( ube_get_query_data_for_autocomplete( $type, $search, $page, $data_args, $current_value ) );
		die();
	}
}