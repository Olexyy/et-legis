<?php

namespace Drupal\legal_position\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Plugin implementation of the CourtLinkFormatter formatter.
 *
 * @FieldFormatter(
 *   id = "court_link",
 *   label = @Translation("Court link"),
 *   field_types = {
 *     "string"
 *   }
 * )
 */
class CourtLinkFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'base_url' => 'http://reyestr.court.gov.ua/Review/',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements['base_url'] = [
      '#type' => 'textfield',
      '#title' => t('Base url for this field'),
      '#default_value' => $this->getSetting('base_url'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $settings = $this->getSettings();

    if (!empty($settings['base_url'])) {
      $summary[] = t('Link using text: @base_url', ['@base_url' => $settings['base_url']]);
    }
    else {
      $summary[] = t('Court link provided identifier.');
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {

    $element = [];
    $base_url = $this->getSetting('base_url');
    foreach ($items as $delta => $item) {
      $element[$delta] = [
        '#type' => 'link',
        '#title' => $base_url . $item->value,
        '#url' => Url::fromUri($base_url . $item->value),
        '#options' => ['external' => TRUE],
      ];
      if (!empty($item->_attributes)) {
        $element[$delta]['#options'] += ['attributes' => []];
        $element[$delta]['#options']['attributes'] += $item->_attributes;
        unset($item->_attributes);
      }
    }

    return $element;
  }

}
