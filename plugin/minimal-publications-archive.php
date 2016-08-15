<?php
/*
Plugin Name: Minimal Publications Archive
Plugin URI: https://github.com/bhaettasch/WPMinimalPublicationsArchive
Version: 0.1
Author: Benjamin Haettasch
E-Mail: Benjamin.Haettasch@igd.fraunhofer.de / h@ttas.ch
Description: Manage and display your (scientific) publications
*/

/*
 * ====================================================================================================================
 * Activation/Deactivation hooks
 * ====================================================================================================================
 */

/**
 * Manual plugin activation hook
 */
function pa_activation() {
	//Nothing to do
}
register_activation_hook(__FILE__, 'pa_activation');

/**
 * Manual deactivation hook
 */
function pa_deactivation() {
	//Nothing to do
}
register_deactivation_hook(__FILE__, 'pa_deactivation');

/**
 * Info box to display when requesting credentials from user
 */
function pa_admin_notice() {
	?>
	<div class="updated">
		<p><?php echo "The plugin needs filesystem access to cache the publications json file" ?></p>
	</div>
<?php
}


/**
 * ====================================================================================================================
 * Post types and taxonomies
 * ====================================================================================================================
 */

/**
 * Create post type for publication and taxonomies for tags, types and categories
 */
function pa_register_publication_entry() {

	// ------------------------------------------------------------------------
	// Publication Entry
	// ------------------------------------------------------------------------

	$labels = array(
        'menu_name'                  => _x('Publications', 'pa_publication'),
        'name'                       => 'Publications',
        'singular_name'              => 'Publication',
        'all_items'                  => 'All Publications',
        'parent_item'                => 'Parent Publication',
        'parent_item_colon'          => 'Parent Publication:',
        'new_item_name'              => 'New Publication Name:',
        'add_new_item'               => 'Add New Publication',
        'edit_item'                  => 'Edit Publication',
        'update_item'                => 'Update Publication',
        'separate_items_with_commas' => 'Separate items with commas',
        'search_items'               => 'Search Publications',
        'add_or_remove_items'        => 'Add or remove Publications',
        'choose_from_most_used'      => 'Choose from the most used Publications',
        'not_found'                  => 'Not Found'
    );

    $args = array(
        'labels' => $labels,
        'hierarchical' => false,
        'description' => 'Publication entry',
        'supports' => array('title', 'editor', 'thumbnail'),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_nav_menus' => true,
        'publicly_queryable' => true,
        'exclude_from_search' => false,
        'has_archive' => true,
        'query_var' => true,
        'can_export' => true,
        'rewrite' => array( 'slug' => 'publications' ),
        'capability_type' => 'post',
        'menu_icon' => 'dashicons-media-document'
    );

    register_post_type('pa_publication', $args);


	// ------------------------------------------------------------------------
	// Publication Tags
	// ------------------------------------------------------------------------

    register_taxonomy(
        'publication-tag',
        'pa_publication',
        array(
            'label' => __( 'Tags' ),
            'rewrite' => array( 'slug' => 'publication-tag' )
        )
    );


	// ------------------------------------------------------------------------
	// Publication Types
	// ------------------------------------------------------------------------

    $labels = array(
        'name'                       => 'Types',
        'singular_name'              => 'Type',
        'menu_name'                  => 'Type',
        'all_items'                  => 'All Types',
        'parent_item'                => 'Parent Type',
        'parent_item_colon'          => 'Parent Type:',
        'new_item_name'              => 'New Type Name',
        'add_new_item'               => 'Add New Type',
        'edit_item'                  => 'Edit Type',
        'update_item'                => 'Update Type',
        'separate_items_with_commas' => 'Separate types with commas',
        'search_items'               => 'Search Types',
        'add_or_remove_items'        => 'Add or remove types',
        'choose_from_most_used'      => 'Choose from the most used types',
        'not_found'                  => 'Not Found'
    );

    $rewrite = array(
        'slug'                       => 'publication-type',
        'with_front'                 => true,
        'hierarchical'               => false,
    );

    $args = array(
        'labels'                     => $labels,
        'hierarchical'               => true,
        'public'                     => true,
        'show_ui'                    => true,
        'show_admin_column'          => true,
        'show_in_nav_menus'          => true,
        'show_tagcloud'              => true,
        'rewrite'                    => $rewrite,
    );

    register_taxonomy( 'publication_type', array( 'pa_publication' ), $args );


	// ------------------------------------------------------------------------
	// Publication Categories
	// ------------------------------------------------------------------------

    $labels = array(
        'name'                       => 'Categories',
        'singular_name'              => 'Category',
        'menu_name'                  => 'Category',
        'all_items'                  => 'All Categories',
        'parent_item'                => 'Parent Category',
        'parent_item_colon'          => 'Parent Category:',
        'new_item_name'              => 'New Category Name',
        'add_new_item'               => 'Add New Category',
        'edit_item'                  => 'Edit Category',
        'update_item'                => 'Update Category',
        'separate_items_with_commas' => 'Separate items with commas',
        'search_items'               => 'Search Categories',
        'add_or_remove_items'        => 'Add or remove Categories',
        'choose_from_most_used'      => 'Choose from the most used Categories',
        'not_found'                  => 'Not Found',
    );

    $rewrite = array(
        'slug'                       => 'publication-category',
        'with_front'                 => true,
        'hierarchical'               => false,
    );

    $args = array(
        'labels'                     => $labels,
        'hierarchical'               => true,
        'public'                     => true,
        'show_ui'                    => true,
        'show_admin_column'          => true,
        'show_in_nav_menus'          => false,
        'show_tagcloud'              => false,
        'rewrite'                    => $rewrite,
    );

    register_taxonomy( 'publication_category', array( 'pa_publication' ), $args );
}

