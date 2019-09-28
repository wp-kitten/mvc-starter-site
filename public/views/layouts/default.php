<?php if ( !defined( 'APP_DIR' ) ) {
    exit( 'No direct access please.' );
}

?><!doctype html>
<html lang="<?php echo ENV_LOCALE; ?>">
<head>
    <meta charset="<?php echo ENV_CHARSET; ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>

    <title><?php echo( isset( $page_title ) ? $page_title : SITE_TITLE ); ?></title>

    <?php app_head(); ?>
</head>
<body>
<main role="main" class="container">

    <div class="starter-template">

        <?php
        echo( isset( $content_for_layout ) ? $content_for_layout : '' );
        ?>

    </div>

</main>

<?php app_footer(); ?>
</body>
</html>
