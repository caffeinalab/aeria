<?php

namespace Aeria\Meta;

use Aeria\Field\FieldGroupProcessor;

class MetaProcessor extends FieldGroupProcessor
{
    public function getType(){
      return "meta";
    }

    public function getSavedFields(){
      return get_post_meta($this->id);
    }
}
