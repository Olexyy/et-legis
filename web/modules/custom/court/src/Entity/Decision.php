<?php

namespace Drupal\court\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Decision entity.
 *
 * @ingroup court
 *
 * @ContentEntityType(
 *   id = "decision",
 *   label = @Translation("Decision"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\court\DecisionListBuilder",
 *     "views_data" = "Drupal\court\Entity\DecisionViewsData",
 *     "translation" = "Drupal\court\DecisionTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\court\Form\DecisionForm",
 *       "add" = "Drupal\court\Form\DecisionForm",
 *       "edit" = "Drupal\court\Form\DecisionForm",
 *       "delete" = "Drupal\court\Form\DecisionDeleteForm",
 *     },
 *     "access" = "Drupal\court\DecisionAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\court\DecisionHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "decision",
 *   data_table = "decision_field_data",
 *   translatable = TRUE,
 *   admin_permission = "administer decision entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "number",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/config/decision/decision/{decision}",
 *     "add-form" = "/admin/config/decision/decision/add",
 *     "edit-form" = "/admin/config/decision/decision/{decision}/edit",
 *     "delete-form" = "/admin/config/decision/decision/{decision}/delete",
 *     "collection" = "/admin/config/decision/decision",
 *   },
 *   field_ui_base_route = "decision.settings"
 * )
 */
class Decision extends ContentEntityBase implements DecisionInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getNumber() {
    return $this->get('number')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setNumber($name) {
    $this->set('number', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isPublished() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setPublished($published) {
    $this->set('status', $published ? TRUE : FALSE);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Decision entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    // Set index unique.
    $fields['number'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Number'))
      ->setDescription(t('Unique number Decision entity.'))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);
    // Set index not unique.
    $fields['case_number'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Number'))
      ->setDescription(t('Unique number Decision entity.'))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);
    $fields['form'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Form'))
      ->setDefaultValue('Europe/Copenhagen')
      ->setDescription(t('Form of decision.'))
      ->setSetting('max_length', 50)
      ->setSetting('allowed_values_function', static::class . '::getOptionsForms')
      ->addPropertyConstraints('value', [
        'AllowedValues' => ['callback' => static::class . '::getAllowedValuesForms'],
      ])
      ->setDisplayOptions('form', [
        'type' => 'options_select',
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);
    $fields['jurisdiction'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Form'))
      ->setDefaultValue('Europe/Copenhagen')
      ->setDescription(t('Form of decision.'))
      ->setSetting('max_length', 50)
      ->setSetting('allowed_values_function', static::class . '::getOptionsJurisdictions')
      ->addPropertyConstraints('value', [
        'AllowedValues' => ['callback' => static::class . '::getAllowedValuesJurisdictions'],
      ])
      ->setDisplayOptions('form', [
        'type' => 'options_select',
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);
    $fields['category'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Category'))
      ->setDescription(t('Decision category.'))
      ->setSetting('target_type', 'taxonomy_term')
      ->setSetting('handler', 'default:taxonomy_term')
      ->setSetting('handler_settings', [
        'target_bundles' => ['categories' => 'categories'],
        'auto_create' => TRUE,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    $fields['court'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Court'))
      ->setDescription(t('Court name.'))
      ->setSetting('target_type', 'taxonomy_term')
      ->setSetting('handler', 'default:taxonomy_term')
      ->setSetting('handler_settings', [
        'target_bundles' => ['courts' => 'courts'],
        'auto_create' => TRUE,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    $fields['judge'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Judge'))
      ->setDescription(t('Judge name.'))
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    $fields['resolved'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(t('Resolved'))
      ->setDescription(t('Time when this decision was resolved.'))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    $fields['validated'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(t('Validated'))
      ->setDescription(t('Time when this decision was validated.'))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    $fields['registered'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(t('Registered'))
      ->setDescription(t('Time when this decision was registered.'))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    $fields['publicised'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(t('Publicised'))
      ->setDescription(t('Time when this decision was publicised.'))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the Decision is published.'))
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => -3,
      ]);
    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));
    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

  /**
   * Getter for options.
   *
   * @return array
   *   Options.
   */
  public function getOptionsForms() {

    $options = array_values(DecisionOptions::instance()->forms());

    return array_combine($options, $options);
  }

  /**
   * Getter for options.
   *
   * @return array
   *   Options.
   */
  public function getAllowedValuesForm() {

    return array_values(DecisionOptions::instance()->forms());
  }

  /**
   * Getter for options.
   *
   * @return array
   *   Options.
   */
  public function getOptionsJurisdictions() {

    $options = array_values(DecisionOptions::instance()->jurisdictions());

    return array_combine($options, $options);
  }

  /**
   * Getter for options.
   *
   * @return array
   *   Options.
   */
  public function getAllowedValuesJurisdictions() {

    return array_values(DecisionOptions::instance()->jurisdictions());
  }

}
