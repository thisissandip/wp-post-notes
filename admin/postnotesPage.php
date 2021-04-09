<h1> Post Notes </h1>

    <div class='post-note-container'>
        <div class="post-note-info" >Activate Post Notes for Custom Post Types</div>
        <div class='manage-post-container'>
            <form id="admin-post-notes-form" action="options.php" method="post">
                <?php
                    settings_fields( "post_notes_OG" );
                    do_settings_sections( "post_notes" );
                ?>
            </form>
        </div>
    </div>

