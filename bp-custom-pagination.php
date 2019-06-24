<?php
/**
 * @package BuddyPress_Custom_Pagination
 * @version 1.7.2
 */
/*
Plugin Name: BuddyPress Custom Pagination
Plugin URI: https://upnrunn.com/
Description: This is a simple plugin to customize the paginated links for BuddyPress members and groups directory pages.
Author: Upnrunn
Version: 1.0.0
Author URI: https://upnrunn.com/
*/

// Retrieve paginated links for BuddyPress directiory pages.
function bp_custom_pagination_paginate_links( $args ) {
	/**
	 * Define the array of defaults
	 * @var array
	 */
	$defaults = [
		'current'       => 1,
		'total_items'   => 0,
		'total_pages'   => 0,
		'disable_first' => false,
		'disable_last'  => false,
		'disable_prev'  => false,
		'disable_next'  => false,
		'base_url'      => '',
	];

	/**
	 * Parse incoming $args into an array and merge it with $defaults
	 * @var [type]
	 */
	$args = wp_parse_args( $args, $defaults );

	if ( 1 == $args['current'] ) {
		$args['disable_first'] = true;
		$args['disable_prev']  = true;
	}

	if ( 2 == $args['current'] ) {
		$args['disable_first'] = true;
	}

	if ( $args['current'] == $args['total_pages'] ) {
		$args['disable_last'] = true;
		$args['disable_next'] = true;
	}

	if ( $args['current'] == $args['total_pages'] - 1 ) {
		$args['disable_last'] = true;
	}

	$output = '<span class="displaying-num">' . sprintf( _n( '%s item', '%s items', $args['total_items'] ), number_format_i18n( $args['total_items'] ) ) . '</span>';

	$page_links = [];

	if ( $args['disable_first'] ) {
		$page_links[] = '<span class="tablenav-pages-navspan disabled" aria-hidden="true">&laquo;</span>';
	} else {
		$page_links[] = sprintf(
			"<a class='first button page-numbers' href='%s'>%s</a>",
			esc_url( add_query_arg( 'upage', 1, $args['base_url'] ) ),
			'&laquo;'
		);
	}

	if ( $args['disable_prev'] ) {
		$page_links[] = '<span class="tablenav-pages-navspan disabled" aria-hidden="true">&lsaquo;</span>';
	} else {
		$page_links[] = sprintf(
			"<a class='prev button page-numbers' href='%s'>%s</a>",
			esc_url( add_query_arg( 'upage', max( 1, $args['current'] - 1 ), $args['base_url'] ) ),
			'&lsaquo;'
		);
	}

	$html_current_page = sprintf(
		"%s<input class='current-page' id='current-page-selector' type='text' name='upage' value='%s' size='%d' aria-describedby='table-paging' /><span class='tablenav-paging-text'>",
		'<span class="screen-reader-text current">' . $args['current'] . '</span><span class="screen-reader-text last">' . $args['total_pages'] . '</span>',
		$args['current'],
		strlen( $args['total_pages'] )
	);

	$html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $args['total_pages'] ) );
	$page_links[]     = '<span class="paging-input">' . sprintf( _x( '%1$s of %2$s', 'paging' ), $html_current_page, $html_total_pages ) . '</span></span>';

	if ( $args['disable_next'] ) {
		$page_links[] = '<span class="tablenav-pages-navspan disabled" aria-hidden="true">&rsaquo;</span>';
	} else {
		$page_links[] = sprintf(
			"<a class='next button page-numbers' href='%s'>%s</a>",
			esc_url( add_query_arg( 'upage', min( $args['total_pages'], $args['current'] + 1 ), $args['base_url'] ) ),
			'&rsaquo;'
		);
	}

	if ( $args['disable_last'] ) {
		$page_links[] = '<span class="tablenav-pages-navspan disabled" aria-hidden="true">&raquo;</span>';
	} else {
		$page_links[] = sprintf(
			"<a class='last button page-numbers' href='%s'>%s</a>",
			esc_url( add_query_arg( 'upage', $args['total_pages'], $args['base_url'] ) ),
			'&raquo;'
		);
	}

	$output .= "\n" . join( "\n", $page_links );

	return $output;
}

