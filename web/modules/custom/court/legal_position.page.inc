<?php

/**
 * @file
 * Contains legal_position.page.inc.
 *
 * Page callback for Legal position entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Legal position templates.
 *
 * Default template: legal_position.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_legal_position(array &$variables) {
  // Fetch LegalPosition Entity Object.
  $legal_position = $variables['elements']['#legal_position'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
