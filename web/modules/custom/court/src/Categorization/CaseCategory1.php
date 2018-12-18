<?php

namespace Drupal\court\Categorization;

/**
 * Class CaseCategory1.
 *
 * @package Drupal\reyestr_court\Categorization
 */
class CaseCategory1 implements CategorizationInterface {

  /**
   * {@inheritdoc}
   */
  public static function getList() {
    return [
      3000 => 'Адміністративні справи',
      4000 => 'Господарські справи',
      2000 => 'Кримінальні справи',
      6258 => 'Невідкладні судові розгляди',
      5139 => 'Справи про адмінправопорушення',
      1000 => 'Цивільні справи',
    ];
  }
}