<h1><?=$extras["config"]["title"]?></h1>
        <form method="post">
            <?= wp_nonce_field($extras['nonceIDs']['action'], $extras['nonceIDs']['field']); ?>
            <div id="aeriaApp-<?=$extras["config"]['id']?>" class="aeriaApp">
                <script>
                    window.aeriaMetaboxes = window.aeriaMetaboxes || [];
                    window.aeriaMetaboxes.push(<?=wp_json_encode($extras["config"]); ?>);
                </script>
            </div>
            <?= submit_button(); ?>
        </form>