// Define the bp_custom_pagination_get_members_pagination_links callback.
function bp_custom_pagination_get_members_pagination_links( $pag_links ) {
	global $members_template;

	return bp_custom_pagination_paginate_links(
		[
			'current'     => $members_template->pag_page,
			'total_items' => $members_template->total_member_count,
			'total_pages' => ceil( (int) $members_template->total_member_count / (int) $members_template->pag_num ),
		]
	);
}

// Add the filter
add_filter( 'bp_get_members_pagination_links', 'bp_custom_pagination_get_members_pagination_links' );

// Define the bp_custom_pagination_get_groups_pagination_links callback.
function bp_custom_pagination_get_groups_pagination_links( $pag_links ) {
	global $groups_template;

	/**
	 * Check the current group is the same as the supplied group ID.
	 * It can differ when using {@link bp_group_has_members()} outside the Groups screens.
	 */
	$current_group = groups_get_current_group();
	if ( empty( $current_group ) || ( $current_group && bp_get_current_group_id() !== $current_group->id ) ) {
		$current_group = groups_get_group( bp_get_current_group_id() );
	}

	// Assemble the base URL for pagination.
	$base_url = trailingslashit( bp_get_group_permalink( $current_group ) . bp_current_action() );
	if ( bp_action_variable() ) {
		$base_url = trailingslashit( $base_url . bp_action_variable() );
	}

	return bp_custom_pagination_paginate_links(
		[
			'base_url'    => $base_url,
			'current'     => $groups_template->pag_page,
			'total_items' => $groups_template->total_group_count,
			'total_pages' => ceil( (int) $groups_template->total_group_count / (int) $groups_template->pag_num ),
		]
	);
}

// Add the filter
add_filter( 'bp_get_groups_pagination_links', 'bp_custom_pagination_get_groups_pagination_links' );

// Add the action.
add_action( 'wp_footer', 'bp_custom_pagination_footer_scripts' );

