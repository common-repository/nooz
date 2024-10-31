<div class="wrap nooz-settings">
    <h1 class="nooz-settings__page-title"><?php echo sprintf( _x( '%s Settings', '[plugin_name] Settings', 'mdnooz' ), esc_html( get_option( 'mdnooz_plugin_name' ) ) ); ?></h1>
    <?php settings_errors(); ?>
    <div class="nooz-settings__body">
        <form method="post" action="options.php" enctype="multipart/form-data">
            <h2 class="nav-tab-wrapper">
                <?php foreach( $data as $group ) {  ?>
                    <a href="<?php echo esc_url( $group['url'] ); ?>" class="nav-tab<?php echo $group['is_active'] ? ' nav-tab-active' : '' ; ?>"><?php echo $group['title']; ?></a>
                <?php } ?>
            </h2>
            <?php foreach( $data as $group ) { if ( FALSE === $group['is_active'] ) continue; ?>
                <div class="nooz-settings__group">
                    <?php if ( ! empty( $group['description'] ) ) { ?>
                        <p class="nooz-settings__group-description"><?php echo $group['description']; ?></p>
                    <?php } ?>
                    <?php settings_fields( $group['id'] ); ?>
                    <?php do_settings_sections( $group['id'] ); ?>
                </div>
            <?php } ?>
            <?php submit_button(); ?>
        </form>
    </div>
</div>
