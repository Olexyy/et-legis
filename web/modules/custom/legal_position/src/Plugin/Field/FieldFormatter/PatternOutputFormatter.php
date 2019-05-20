<?php

namespace Drupal\legal_position\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Render\Element;

/**
 * Plugin implementation of the Pattern output formatter.
 *
 * @FieldFormatter(
 *   id = "pattern_output",
 *   label = @Translation("Pattern output"),
 *   field_types = {
 *     "string",
 *     "string_long",
 *     "text",
 *     "text_long",
 *     "text_with_summary"
 *   }
 * )
 */
class PatternOutputFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'pattern' => '<h5><b>{value}</b></h5>',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements['pattern'] = [
      '#type' => 'textfield',
      '#title' => t('Pattern for this field'),
      '#default_value' => $this->getSetting('pattern'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $settings = $this->getSettings();

    if (!empty($settings['pattern'])) {
      $summary[] = t('Pattern: @pattern', ['@pattern' => $settings['pattern']]);
    }
    else {
      $summary[] = t('Pattern not set.');
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {

    $element = [];
    $pattern = $this->getSetting('pattern');
    foreach ($items as $delta => $item) {
      $element[$delta] = [
        '#type' => 'markup',
        '#markup' => str_replace('{value}', strip_tags($item->value), $pattern),
      ];
      if (!empty($item->_attributes)) {
        $element[$delta]['#options'] += ['attributes' => []];
        $element[$delta]['#options']['attributes'] += $item->_attributes;
        unset($item->_attributes);
      }
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function view(FieldItemListInterface $items, $langcode = NULL) {
    // Default the language to the current content language.
    if (empty($langcode)) {
      $langcode = \Drupal::languageManager()->getCurrentLanguage(LanguageInterface::TYPE_CONTENT)->getId();
    }
    $elements = $this->viewElements($items, $langcode);

    // If there are actual renderable children, use #theme => field, otherwise,
    // let access cacheability metadata pass through for correct bubbling.
    if (Element::children($elements)) {
      $entity = $items->getEntity();
      $entity_type = $entity->getEntityTypeId();
      $field_name = $this->fieldDefinition->getName();
      $info = [
        '#theme' => 'field',
        '#title' => $this->fieldDefinition->getLabel(),
        '#label_display' => $this->label,
        '#view_mode' => $this->viewMode,
        '#language' => $items->getLangcode(),
        '#field_name' => $field_name,
        '#field_type' => $this->fieldDefinition->getType(),
        '#field_translatable' => $this->fieldDefinition->isTranslatable(),
        '#entity_type' => $entity_type,
        '#bundle' => $entity->bundle(),
        '#object' => $entity,
        '#items' => $items,
        '#formatter' => $this->getPluginId(),
        '#is_multiple' => $this->fieldDefinition->getFieldStorageDefinition()->isMultiple(),
      ];

      $elements = array_merge($info, $elements);
    }

    return $elements;
  }

}
