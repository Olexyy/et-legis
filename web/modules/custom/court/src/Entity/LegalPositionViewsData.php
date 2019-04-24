<?php

namespace Drupal\court\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Legal position entities.
 */
class LegalPositionViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {

    $data = parent::getViewsData();
    $data['taxonomy_term_field_data']['reverse__legal_position__tags']['relationship'] = [
      'title' => t('Legal positions'),
      'label' => t('Legal positions'),
      'group' => t('Legal position'),
      'help' => t('Reference to legal positions'),
      'id' => 'entity_reverse',
      'entity_type' => 'legal_position',
      'base' => 'legal_position_field_data',
      'base field' => 'id',
      'field_name' => 'tags',
      'field table' => 'legal_position__tags',
      'field field' => 'tags_target_id',
      'join_extra' => [
        [
          'field' => 'deleted',
          'value' => 0,
          'numeric' => TRUE,
        ],
      ],
    ];

    return $data;
  }

}
