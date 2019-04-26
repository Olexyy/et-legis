<?php

namespace Drupal\court\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\court\Plugin\Field\IncludeParentTermItemList;
use Drupal\taxonomy\TermInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Legal position entity.
 *
 * @ingroup court
 *
 * @ContentEntityType(
 *   id = "legal_position",
 *   label = @Translation("Legal position"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\court\LegalPositionListBuilder",
 *     "views_data" = "Drupal\court\Entity\LegalPositionViewsData",
 *     "translation" = "Drupal\court\LegalPositionTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\court\Form\LegalPositionForm",
 *       "add" = "Drupal\court\Form\LegalPositionForm",
 *       "edit" = "Drupal\court\Form\LegalPositionForm",
 *       "delete" = "Drupal\court\Form\LegalPositionDeleteForm",
 *     },
 *     "access" = "Drupal\court\LegalPositionAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\court\LegalPositionHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "legal_position",
 *   data_table = "legal_position_field_data",
 *   translatable = TRUE,
 *   admin_permission = "administer legal position entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/y/legal_position/{legal_position}",
 *     "add-form" = "/y/legal_position/add",
 *     "edit-form" = "/y/legal_position/{legal_position}/edit",
 *     "delete-form" = "/y/legal_position/{legal_position}/delete",
 *     "collection" = "/y/legal_position",
 *   },
 *   field_ui_base_route = "legal_position.settings"
 * )
 */
class LegalPosition extends ContentEntityBase implements LegalPositionInterface {

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
  public function preSave(EntityStorageInterface $storage) {

    parent::preSave($storage);
    if ($this->getIncludeParentTags()) {
      foreach ($this->getTags() as $tag) {
        foreach (static::getTermStorage()->loadAllParents($tag->id()) as $parent) {
          if (!$this->hasTag($parent)) {
            $this->addTag($parent);
          }
        }
      }
    }
  }

  /**
   * Term storage.
   *
   * @return \Drupal\taxonomy\TermStorageInterface
   *   Term storage.
   */
  public static function getTermStorage() {

    return \Drupal::service('entity_type.manager')
      ->getStorage('taxonomy_term');
  }

  public function getIncludeParentTags() {

    return $this->get('include_parent_tags')->value;
  }

  public function setIncludeParentTags($includeParentTags) {

    $this->set('include_parent_tags', $includeParentTags);
    return $this;
  }

  public function getTags() {

    if (isset($this->tags)) {
      return $this->tags->referencedEntities();
    }

    return [];
  }

  public function getTagsIds() {

    if (isset($this->tags)) {
      $values = $this->tags->getValue();

      return array_column($values, 'target_id');
    }

    return [];
  }

  public function setTags(array $tags) {

    $this->set('tags', $tags);

    return $this;
  }

  /**
   * Predicate to define if entity has tag.
   *
   * @param \Drupal\taxonomy\TermInterface $term
   *   Term.
   *
   * @return bool
   *   Result.
   */
  public function hasTag(TermInterface $term) {

    return in_array($term->id(), $this->getTagsIds());
  }

  /**
   * Adds given tag.
   *
   * @param \Drupal\taxonomy\TermInterface $term
   *   Term to add.
   *
   * @return $this
   *   Chaining.
   */
  public function addTag(TermInterface $term) {

    if (isset($this->tags)) {
      $this->tags[] = $term;
    }

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
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
      ->setDescription(t('The user ID of author of the Legal position entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
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

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Legal position entity.'))
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

    $fields['text'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Text'))
      ->setDefaultValue('')
      ->setDescription(t('Text of legal position.'))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['reference_number'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Reference'))
      ->setDefaultValue('')
      ->setDescription(t('Reference number in court register.'))
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'court_link',
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['tags'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Tags'))
      ->setDescription(t('Legal position tags.'))
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setSetting('target_type', 'taxonomy_term')
      ->setSetting('handler', 'default:taxonomy_term')
      ->setSetting('handler_settings', [
        'target_bundles' => ['legal_position_tags' => 'legal_position_tags'],
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

    $fields['related'] = BaseFieldDefinition::create('entity_reference')
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setLabel(t('Related Legal positions'))
      ->setDescription(t('Related legal positions from another instances.'))
      ->setSetting('target_type', 'legal_position')
      ->setSetting('handler', 'default')
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['concurrent'] = BaseFieldDefinition::create('entity_reference')
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setLabel(t('Concurrent Legal positions'))
      ->setDescription(t('Concurrent legal positions from another instances.'))
      ->setSetting('target_type', 'legal_position')
      ->setSetting('handler', 'default')
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the Legal position is published.'))
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
      ]);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['include_parent_tags'] = BaseFieldDefinition::create('boolean')
      ->setComputed(TRUE)
      ->setDefaultValue(TRUE)
      ->setClass(IncludeParentTermItemList::class)
      ->setLabel(t('Include parent terms'))
      ->setDescription(t('Include parent terms if not set.'))
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    return $fields;
  }

}
