<?php

namespace Drupal\web_drush\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\Process\Process;

/**
 * Class WebDrushForm.
 *
 * @package Drupal\web_drush\Form
 */
class WebDrushForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {

    return 'web_drush_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    if ($commands = $this->getCommands()) {

      $form['command'] = [
        '#type' => 'select',
        '#title' => $this->t('Command to execute'),
        '#description' => $this->t('Select command to execute.'),
        '#options' => $this->getCommandsSelect(),
      ];
      $form['parameters'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Additional parameters'),
        '#description' => $this->t('Additional parameters for example " -y".'),
      ];
      $form['available_commands'] = [
        '#type' => 'details',
        '#title' => $this->t('Available drush commands'),
        '#open' => FALSE,
        'commands' => [
          '#type' => 'item',
          '#markup' => '<pre>' . $commands . '</pre>',
        ],
      ];
      $form['executable_type'] = [
        '#type' => 'select',
        '#title' => $this->t('Executable type'),
        '#description' => $this->t('Defines how drush is invoked.'),
        '#default_value' => 'global',
        '#options' => [
          'global' => $this->t('Global'),
          'local' => $this->t('Local (standard project)'),
          'local_web' => $this->t('Local (template project)'),
        ],
      ];
      $form['actions'] = [
        '#type' => 'actions',
        '#attributes' => ['class' => ['container-inline']],
      ];
      $form['#prefix'] = '<div id="ajax--wrapper">';
      $form['#suffix'] = '</div>';
      $form['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Execute'),
        '#ajax' => [
          'wrapper' => 'ajax--wrapper',
          'callback' => [$this, 'ajaxCallback'],
        ],
      ];
      if ($result = $form_state->getValue('result')) {
        $form['result'] = [
          '#type' => 'item',
          '#title' => $this->t('Result'),
          '#markup' => '<pre>' . $result . '</pre>',
        ];
      }
    }
    else {
      $form['result'] = [
        '#type' => 'item',
        '#title' => $this->t('Drush is not available'),
        '#markup' => $this->t('Unable to obtain commands list'),
      ];
    }

    return $form;
  }

  /**
   * Ajax callback.
   */
  public function ajaxCallback(array $form, FormStateInterface $form_state) {

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $form_state->setRebuild(TRUE);
    if ($command = $form_state->getValue('command')) {
      if ($parameters = $form_state->getValue('parameters')) {
        if (is_string($parameters)) {
          $command .= ' ' . $parameters;
        }
      }
      $cwd = NULL;
      $executableType = $form_state->getValue('executable_type');
      if ($executableType == 'local') {
        $cwd = getcwd() . '/vendor/bin';
      }
      elseif ($executableType == 'local_web') {
        $cwd = getcwd() . '/../vendor/bin';
      }
      $process = new Process('drush ' . $command, $cwd, ['HOME' => getcwd()]);
      $process->run();
      $result = $process->getOutput()? $process->getOutput() : $process->getErrorOutput();
      $form_state->setValue('result', $result);
    }
  }

  protected function getCommands($parse = FALSE) {

    if ($commands = $this->drushCacheGet('drush_list')) {

      return $parse? $this->parseCommands($commands) : $commands;
    }
    if ($commands = $this->executeDrush('drush list --raw')) {
      $this->drushCacheSet('drush_list', $commands);

      return $parse? $this->parseCommands($commands) : $commands;
    }

    return NULL;
  }

  protected function getCommandsSelect() {

    $commands = array_keys($this->getCommands(TRUE));

    return array_combine($commands, $commands);
  }

  public function parseCommands($commands) {

    $commandsArray = [];
    if ($parsed = preg_split('@\R@', $commands)) {
      foreach ($parsed as $row) {
        $values = array_filter(explode('  ', $row));
        $name = trim(current($values));
        $description = trim(next($values));
        if ($name && $description) {
          $commandsArray[$name] = $description;
        }
      }
    }

    return $commandsArray;
  }

  protected function drushCacheSet($cid, $data) {

    \Drupal::cache()->set($cid, $data);
  }

  protected function drushCacheGet($cid) {

    if ($data = \Drupal::cache()->get($cid)) {

      return $data->data;
    }

    return NULL;
  }

  protected function executeDrush($command) {

    $env = [
      'HOME' => getcwd(),
    ];
    $process = new Process($command, NULL, $env);
    $process->run();

    return $process->getErrorOutput()? $process->getErrorOutput() : $process->getOutput();
  }

}
