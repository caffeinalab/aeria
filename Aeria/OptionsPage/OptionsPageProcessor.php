<?php

namespace Aeria\OptionsPage;

use Aeria\Field\FieldGroupProcessor;


class OptionsPageProcessor extends FieldGroupProcessor
{
    public function getType(){
      return "options";
    }

    public function getSavedFields(){
      return wp_load_alloptions();
    }
}
