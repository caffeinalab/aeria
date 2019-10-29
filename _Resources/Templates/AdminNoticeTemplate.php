<div class="notice notice-<?php 
        echo $data['type'];
        echo ' ';
        $data['dismissible'] = isset($data['dismissible']) ? $data['dismissible'] : false;
        if ($data['dismissible'])
            echo 'is-dismissible'; 
    ?>
">
    <p><strong>Aeria:</strong> <?= $data['message']?></p>
</div>