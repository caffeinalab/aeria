<div id="aeriaApp-<?=$extras['metabox']['id']?>" class="aeriaApp">
          <script>
            window.aeriaMetaboxes = window.aeriaMetaboxes || [];
            window.aeriaMetaboxes.push(<?=wp_json_encode($extras['metabox']);?>);
          </script>
</div>