<h1>Editor</h1>
<div id="aeriaApp-editor" class="aeriaApp"></div>
<script>
    window.aeriaConfig = <?=wp_json_encode($data["config"]); ?>
</script>
<?php
    $aeria_base_path = plugins_url('aeria');
    $aeria_editor_js = new Aeria\Action\Enqueuers\ScriptsEnqueuer(
        'aeria-editor-js',
        "{$aeria_base_path}/assets/js/aeria-editor.js",
        null,
        null,
        true
    );
    $admin_enqueue_scripts = new Aeria\Action\Actions\AdminEnqueueScripts();
    $admin_enqueue_scripts->register($aeria_editor_js);
    $admin_enqueue_scripts->dispatch(aeria());
?>
