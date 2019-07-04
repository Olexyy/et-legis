<?php

namespace Drupal\legal_position\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
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

    $fid = $this->configFactory()
      ->get('legal_position.settings')
      ->get('processes_fid');
    $types = [
      'ВВ' => 'ВВ',
      'ВП' => 'ВП',
      'Ю' => 'Ю',
    ];
    $form['file'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('File'),
      '#upload_location' => 'public://process',
      '#upload_validators' => [
        'file_validate_extensions' => ['xlsx'],
      ],
      '#default_value' => $fid ? [$fid] : NULL,
      '#required' => TRUE,
    ];
    $form['type'] = [
      '#type' => 'select',
      '#default_value' => 'ВВ',
      '#title' => $this->t('Type'),
      '#options' => $types,
      '#required' => TRUE,
    ];
    $form['limit'] = [
      '#type' => 'number',
      '#title' => $this->t('Limit'),
      '#default_value' => 0,
      '#min' => 0,
      '#max' => 1000,
      '#step' => 10,
      '#required' => TRUE,
    ];
    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Import'),
    ];

    return $form;
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

}