add_action('init', 'pa_register_publication_entry');


/*
 * ====================================================================================================================
 * Meta Data
 * Allow additional data for publication entry (like a link to a pdf of the paper, different urls, ...)
 * This block covers all necessary methods and backend interface manipulations
 * ====================================================================================================================
 */

/**
 * Add additional box to enter meta data in create/update publication entry view
 */
function pa_add_meta_box()
{
	add_meta_box("pa_meta_box", "Meta-Daten", 'pa_meta_box', "pa_publication", "normal");
}
add_action('add_meta_boxes', 'pa_add_meta_box');

/**
 * Create meta box
 */
function pa_meta_box() {
	global $post;

	// ------------------------------------------------------------------------
	// Get properties for the current publication (if already saved/updating)
	// ------------------------------------------------------------------------
	$publication_url =  	get_post_meta($post->ID, "_publication_url", true);
	$authors =  	        get_post_meta($post->ID, "_authors", true);
	$proceeding =  	        get_post_meta($post->ID, "_proceeding", true);
	$page_url =     		get_post_meta($post->ID, "_page_url", true);
	$supp_material_url =    get_post_meta($post->ID, "_supp_material_url", true);
	$video_url =    		get_post_meta($post->ID, "_video_url", true);
	$bibtex_url =    		get_post_meta($post->ID, "_bibtex_url", true);

	// ------------------------------------------------------------------------
	// Create HTML code
	// ------------------------------------------------------------------------

	// Use nonce for verification
	$html =  '<input type="hidden" name="pa_publication_meta_box_nonce" value="'. wp_create_nonce(basename(__FILE__)). '" />';

	$html .= '
    '; $html .= '
    <table class="form-table">
        <tbody>
        <tr>
            <th><label for="publication_url">Publication URL</label></th>
            <td><input id="publication_url" type="text" name="publication_url" value="'.$publication_url.'" style="width: 500px;"/></td>
        </tr>
        <tr>
            <th><label for="authors">Authors</label></th>
            <td><input id="authors" type="text" name="authors" value="'.$authors.'" style="width: 500px;"/></td>
        </tr>
        <tr>
            <th><label for="proceeding">Proceeding</label></th>
            <td><input id="proceeding" type="text" name="proceeding" value="'.$proceeding.'" style="width: 500px;"/></td>
        </tr>
        <tr>
            <th><label for="page_url">Project Page URL</label></th>
            <td><input id="page_url" type="text" name="page_url" value="'.$page_url.'" style="width: 500px;"/></td>
        </tr>
        <tr>
            <th><label for="supp_material_url">Supplemental Material URL</label></th>
            <td><input id="supp_material_url" type="text" name="supp_material_url" value="'.$supp_material_url.'" style="width: 500px;"/></td>
        </tr>
        <tr>
            <th><label for="video_url">Video URL</label></th>
            <td><input id="video_url" type="text" name="video_url" value="'.$video_url.'" style="width: 500px;"/></td>
        </tr>
        <tr>
            <th><label for="bibtex_url">Bibtex URL</label></th>
            <td><input id="bibtex_url" type="text" name="bibtex_url" value="'.$bibtex_url.'" style="width: 500px;"/></td>
        </tr>
        </tbody>
    </table>
    ';

	echo $html;
}

/**
 * Custom saving hook for publication entry (to store the meta data with it)
 *
 * @param $post_id int id of the publication to save
 * @return mixed
 */
function pa_save_publication($post_id) {
	// verify nonce
	if (!wp_verify_nonce($_POST['pa_publication_meta_box_nonce'], basename(__FILE__)))
	{
		return $post_id;
	}

	// check autosave
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return $post_id;
	}

	// check permissions
	if ('pa_publication' == $_POST['post_type'] && current_user_can('edit_post', $post_id))
	{
		/* Save Meta Data */
		$publication_url = (isset($_POST['publication_url']) ? $_POST['publication_url'] : '');
        update_post_meta($post_id, "_publication_url", $publication_url);

        $authors = (isset($_POST['authors']) ? $_POST['authors'] : '');
        update_post_meta($post_id, "_authors", $authors);

        $proceeding = (isset($_POST['proceeding']) ? $_POST['proceeding'] : '');
        update_post_meta($post_id, "_proceeding", $proceeding);

		$page_url = (isset($_POST['page_url']) ? $_POST['page_url'] : '');
		update_post_meta($post_id, "_page_url", $page_url);

		$supp_material_url = (isset($_POST['supp_material_url']) ? $_POST['supp_material_url'] : '');
		update_post_meta($post_id, "_supp_material_url", $supp_material_url);

		$video_url = (isset($_POST['video_url']) ? $_POST['video_url'] : '');
		update_post_meta($post_id, "_video_url", $video_url);

		$bibtex_url = (isset($_POST['bibtex_url']) ? $_POST['bibtex_url'] : '');
		update_post_meta($post_id, "_bibtex_url", $bibtex_url);
	}
	else {
		return $post_id;
	}
}
add_action('post_updated', 'pa_save_publication');

?>