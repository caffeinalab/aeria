<div id="aeriaApp-<?=$data['metabox']['id']; ?>" class="aeriaApp">
  <div style="display: none;">
    <?php wp_editor('', 'loadTinymce'); ?>
  </div>
</div>
<script>
  window.aeriaMetaboxes = window.aeriaMetaboxes || [];
  window.aeriaMetaboxes.push(<?=wp_json_encode($data['metabox']); ?>);
</script>
