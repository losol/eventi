<?php


class Eventi_Register_Cpt {
	private $plugin_name;
	private $version;

	function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		// Register the custom post type
		add_action( 'init', array( $this, 'eventi_register_cpt' ) );

		// Add event categories
		add_action( 'init', array( $this, 'eventi_eventcategory_taxonomy' ), 0 );

		// Change columns in admin
		add_filter( 'manage_eventi_posts_columns', array( $this, 'eventi_edit_columns' ) );
		add_action( 'manage_posts_custom_column', array( $this, 'eventi_custom_columns' ) );

		// Add meta box
		add_action( 'admin_init', array( $this, 'eventi_add_metabox' ) );

		// Styles and scripts
		$this->eventi_styles_and_scripts();

		// Save post
		add_action( 'save_post', array( $this, 'save_eventi' ) );
		add_filter( 'post_updated_messages', array( $this, 'events_updated_messages' ) );
	}

	function eventi_register_cpt() {

		$labels = array(
			'name'               => _x( 'Events', 'post type general name' ),
			'singular_name'      => _x( 'Event', 'post type singular name' ),
			'add_new'            => _x( 'Add New', 'events' ),
			'add_new_item'       => __( 'Add New Event' ),
			'edit_item'          => __( 'Edit Event' ),
			'new_item'           => __( 'New Event' ),
			'view_item'          => __( 'View Event' ),
			'search_items'       => __( 'Search Events' ),
			'not_found'          => __( 'No events found' ),
			'not_found_in_trash' => __( 'No events found in Trash' ),
			'parent_item_colon'  => '',
		);

		$args = array(
			'label'             => __( 'Events' ),
			'labels'            => $labels,
			'public'            => true,
			'show_in_rest'      => true,
			'has_archive'       => true,
			'can_export'        => true,
			'show_ui'           => true,
			'_builtin'          => false,
			'capability_type'   => 'post',
			'menu_icon'         => 'dashicons-calendar-alt',
			'hierarchical'      => false,
			'rewrite'           => array( 'slug' => get_option( 'eventi_slug' ) ),
			'supports'          => array( 'title', 'thumbnail', 'excerpt', 'editor' ),
			'show_in_nav_menus' => true,
			'taxonomies'        => array( 'eventi_eventcategory', 'post_tag' ),
		);

		register_post_type( 'eventi', $args );

	}

	function eventicategory_taxonomy() {

		$labels = array(
			'name'                       => _x( 'Categories', 'taxonomy general name' ),
			'singular_name'              => _x( 'Category', 'taxonomy singular name' ),
			'search_items'               => __( 'Search Categories' ),
			'popular_items'              => __( 'Popular Categories' ),
			'all_items'                  => __( 'All Categories' ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Category' ),
			'update_item'                => __( 'Update Category' ),
			'add_new_item'               => __( 'Add New Category' ),
			'new_item_name'              => __( 'New Category Name' ),
			'separate_items_with_commas' => __( 'Separate categories with commas' ),
			'add_or_remove_items'        => __( 'Add or remove categories' ),
			'choose_from_most_used'      => __( 'Choose from the most used categories' ),
		);

		register_taxonomy(
			'eventicategory',
			'eventi',
			array(
				'label'        => __( 'Event Category' ),
				'labels'       => $labels,
				'hierarchical' => true,
				'show_ui'      => true,
				'query_var'    => true,
				'rewrite'      => array( 'slug' => 'event-category' ),
			)
		);
	}

	function eventi_edit_columns( $columns ) {

		$columns = array(
			'cb'              => '<input type="checkbox" />',
			'title'           => 'Event',
			'eventi_col_desc' => 'Description',
			'eventi_col_date' => 'Dates',
			'eventi_col_cat'  => 'Category',
		);
		return $columns;
	}

	function eventi_custom_columns( $column ) {
		global $post;
		$custom      = get_post_custom();
		$date_format = get_option( 'date_format' );
		$time_format = get_option( 'time_format' );

		switch ( $column ) {
			case 'eventi_col_desc':
				the_excerpt();
				break;
			case 'eventi_col_date':
				$meta_startdate = $custom['eventi_startdate'][0];
				$meta_enddate   = $custom['eventi_enddate'][0];
				$meta_starttime = $custom['eventi_starttime'][0];
				$meta_endtime   = $custom['eventi_endtime'][0];

				$formatted_time = date_i18n( $date_format, strtotime( $meta_startdate ) );
				if ( null != $meta_starttime ) {
					$formatted_time .= ': ' . date_i18n( $time_format, strtotime( $meta_starttime ) );
				}

				if ( null != $meta_enddate ) {
					if ( $meta_startdate != $meta_enddate ) {
						$formatted_time .= '<br> &mdash; ' . date_i18n( $date_format, strtotime( $meta_enddate ) );
					} else {
						$formatted_time .= '-';
					}

					if ( null != $meta_endtime ) {
						$formatted_time .= date_i18n( $time_format, strtotime( $meta_endtime ) );
					}
				}

				echo $formatted_time;
				break;
			case 'eventi_col_cat':
				// - show taxonomy terms -
				$eventcats      = get_the_terms( $post->ID, 'eventicategory' );
				$eventcats_html = array();
				if ( $eventcats ) {
					foreach ( $eventcats as $eventcat ) {
						array_push( $eventcats_html, $eventcat->name );
					}
					echo implode( $eventcats_html, ', ' );
				} else {
					_e( 'None', 'eventi' );

				}
				break;

		}
	}

	function eventi_add_metabox() {
		add_meta_box( 'eventi_render_admin_metabox', 'Event time', array( $this, 'eventi_render_admin_metabox' ), 'eventi' );
	}

	function eventi_render_admin_metabox() {

		// Get post meta.
		global $post;
		$custom         = get_post_custom( $post->ID );
		$meta_startdate = $custom['eventi_startdate'][0];
		$meta_enddate   = $custom['eventi_enddate'][0];
		$meta_starttime = $custom['eventi_starttime'][0];
		$meta_endtime   = $custom['eventi_endtime'][0];

		// WP nonce
		echo '<input type="hidden" name="eventi-events-nonce" id="eventi-events-nonce" value="' .
		wp_create_nonce( 'eventi-events-nonce' ) . '" />';
		?>
			<div class="tf-meta">
			<ul>
				<li><label>Start Date</label><input name="eventi_startdate" class="tfdate" value="<?php echo esc_attr( $meta_startdate ); ?>" /><em> YYYY-MM-DD, like 2019-12-31</em></li>
				<li><label>Start Time</label><input name="eventi_starttime" value="<?php echo esc_attr( $meta_starttime ); ?>" /><em> Use 24h format (7pm = 19:00)</em></li>
				<li><label>End Date</label><input name="eventi_enddate" class="tfdate" value="<?php echo esc_attr( $meta_enddate ); ?>" /><em> YYYY-MM-DD, like 2019-12-31</em></li>
				<li><label>End Time</label><input name="eventi_endtime" value="<?php echo esc_attr( $meta_endtime ); ?>" /><em> Use 24h format (7pm = 19:00)</em></li>
			</ul>
			</div>
		<?php
	}

	function eventi_styles_and_scripts() {
		add_action( 'admin_print_styles-post.php', array( $this, 'events_styles' ), 1000 );
		add_action( 'admin_print_styles-post-new.php', array( $this, 'events_styles' ), 1000 );

		add_action( 'admin_print_scripts-post.php', array( $this, 'events_scripts' ), 1000 );
		add_action( 'admin_print_scripts-post-new.php', array( $this, 'events_scripts' ), 1000 );
	}


	function events_styles() {
		global $post_type;
		if ( 'eventi' != $post_type ) {
			return;
		}
		wp_enqueue_style( 'jquery-ui-datepicker-style', '//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css' );
		wp_enqueue_style( $this->plugin_name . '-eventi-admin', plugin_dir_url( __DIR__ ) . 'admin/css/eventi-admin.css', array(), $this->version, 'all' );
	}

	function events_scripts() {
		global $post_type;
		if ( 'eventi' != $post_type ) {
			return;
		}

		wp_enqueue_script( $this->plugin_name . '-eventi-admin', plugin_dir_url( __DIR__ ) . 'admin/js/eventi-admin.js', array( 'jquery', 'jquery-ui-datepicker' ) );
	}



	function save_eventi() {

		global $post;

		// Require nonce
		if ( ! wp_verify_nonce( $_POST['eventi-events-nonce'], 'eventi-events-nonce' ) ) {
			return $post->ID;
		}

		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return $post->ID;
		}

		// Start date is mandatory
		if ( ! isset( $_POST['eventi_startdate'] ) ) :
			return $post;
		endif;

		// Update start date.
		$update_startdate = strtotime( sanitize_text_field( $_POST['eventi_startdate'] ) );
		update_post_meta( $post->ID, 'eventi_startdate', date( 'Y-m-d', $update_startdate ) );

		// Update end date if submitted.
		if ( null != $_POST['eventi_enddate'] ) {
			$update_enddate = strtotime( sanitize_text_field( $_POST['eventi_enddate'] ) );
			update_post_meta( $post->ID, 'eventi_enddate', date( 'Y-m-d', $update_enddate ) );
		} else {
			update_post_meta( $post->ID, 'eventi_enddate', null );
		}

		// Update start and end time if matches regex pattern.
		$update_starttime = sanitize_text_field( $_POST['eventi_starttime'] );
		if ( preg_match( '/^(?:2[0-3]|[01][0-9]):[0-5][0-9]$/', $update_starttime ) ) {
			update_post_meta( $post->ID, 'eventi_starttime', $update_starttime );
		} else {
			update_post_meta( $post->ID, 'eventi_starttime', null );
		}

		$update_endtime = sanitize_text_field( $_POST['eventi_endtime'] );
		if ( preg_match( '/^(?:2[0-3]|[01][0-9]):[0-5][0-9]$/', $update_endtime ) ) {
			update_post_meta( $post->ID, 'eventi_endtime', $update_endtime );
		} else {
			update_post_meta( $post->ID, 'eventi_endtime', null );
		}

	}

	function events_updated_messages( $messages ) {

		global $post, $post_ID;

		$messages['eventi'] = array(
			0  => '', // Unused. Messages start at index 1.
			1  => sprintf( __( 'Event updated. <a href="%s">View item</a>' ), esc_url( get_permalink( $post_ID ) ) ),
			2  => __( 'Custom field updated.' ),
			3  => __( 'Custom field deleted.' ),
			4  => __( 'Event updated.' ),
			/* translators: %s: date and time of the revision */
			5  => isset( $_GET['revision'] ) ? sprintf( __( 'Event restored to revision from %s' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6  => sprintf( __( 'Event published. <a href="%s">View event</a>' ), esc_url( get_permalink( $post_ID ) ) ),
			7  => __( 'Event saved.' ),
			8  => sprintf( __( 'Event submitted. <a target="_blank" href="%s">Preview event</a>' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
			9  => sprintf(
				__( 'Event scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview event</a>' ),
				// translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ),
				esc_url( get_permalink( $post_ID ) )
			),
			10 => sprintf( __( 'Event draft updated. <a target="_blank" href="%s">Preview event</a>' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
		);

		return $messages;
	}

}

