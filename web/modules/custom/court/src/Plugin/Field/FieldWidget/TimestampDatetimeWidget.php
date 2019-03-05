<?php

namespace Drupal\court\Plugin\Field\FieldWidget;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Datetime\Element\Datetime;
use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'datetime timestamp' widget nullable.
 *
 * @FieldWidget(
 *   id = "datetime_timestamp_nullable",
 *   label = @Translation("Timestamp Nullable"),
 *   field_types = {
 *     "timestamp",
 *     "created",
 *   }
 * )
 */
class TimestampDatetimeWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $date_format = DateFormat::load('html_date')->getPattern();
    //$time_format = DateFormat::load('html_time')->getPattern();
    $default_value = isset($items[$delta]->value) ? DrupalDateTime::createFromTimestamp($items[$delta]->value)->format($date_format) : '';
    $element['value'] = $element + [
      '#type' => 'date',
      '#default_value' => $default_value,
      '#date_year_range' => '1902:2037',
      '#date_date_format' => $date_format,
    ];
    $element['value']['#description'] = $this->t('Format: %format. Leave blank to use the time of form submission.', ['%format' => Datetime::formatExample('m/d/Y')]);

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    foreach ($values as &$item) {
      $date = NULL;
      if (isset($item['value']) && $item['value'] instanceof DrupalDateTime) {
        $date = $item['value'];
      }
      elseif (isset($item['value']['object']) && $item['value']['object'] instanceof DrupalDateTime) {
        $date = $item['value']['object'];
      }
      if ($date instanceof DrupalDateTime) {
        $item['value'] = $date->getTimestamp();
      }
      elseif (!empty($item['value']) && is_string($item['value'])) {
        $item['value'] = strtotime($item['value']);
      }
      else {
        $item['value'] = NULL;
      }
    }

    return $values;
  }

}
