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
    // Set index unique.
    $fields['number'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Decision number'))
      ->setDescription(t('Unique number of Decision.'))
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
      ->setLabel(t('Case number'))
      ->setDescription(t('Case number.'))
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
      ->setDisplayConfigurable('view', TRUE);
    $fields['type'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Type'))
      ->setDefaultValue('')
      ->setDescription(t('Type of decision.'))
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
      ->setLabel(t('Jurisdiction'))
      ->setDescription(t('Decision jurisdiction.'))
      ->setSetting('max_length', 50)
      ->setSetting('allowed_values_function', static::class . '::getOptionsJurisdictions')
      ->addPropertyConstraints('value', [
        'AllowedValues' => ['callback' => static::class . '::getAllowedValuesJurisdictions'],
      ])
      ->setDisplayOptions('form', [
        'type' => 'options_select',
      ])
      ->setDefaultValue('')
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);
    $fields['category']  = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Category'))
      ->setSetting('max_length', 50)
      ->setDescription(t('Decision category.'))
      ->setSetting('allowed_values_function', static::class . '::getOptionsCategories')
      ->addPropertyConstraints('value', [
        'AllowedValues' => ['callback' => static::class . '::getAllowedValuesCategories'],
      ])
      ->setDisplayOptions('form', [
        'type' => 'options_select',
      ])
      ->setDefaultValue('')
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);
    $fields['court'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Court'))
      ->setSetting('max_length', 50)
      ->setDescription(t('Court name.'))
      ->setSetting('allowed_values_function', static::class . '::getOptionsCourts')
      ->addPropertyConstraints('value', [
        'AllowedValues' => ['callback' => static::class . '::getAllowedValuesCourts'],
      ])
      ->setDisplayOptions('form', [
        'type' => 'options_select',
      ])
      ->setDefaultValue('')
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);
    $fields['instance'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Instance'))
      ->setDescription(t('Court instance.'))
      ->setSetting('allowed_values_function', static::class . '::getOptionsInstances')
      ->addPropertyConstraints('value', [
        'AllowedValues' => ['callback' => static::class . '::getAllowedValuesInstances'],
      ])
      ->setDisplayOptions('form', [
        'type' => 'options_select',
      ])
      ->setDefaultValue('')
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);
    $fields['judge'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Judge'))
      ->setDescription(t('Judge name.'))
      ->setDefaultValue('')
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    $fields['resolved'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(t('Resolved'))
      ->setDefaultValue(NULL)
      ->setDescription(t('Time when this decision was resolved.'))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    $fields['validated'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(t('Validated'))
      ->setDefaultValue(NULL)
      ->setDescription(t('Time when this decision was validated.'))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    $fields['registered'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(t('Registered'))
      ->setDefaultValue(NULL)
      ->setDescription(t('Time when this decision was registered.'))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    $fields['published'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(t('Published'))
      ->setDefaultValue(NULL)
      ->setDescription(t('Time when this decision was published.'))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    $fields['text'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Text'))
      ->setDefaultValue('')
      ->setDescription(t('Decision text.'))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    $fields['resume'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Resume'))
      ->setDefaultValue('')
      ->setDescription(t('Short resume.'))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    $fields['legal_position'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Legal position'))
      ->setDefaultValue('')
      ->setDescription(t('Legal position.'))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    $fields['related'] = BaseFieldDefinition::create('entity_reference')
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setLabel(t('Related decisions'))
      ->setDescription(t('Related Decisions from another instances.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'decision')
      ->setSetting('handler', 'default')
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    $fields['tags'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Tags'))
      ->setDescription(t('Decision tags.'))
      ->setSetting('target_type', 'taxonomy_term')
      ->setSetting('handler', 'default:taxonomy_term')
      ->setSetting('handler_settings', [
        'target_bundles' => ['decision_tags' => 'decision_tags'],
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
    // System related properties.
    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the Decision is published.'))
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
      ]);
    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Created by'))
      ->setDescription(t('The user ID of author of the Decision entity.'))
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
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

  /**
   * Getter for options.
   *
   * @return array
   *   Options.
   */
  public function getOptionsCategories() {

    $options = array_values(DecisionOptions::instance()->categories());

    return array_combine($options, $options);
  }

  /**
   * Getter for options.
   *
   * @return array
   *   Options.
   */
  public function getAllowedValuesCategories() {

    return array_values(DecisionOptions::instance()->categories());
  }

  /**
   * Getter for options.
   *
   * @return array
   *   Options.
   */
  public function getOptionsCourts() {

    $options = array_values(DecisionOptions::instance()->courts());

    return array_combine($options, $options);
  }

  /**
   * Getter for options.
   *
   * @return array
   *   Options.
   */
  public function getAllowedValuesCourts() {

    return array_values(DecisionOptions::instance()->courts());
  }

  /**
   * Getter for options.
   *
   * @return array
   *   Options.
   */
  public function getOptionsInstances() {

    $options = array_values(DecisionOptions::instance()->instances());

    return array_combine($options, $options);
  }

  /**
   * Getter for options.
   *
   * @return array
   *   Options.
   */
  public function getAllowedValuesInstances() {

    return array_values(DecisionOptions::instance()->instances());
  }

}
