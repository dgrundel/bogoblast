<div class="wrap">  
    <div id="icon-tools" class="icon32"></div>  
    <h2>Bogoblast Options</h2>  
    <?php settings_errors(); ?>  
    <form method="post" action="options.php">  
        <?php  
            settings_fields( 'bogoblast_options' );  
            do_settings_sections( 'bogoblast_options' );  
            submit_button();  
        ?>  
    </form>  
</div>