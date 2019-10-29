<div id="aeriaApp-<?=$data['metabox']['id']?>" class="aeriaApp">
  <?php wp_editor( '', 'loadTinymce'); ?>
</div>
<script>
  window.aeriaMetaboxes = window.aeriaMetaboxes || [];
  window.aeriaMetaboxes.push(<?=wp_json_encode($data['metabox']);?>);
</script>
