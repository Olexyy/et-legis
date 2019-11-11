<?php

namespace Drupal\legal_position\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\legal_position\DigestParser\DigestParser;
use Drupal\legal_position\ProcessParser\ProcessParserClient;

/**
 * Class ProcessParserForm.
 *
 * @package Drupal\legal_position\Form
 */
class ProcessParserForm extends FormBase {

  /**
   * Returns a unique string identifying the form.
   *
   * The returned ID should be a unique string that can be a valid PHP function
   * name, since it's used in hook implementation names such as
   * hook_form_FORM_ID_alter().
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {

    return 'process_parser_form';
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $processFid = $this->configFactory()
      ->get('legal_position.settings')
      ->get('processes_fid');
    $digestFid = $this->configFactory()
      ->get('legal_position.settings')
      ->get('digest_fid');
    $types = [
      'ВВ' => 'ВВ',
      'ВП' => 'ВП',
      'Ю' => 'Ю',
    ];
    $form['process'] = [
      '#type' => 'details',
      '#title' => $this->t('Огляд судової практики'),
      '#open' => TRUE,
      '#group' => 'container',
    ];
    $form['process']['file'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('File'),
      '#upload_location' => 'public://process',
      '#upload_validators' => [
        'file_validate_extensions' => ['xlsx'],
      ],
      '#default_value' => $processFid ? [$processFid] : NULL,
      '#required' => TRUE,
    ];
    $form['process']['type'] = [
      '#type' => 'select',
      '#default_value' => 'ВВ',
      '#title' => $this->t('Type'),
      '#options' => $types,
      '#required' => TRUE,
    ];
    $form['process']['limit'] = [
      '#type' => 'number',
      '#title' => $this->t('Limit'),
      '#default_value' => 0,
      '#min' => 0,
      '#max' => 1000,
      '#step' => 10,
      '#required' => TRUE,
    ];
    $form['process']['actions'] = ['#type' => 'actions'];
    $form['process']['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Import'),
      '#name' => 'submit_process',
      '#limit_validation_errors' => [
        ['limit'], ['type'], ['file'],
      ],
      '#submit' => [[$this, 'submitProcess']],
    ];
    $form['digest'] = [
      '#type' => 'details',
      '#title' => $this->t('Дайджест судової практики'),
      '#open' => TRUE,
      '#group' => 'container',
    ];
    $form['digest']['digest_file'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('File'),
      '#upload_location' => 'public://digest',
      '#upload_validators' => [
        'file_validate_extensions' => ['pdf'],
      ],
      '#default_value' => $digestFid ? [$digestFid] : NULL,
      '#required' => TRUE,
    ];
    $form['digest']['actions'] = ['#type' => 'actions'];
    $form['digest']['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Import'),
      '#name' => 'submit_digest',
      '#limit_validation_errors' => [
        ['digest_file'],
      ],
      '#submit' => [[$this, 'submitDigest']],
    ];

    return $form;
  }

  public function submitProcess(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory()
      ->getEditable('legal_position.settings');
    $existing = $config->get('processes_fid');
    $limit = $form_state->getValue('limit');
    $type = $form_state->getValue('type');
    $file = $form_state->getValue('file', []);
    if ($file) {
      $file = current($file);
      $file = File::load($file);
      $url = $file->getFileUri();
      $url = \Drupal::service('file_system')
        ->realpath($url);
      if ($existing != $file->id()) {
        // Delete existing.
        if ($existing && ($existing = File::load($existing))) {
          $existing->delete();
        }
        // Set permanent status.
        $config->set('processes_fid', $file->id())
          ->save(TRUE);
        $file->setPermanent();
        $file->save();
      }
      try {
        $count = ProcessParserClient::instance()
          ->setType($type)
          ->setLimit($limit)
          ->setUrl($url)
          ->processXml();
        $this->messenger()->addStatus(
          $this->t("Created :count items.", [
            ':count' => $count,
          ])
        );
      }
      catch (\Exception $exception) {
        $this->messenger()->addError(
          $this->t('Incorrect input data or service unavailable.')
        );
      }
    }
  }

  public function submitDigest(array &$form, FormStateInterface $form_state) {

    $config = $this->configFactory()
      ->getEditable('legal_position.settings');
    $existing = $config->get('digest_fid');
    $file = $form_state->getValue('digest_file', []);
    if ($file) {
      $file = current($file);
      $file = File::load($file);
      $url = $file->getFileUri();
      $url = \Drupal::service('file_system')
        ->realpath($url);
      if ($existing != $file->id()) {
        // Delete existing.
        if ($existing && ($existing = File::load($existing))) {
          $existing->delete();
        }
        // Set permanent status.
        $config->set('digest_fid', $file->id())
          ->save(TRUE);
        $file->setPermanent();
        $file->save();
      }
      try {
        $count = DigestParser::instance()
          ->doParse($url);
        $this->messenger()->addStatus(
          $this->t("Created :count items.", [
            ':count' => $count,
          ])
        );
      }
      catch (\Exception $exception) {
        $this->messenger()->addError(
          $this->t('Incorrect input data or service unavailable.')
        );
      }
    }
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $a = 1;
  }

}
