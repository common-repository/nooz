<div class="nooz-release__content">
    <?php if ( ! empty( $data['subheadline'] ) ) { ?>
        <h2 class="nooz-release__subheadline"><?php echo wp_kses_post( $data['subheadline'] ); ?></h2>
    <?php } ?>
    <?php if ( ! empty( $data['content'] ) ) { ?>
        <div class="nooz-release__body">
            <?php echo $data['dateline'] . $data['content']; ?>
        </div>
    <?php } ?>
    <?php if ( ! empty( $data['boilerplate'] ) || ! empty( $data['additional_boilerplates'] ) ) { ?>
        <div class="nooz-release__boilerplates">
            <?php if ( ! empty( $data['boilerplate'] ) ) { ?>
                <div class="nooz-release__boilerplate"><?php echo wp_kses_post( $data['boilerplate'] ); ?></div>
            <?php } ?>
            <?php if ( ! empty( $data['additional_boilerplates'] ) ) { ?>
                <?php foreach( $data['additional_boilerplates'] as $v ) { ?>
                    <div class="nooz-release__boilerplate"><?php echo wp_kses_post( $v ); ?></div>
                <?php } ?>
            <?php } ?>
        </div>
    <?php } ?>
    <?php if ( ! empty( $data['ending'] ) ) { ?>
        <p class="nooz-release__ending"><?php echo $data['ending']; ?></p>
    <?php } ?>
    <?php if ( ! empty( $data['contact'] ) || ! empty( $data['additional_contacts'] ) ) { ?>
        <div class="nooz-release__contacts">
            <?php if ( ! empty( $data['contact'] ) ) { ?>
                <div class="nooz-release__contact"><?php echo wp_kses_post( $data['contact'] ); ?></div>
            <?php } ?>
            <?php if ( ! empty( $data['additional_contacts'] ) ) { ?>
                <?php foreach( $data['additional_contacts'] as $v ) { ?>
                    <div class="nooz-release__contact"><?php echo wp_kses_post( $v ); ?></div>
                <?php } ?>
            <?php } ?>
        </div>
    <?php } ?>
</div>