// Define the bp_custom_pagination_footer_scripts callback.
function bp_custom_pagination_footer_scripts() {
	?>
	<script>
	var jq = jQuery;
	jq(document).ready( function() {
		/* All pagination links run through this function */
		jq('#buddypress').on( 'click', function(event) {
			var target = jq(event.target),
				el,
				css_id, object, search_terms, pagination_id, template,
				page_number,
				$gm_search,
				caller;

			if ( target.parent().parent().hasClass('pagination') && !target.parent().parent().hasClass('no-ajax') ) {
				if ( target.hasClass('dots') || target.hasClass('current') ) {
					return false;
				}

				if ( jq('.item-list-tabs li.selected').length ) {
					el = jq('.item-list-tabs li.selected');
				} else {
					el = jq('li.filter select');
				}

				css_id = el.attr('id').split( '-' );
				object = css_id[0];
				search_terms = false;
				pagination_id = jq(target).closest('.pagination-links').attr('id');
				template = null;

				// Search terms
				if ( jq('div.dir-search input').length ) {
					search_terms =  jq('.dir-search input');

					if ( ! search_terms.val() && bp_get_querystring( search_terms.attr( 'name' ) ) ) {
						search_terms = jq('.dir-search input').prop('placeholder');
					} else {
						search_terms = search_terms.val();
					}
				}

				// Page number
				if ( jq(target).hasClass('next') || jq(target).hasClass('prev') ) {
					page_number = jq('.pagination span.current').html();
				} else {
					page_number = jq(target).html();
				}

				if ( jq(target).hasClass('first') ) {
					page_number = '1';
				} else if ( jq(target).hasClass('last') ) {
					page_number = jq('.pagination span.last').html();
				}

				// Remove any non-numeric characters from page number text (commas, etc.)
				page_number = Number( page_number.replace(/\D/g,'') );

				if ( jq(target).hasClass('next') ) {
					page_number++;
				} else if ( jq(target).hasClass('prev') ) {
					page_number--;
				}

				// The Group Members page has a different selector for
				// its search terms box
				$gm_search = jq( '.groups-members-search input' );
				if ( $gm_search.length ) {
					search_terms = $gm_search.val();
					object = 'members';
				}

				// On the Groups Members page, we specify a template
				if ( 'members' === object && 'groups' === css_id[1] ) {
					object = 'group_members';
					template = 'groups/single/members';
				}

				// On the Admin > Requests page, we need to reset the object,
				// since "admin" isn't specific enough
				if ( 'admin' === object && jq( 'body' ).hasClass( 'membership-requests' ) ) {
					object = 'requests';
				}

				if ( pagination_id.indexOf( 'pag-bottom' ) !== -1 ) {
					caller = 'pag-bottom';
				} else {
					caller = null;
				}

				var scope  = bp_get_directory_preference( object, 'scope' );
				var filter = bp_get_directory_preference( object, 'filter' );
				var extras = bp_get_directory_preference( object, 'extras' );

				bp_filter_request( object, filter, scope, 'div.' + object, search_terms, page_number, extras, caller, template );

				return false;
			}
		});

		jq( '#buddypress' ).on( 'change', '.pagination .current-page', function(event) {
			var target = jq(event.target),
				el,
				css_id, object, search_terms, pagination_id, template,
				page_number,
				$gm_search,
				caller;

			if ( target.parent().parent().parent().hasClass('pagination') && !target.parent().parent().parent().hasClass('no-ajax') ) {
				if ( target.hasClass('dots') ) {
					return false;
				}

				if ( jq('.item-list-tabs li.selected').length ) {
					el = jq('.item-list-tabs li.selected');
				} else {
					el = jq('li.filter select');
				}

				css_id = el.attr('id').split( '-' );
				object = css_id[0];
				search_terms = false;
				pagination_id = jq(target).closest('.pagination-links').attr('id');
				template = null;

				// Search terms
				if ( jq('div.dir-search input').length ) {
					search_terms =  jq('.dir-search input');

					if ( ! search_terms.val() && bp_get_querystring( search_terms.attr( 'name' ) ) ) {
						search_terms = jq('.dir-search input').prop('placeholder');
					} else {
						search_terms = search_terms.val();
					}
				}

				// Page number
				page_number = jq(target).val();

				// Remove any non-numeric characters from page number text (commas, etc.)
				page_number = Number( page_number.replace(/\D/g,'') );

				// The Group Members page has a different selector for
				// its search terms box
				$gm_search = jq( '.groups-members-search input' );
				if ( $gm_search.length ) {
					search_terms = $gm_search.val();
					object = 'members';
				}

				// On the Groups Members page, we specify a template
				if ( 'members' === object && 'groups' === css_id[1] ) {
					object = 'group_members';
					template = 'groups/single/members';
				}

				// On the Admin > Requests page, we need to reset the object,
				// since "admin" isn't specific enough
				if ( 'admin' === object && jq( 'body' ).hasClass( 'membership-requests' ) ) {
					object = 'requests';
				}

				if ( pagination_id.indexOf( 'pag-bottom' ) !== -1 ) {
					caller = 'pag-bottom';
				} else {
					caller = null;
				}

				var scope  = bp_get_directory_preference( object, 'scope' );
				var filter = bp_get_directory_preference( object, 'filter' );
				var extras = bp_get_directory_preference( object, 'extras' );

				bp_filter_request( object, filter, scope, 'div.' + object, search_terms, page_number, extras, caller, template );
			}
		});
	});
	</script>
	<?php
}
