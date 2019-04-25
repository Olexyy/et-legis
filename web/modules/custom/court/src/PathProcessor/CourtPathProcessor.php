<?php

namespace Drupal\court\PathProcessor;

use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Drupal\Core\PathProcessor\OutboundPathProcessorInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\taxonomy\TermInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Path processor.
 */
class CourtPathProcessor implements InboundPathProcessorInterface, OutboundPathProcessorInterface {

  /**
   * {@inheritdoc}
   */
  public function processInbound($path, Request $request) {

    return $path;
  }

  /**
   * {@inheritdoc}
   *
   * Alter path for specific taxonomy terms.
   */
  public function processOutbound($path, &$options = [], Request $request = NULL, BubbleableMetadata $bubbleable_metadata = NULL) {

    if (stripos($path, '/taxonomy/term/') === 0) {
      if (!empty($options['entity'])) {
        $term = $options['entity'];
        if ($term instanceof TermInterface) {
          if ($term->bundle() == 'legal_position_tags') {
            $path = '/legal-positions/' . $term->id();
          }
        }
      }
    }

    return $path;
  }

}
