<?php 
/*
Plugin Name: Post Notes
Description: Write Notes for your Posts. So that you Don't miss anything while writing!
Version: 1.0.0
Author: Sandip Mondal
License: GPLv2 or later
Text Domain: post-note
*/

if(!defined("ABSPATH")) : exit(); endif;

/**
 * Define plugin constants
 */

define("POSTNOTES_PATH", trailingslashit( plugin_dir_path(__FILE__) ));
define("POSTNOTES_URL", trailingslashit( plugins_url("/", __FILE__) ));

/**
 * Enqueue Styles and Scripts for Admin
 */

function post_notes_admin_scripts(){
    wp_enqueue_style( "post_note_admin_style", POSTNOTES_URL .  'admin/css/styles.css', array(), "", "all" );

    if(is_admin() && $_GET["page"]=="post_notes"){
        wp_enqueue_script( "post_note_admin_script", POSTNOTES_URL . 'admin/js/script.js', array("jquery"), "", true );
    }
}

add_action( "admin_enqueue_scripts","post_notes_admin_scripts" );


/**
 * Register Plugin Menu Page
 */

function register_post_note_menupage(){
    add_menu_page( "Post Notes", "Post Notes", "manage_options", "post_notes","get_post_notes_admin" , "", null);
}

function get_post_notes_admin(){
    require_once POSTNOTES_PATH . 'admin/postnotesPage.php';
}

add_action( "admin_menu", "register_post_note_menupage" );

/*
Register Settings for the Option Group
*/

function register_post_note_OG(){
    /* Register Setting */
    $args = array( 
        'sanitize_callback' => 'SanitizeInputs',
        );
    register_setting( "post_notes_OG", "post_note_cpt", $args  );
    /* Register Setting Section */
    add_settings_section( "post_notes_admin", "", "post_notes_section_admin_display", "post_notes" );
    /* Register Settings Fields */
    add_settings_field( "post_notes_field", "Choose Custom Post Types", "CheckboxFields",
     "post_notes", "post_notes_admin", array(
         "id" => "post_notes_field",
         "class" => "pn-input",
         "option_name" => "post_note_cpt",
     ) );
}

function post_notes_section_admin_display(){
    echo "";
}

function CheckboxFields( $args ){
    /*
        Get all post Types
    */
    $postargs = array(
        'public'   => true,
        '_builtin' => false,
    );

    $output = 'names'; // names or objects, note names is the default
    $operator = 'or'; // 'and' or 'or'

    $allposttypes = get_post_types( $postargs, $output, $operator ); 

    /*
        Create name for each input field
    */

    $id = $args["id"];
    $option_name = $args["option_name"];
    
    foreach($allposttypes as $posttype){
        $name = $option_name.'['. $posttype .']';

    /*
        Check if the input field is checked
    */

        $output = get_option( "post_note_cpt" );
        $checked = false;
    
        $checked = isset($output[$posttype]) ? ($output[$posttype]==1 ? true : false) : false;
        echo '
   
            <label for="'. $name .'" >
                <div class="post-type-slug">'. $posttype .'</div>

                <div class="switch-wrapper">
                    <input id="'.$name .'" type="checkbox" class="regular-text" name="'. $name .'" value="1" '. ($checked ? 'checked' : '') .' />
                    
                    <div class="switch-bg">
                        <span class="switch-circle"></span>
                    </div>
                </div>
            </label>
     
        <br>
    ';
    }
}

function SanitizeInputs($input){
    return $input;
}

add_action("admin_init", "register_post_note_OG");

/*
    Add meta Boxes
*/

function add_meta_boxes(){
    $output = get_option( "post_note_cpt" );

    foreach($output as $key=>$value){
        add_meta_box( "postnote_notesbox", $title= "Notes by Post Notes", 
        $callback = "render_meta_box" , $screen = $key, 
        $context = "side", $priority = "default" );
    }
}

