<?php

namespace Drupal\legal_position\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
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

    $types = [
      'ВВ' => 'ВВ',
      'ВП' => 'ВП',
      'Ю' => 'Ю',
    ];
    $form['url'] = [
      '#type' => 'textfield',
      '#default_value' => 'https://supreme.court.gov.ua/userfiles/media/2019_06_27_zvid2019_27_06_2019.xlsx',
      '#title' => $this->t('Link'),
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
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $limit = $form_state->getValue('limit');
    $url = $form_state->getValue('url');
    $type = $form_state->getValue('type');
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
