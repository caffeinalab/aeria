<?php

namespace Aeria\Meta\MetaTree;

use Aeria\Meta\MetaTree\MetaField;
use Aeria\Meta\MetaTree\PictureField;
use Aeria\Meta\MetaTree\GalleryField;
use Aeria\Meta\MetaTree\RepeaterField;
use Aeria\Meta\MetaTree\SectionsField;
use Aeria\Meta\MetaTree\SelectField;

class TreeFactory
{

  public static function make($parentKey, $config, $index = null)
  {
    switch($config["type"]){
      case 'repeater':
          return new RepeaterField($parentKey, $config, $index);
          break;
      case 'gallery':
          return new GalleryField($parentKey, $config, $index);
          break;
      case 'picture':
          return new PictureField($parentKey, $config, $index);
          break;
      case 'sections':
          return new SectionsField($parentKey, $config, $index);
          break;
      case 'select':
          return new SelectField($parentKey, $config, $index);
          break;

      default:
          return new MetaField($parentKey, $config, $index);
      }
  }

}