/*  Create the display / Form of the Meta Box Set all the nonce field and Keys of Meta Post Type */
function render_meta_box($post){
    wp_nonce_field( "postnote_notesbox", "postnote_notesbox_nonce" );

    $data = get_post_meta( $post->ID, "_postnote_key", true );

    ?>
    <p>
        <label class="meta-label" for="postnotes_area">Notes</label>
        <textarea name="postnotes_area" id="postnotes_area" cols="30" rows="5" style="width:99%"><?php echo $data ?></textarea>
    </p>

<?php
}

add_action( "add_meta_boxes","add_meta_boxes" );

function save_post_notes($post_id){
  // check if the nonce field is set
    if (! isset($_POST['postnote_notesbox_nonce'])) {
        return $post_id;    
    }
    // If it is set, Verify the Nonce
		$nonce = $_POST['postnote_notesbox_nonce'];
	if (! wp_verify_nonce( $nonce, 'postnote_notesbox' )) {
		return $post_id;
	}
    /* The save_post hook is triggered while doing autosave too, So to avoid
        unnecessary saves. Check if it is an autosave action
    */
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return $post_id;
	}
    // Check if the current user can edit post for the particular Post ID
	if (! current_user_can( 'edit_post', $post_id ) ) {
		return $post_id;
	}

    /* Get the value from the Fields and Update the Meta Fields */

    if( isset($_POST['postnotes_area']) ) {
        update_post_meta( $post_id, '_postnote_key', sanitize_text_field($_POST['postnotes_area'])  );
    }
}

add_action("save_post","save_post_notes");

/* 
  Set the Columns for every custom post type
*/

    $allposts = get_option( "post_note_cpt" );

function postnotes_add_columns($columns){

        $columns['note'] = __("Notes", 'post-note');
        return $columns;
}

function set_custom_columns_data($column, $post_id){

    $data = get_post_meta( $post_id, "_postnote_key", true );

    switch($column){
        case "note":
            echo $data;
        break;
        default:
        break;
    }
}

    foreach ($allposts as $key=>$value){
        add_action( 'manage_'.$key.'_posts_columns', 'postnotes_add_columns' );
        add_action( 'manage_'.$key.'_posts_custom_column', 'set_custom_columns_data' , 10, 2 );
    }

/**
 * Register Options Group for Dashboard
 */

function dashboard_post_note_OG(){
        /* Register Setting */
        $args = array( 
            'sanitize_callback' => 'SanitizeDashBoardInputs',
            );
        register_setting( "dash_post_notes_OG", "dash_post_note", $args  );
        /* Register Setting Section */
        add_settings_section( "post_notes_dash_section", "", "post_notes_section_dashboard_display",
         "dashboard" );
        /* Register Settings Fields */
        add_settings_field( "post_notes_dash_textarea", "Notes", "DashTextArea",
         "dashboard", "post_notes_dash_section", array(
             "class" => "dash-row",
             "option_name" => "dash_post_note"
         ) );
}

function post_notes_section_dashboard_display(){
    echo "";
}

function DashTextArea($args){

    $data = get_option( $args["option_name"] );
    $name = $args["option_name"];
    echo '
        <p>
            <textarea name="'.$name.'" id="'.$name.'" cols="30" rows="5" style="width:99%">'.$data.'</textarea>
        </p>
    ';
}

function SanitizeDashBoardInputs($input){

    sanitize_text_field( $input );
    return $input;
}

add_action("admin_init", "dashboard_post_note_OG");

/**
 * Add a new dashboard widget.
 */
function add_dashboard_widgets() {
    wp_add_dashboard_widget( "post_notes_widget", "Notes by Post Notes", 
    "render_dashboard_widget");

}
add_action( 'wp_dashboard_setup', 'add_dashboard_widgets' );
 
/**
 * Output the contents of the dashboard widget
 */
function render_dashboard_widget( $post, $callback_args ) {
    echo '<form id="postnote-dash-form" action="options.php" method="post" >';
        settings_fields( "dash_post_notes_OG" );
        do_settings_sections( "dashboard" );
        submit_button("Save Note", "primary", "submit", false);
    echo "</form>";
}

?>