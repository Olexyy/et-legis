<?php

namespace Drupal\court\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\court\Service\CourtApiService;
use Drupal\court\Service\CourtApiServiceInterface;
use Drupal\court\Utils\ReviewRequestData;
use Drupal\court\Utils\SearchRequestData;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for Decision edit forms.
 *
 * @ingroup court
 */
class DecisionForm extends ContentEntityForm {

  /**
   * Court api service.
   *
   * @var \Drupal\court\Service\CourtApiServiceInterface
   */
  protected $courtApiService;

  /**
   * DecisionForm constructor.
   *
   * {@inheritdoc}
   */
  public function __construct(CourtApiServiceInterface $courtApiService,
                              EntityRepositoryInterface $entity_repository,
                              EntityTypeBundleInfoInterface $entity_type_bundle_info = NULL,
                              TimeInterface $time = NULL) {

    parent::__construct($entity_repository, $entity_type_bundle_info, $time);
    $this->courtApiService = $courtApiService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {

    return new static(
      $container->get(CourtApiService::ID),
      $container->get('entity.repository'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    /* @var $entity \Drupal\court\Entity\Decision */
    $entity = $form_state->getFormObject()->getEntity();
    if ($number = $this->getRequest()->query->get('import')) {
      $this->messenger()->addStatus($number);
      $resp = $this->courtApiService
        ->review(
          ReviewRequestData::create()->setRegNumber($number)
        );
      if (!$resp->isEmpty()) {
        $this->courtApiService->sync($entity, $resp);
      }
    }
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function actionsElement(array $form, FormStateInterface $form_state) {

    $actions = parent::actionsElement($form, $form_state);
    $actions['import'] = [
      '#type' => 'button',
      '#executes_submit_callback' => TRUE,
      '#limit_validation_errors' => [['number']],
      '#value' => $this->t('Import'),
    ];

    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    if ($form_state->getTriggeringElement()['#type'] == 'button') {
      $form_state->setRedirect('<current>', [
        'import' => $form_state->getValue('number')[0]['value'],
      ]);
    }
    else {
      parent::submitForm($form, $form_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Decision.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Decision.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.decision.canonical', ['decision' => $entity->id()]);
  }

}
