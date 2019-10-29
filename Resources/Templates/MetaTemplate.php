<div id="aeriaApp-<?=$data['metabox']['id']?>" class="aeriaApp">
          <script>
            window.aeriaMetaboxes = window.aeriaMetaboxes || [];
            window.aeriaMetaboxes.push(<?=wp_json_encode($data['metabox']);?>);
          </script>
</div>