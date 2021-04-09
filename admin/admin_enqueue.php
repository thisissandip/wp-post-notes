<?php

function post_notes_admin_scripts(){
    wp_enqueue_style( "post_note_admin_style", POSTNOTES_URL .  'admin/css/styles.css', array(), "", "all" );
    wp_enqueue_script( "post_note_admin_script", POSTNOTES_URL . 'admin/js/script.js', array("jquery"), "", true );
}

add_action( "admin_enqueue_scripts","post_notes_admin_scripts" );

?>