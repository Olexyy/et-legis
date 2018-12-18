<?php

namespace Drupal\court\Categorization;

/**
 * Class CsType.
 *
 * @package Drupal\court\Categorization
 */
class CsType implements CategorizationInterface {

  /**
   * {@inheritdoc}
   */
  public static function getList() {
    return [
      4 => 'Адміністративне',
      3 => 'Господарське',
      2 => 'Кримінальне',
      1 => 'Цивільне',
      5 => 'Справи про адміністративні правопорушення',
    ];
  }
}