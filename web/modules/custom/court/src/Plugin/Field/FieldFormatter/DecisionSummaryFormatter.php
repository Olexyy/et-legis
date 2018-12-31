<?php

namespace Drupal\court\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'decision_table' formatter.
 *
 * @FieldFormatter(
 *   id = "decision_table",
 *   label = @Translation("Decision table"),
 *   field_types = {
 *     "text_long"
 *   },
 *   quickedit = {
 *     "editor" = "plain_text"
 *   }
 * )
 */
class DecisionSummaryFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {

    $elements = [];
    /** @var \Drupal\court\Entity\Decision $decision */
    $decision = $items->getEntity();
    if ($items) {
      if ($decision->hasRelated()) {
        $resumes = '';
        foreach ($decision->getRelated() as $decision) {
          $resumes .= $decision->getResume();
        }
        foreach ($items as $delta => $item) {
          // The text value has no text format assigned to it, so the user input
          // should equal the output, including newlines.
          $elements[$delta] = [
            '#type' => 'inline_template',
            '#template' => '{{ value|nl2br }}',
            '#context' => ['value' => $item->value . $resumes],
          ];
        }
      }
    }

    return $elements;
  }

}
