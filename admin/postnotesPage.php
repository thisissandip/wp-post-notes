<h1> Post Notes </h1>

    <div class='post-note-container'>
        <span>Activate Post Notes for Custom Post Types</span>
        <div class='manage-post-container'>
            <form action="options.php" method="post">
                <?php
                    settings_fields( "post_notes_OG" );
                    do_settings_sections( "post_notes" );
                    submit_button();     
                ?>
            </form>
        </div>
    </div>

