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
		add_filter( 'manage_edit-eventi_columns', 'eventi_edit_columns' );
		add_action( 'manage_posts_custom_column', 'eventi_custom_columns' );

		// Add meta box
		add_action( 'admin_init', array( $this, 'eventi_add_metabox' ) );

		// Styles and scripts
		$this->eventi_styles_and_scripts();

		// Save post
		add_action( 'save_post', array( $this, 'save_eventi_event' ) );
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
			'can_export'        => true,
			'show_ui'           => true,
			'_builtin'          => false,
			'capability_type'   => 'post',
			'menu_icon'         => 'dashicons-calendar-alt',
			'hierarchical'      => false,
			'rewrite'           => array( 'slug' => 'events' ),
			'supports'          => array( 'title', 'thumbnail', 'excerpt', 'editor' ),
			'show_in_nav_menus' => true,
			'taxonomies'        => array( 'eventi_eventcategory', 'post_tag' ),
		);

		register_post_type( 'eventi_event', $args );

	}

	function eventi_eventcategory_taxonomy() {

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
			'eventi_eventcategory',
			'eventi_event',
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
			'tf_col_ev_cat'   => 'Category',
			'tf_col_ev_date'  => 'Dates',
			'tf_col_ev_times' => 'Times',
			'tf_col_ev_thumb' => 'Thumbnail',
			'title'           => 'Event',
			'tf_col_ev_desc'  => 'Description',
		);
		return $columns;
	}

	function eventi_custom_columns( $column ) {
		global $post;
		$custom = get_post_custom();
		switch ( $column ) {
			case 'tf_col_ev_cat':
				// - show taxonomy terms -
				$eventcats      = get_the_terms( $post->ID, 'tf_eventcategory' );
				$eventcats_html = array();
				if ( $eventcats ) {
					foreach ( $eventcats as $eventcat ) {
						array_push( $eventcats_html, $eventcat->name );
					}
					echo implode( $eventcats_html, ', ' );
				} else {
					_e( 'None', 'themeforce' );

				}
				break;
			case 'tf_col_ev_date':
				// - show dates -
				$startd    = $custom['eventi_startdate'][0];
				$endd      = $custom['eventi_enddate'][0];
				$startdate = date( 'F j, Y', $startd );
				$enddate   = date( 'F j, Y', $endd );
				echo $startdate . '<br /><em>' . $enddate . '</em>';
				break;
			case 'tf_col_ev_times':
				// - show times -
				$startt      = $custom['eventi_startdate'][0];
				$endt        = $custom['eventi_enddate'][0];
				$time_format = get_option( 'time_format' );
				$starttime   = date( $time_format, $startt );
				$endtime     = date( $time_format, $endt );
				echo $starttime . ' - ' . $endtime;
				break;
			case 'tf_col_ev_thumb':
				// - show thumb -
				$post_image_id = get_post_thumbnail_id( get_the_ID() );
				if ( $post_image_id ) {
					$thumbnail = wp_get_attachment_image_src( $post_image_id, 'post-thumbnail', false );
					if ( $thumbnail ) {
						(string) $thumbnail = $thumbnail[0];
					}
					echo '<img src="';
					echo bloginfo( 'template_url' );
					echo '/timthumb/timthumb.php?src=';
					echo $thumbnail;
					echo '&h=60&w=60&zc=1" alt="" />';
				}
				break;
			case 'tf_col_ev_desc':
				the_excerpt();
				break;

		}
	}

	function eventi_add_metabox() {
		add_meta_box( 'eventi_render_admin_metabox', 'Event time', array( $this, 'eventi_render_admin_metabox' ), 'eventi_event' );
	}

	function eventi_render_admin_metabox() {

		// - grab data -
		global $post;
		$custom  = get_post_custom( $post->ID );
		$meta_sd = $custom['eventi_startdate'][0];
		$meta_ed = $custom['eventi_enddate'][0];
		$meta_st = $meta_sd;
		$meta_et = $meta_ed;

		// - grab wp time format -
		$date_format = get_option( 'date_format' ); // Not required in my code
		$time_format = get_option( 'time_format' );

		// - populate today if empty, 00:00 for time -
		if ( $meta_sd == null ) {
			$meta_sd = time();
			$meta_ed = $meta_sd;
			$meta_st = 0;
			$meta_et = 0;}

		// - convert to pretty formats -
		$clean_sd = date( 'D, M d, Y', $meta_sd );
		$clean_ed = date( 'D, M d, Y', $meta_ed );
		$clean_st = date( $time_format, $meta_st );
		$clean_et = date( $time_format, $meta_et );

		// - security -
		echo '<input type="hidden" name="eventi-events-nonce" id="eventi-events-nonce" value="' .
		wp_create_nonce( 'eventi-events-nonce' ) . '" />';
		// - output -
		?>
			<div class="tf-meta">
			<ul>
				<li><label>Start Date</label><input name="eventi_startdate" class="tfdate" value="<?php echo $clean_sd; ?>" /></li>
				<li><label>Start Time</label><input name="eventi_starttime" value="<?php echo $clean_st; ?>" /><em>Use 24h format (7pm = 19:00)</em></li>
				<li><label>End Date</label><input name="eventi_enddate" class="tfdate" value="<?php echo $clean_ed; ?>" /></li>
				<li><label>End Time</label><input name="eventi_endtime" value="<?php echo $clean_et; ?>" /><em>Use 24h format (7pm = 19:00)</em></li>
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
		if ( 'eventi_event' != $post_type ) {
			return;
		}
		wp_enqueue_style( 'jquery-ui-datepicker-style', '//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css' );
		wp_enqueue_style( $this->plugin_name . '-eventi-admin', plugin_dir_url( __DIR__ ) . 'admin/css/eventi-admin.css', array(), $this->version, 'all' );
	}

	function events_scripts() {
		global $post_type;
		if ( 'eventi_event' != $post_type ) {
			return;
		}

		wp_enqueue_script( $this->plugin_name . '-eventi-admin', plugin_dir_url( __DIR__ ) . 'admin/js/eventi-admin.js', array( 'jquery', 'jquery-ui-datepicker' ) );
	}



	function save_eventi_event() {

		global $post;

		// - still require nonce
		if ( ! wp_verify_nonce( $_POST['eventi-events-nonce'], 'eventi-events-nonce' ) ) {
			return $post->ID;
		}

		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return $post->ID;
		}

		// - convert back to unix & update post
		if ( ! isset( $_POST['eventi_startdate'] ) ) :
			return $post;
		endif;
		$updatestartd = strtotime( $_POST['eventi_startdate'] . $_POST['eventi_starttime'] );
		update_post_meta( $post->ID, 'eventi_startdate', $updatestartd );

		if ( ! isset( $_POST['eventi_enddate'] ) ) :
			return $post;
		endif;
		$updateendd = strtotime( $_POST['eventi_enddate'] . $_POST['eventi_endtime'] );
		update_post_meta( $post->ID, 'eventi_enddate', $updateendd );

	}

	function events_updated_messages( $messages ) {

		global $post, $post_ID;

		$messages['eventi_event'] = array(
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

