<?php

namespace Drupal\legal_position\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a block with a simple text.
 *
 * @Block(
 *   id = "config_based_block",
 *   admin_label = @Translation("Config based block"),
 * )
 */
class ConfigBasedBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    return [
      '#markup' => $this->configuration['content'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {

    return AccessResult::allowedIfHasPermission($account, 'access content');
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();
    $form['content'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Content'),
      '#default_value' => $config['content'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {

    $this->configuration['content'] = $form_state->getValue('content');
  }

}
