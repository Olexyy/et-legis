<?php

/**
 * @file
 * Contains decision.page.inc.
 *
 * Page callback for Decision entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Decision templates.
 *
 * Default template: decision.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_decision(array &$variables) {
  // Fetch Decision Entity Object.
  $decision = $variables['elements']['#decision'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
