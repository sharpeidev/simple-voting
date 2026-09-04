<?php

namespace Drupal\simple_voting\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the Option entity.
 *
 * @ContentEntityType(
 *   id = "simple_voting_option",
 *   label = @Translation("Option"),
 *   label_collection = @Translation("Options"),
 *   handlers = {
 *     "list_builder" = "Drupal\simple_voting\OptionListBuilder",
 *     "form" = {
 *       "add" = "Drupal\simple_voting\Form\OptionForm",
 *       "edit" = "Drupal\simple_voting\Form\OptionForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm"
 *     },
 *     "access" = "Drupal\Core\Entity\EntityAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider"
 *     }
 *   },
 *   base_table = "simple_voting_option",
 *   admin_permission = "administer simple voting",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "title",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/content/simple-voting/options/{simple_voting_option}",
 *     "add-form" = "/admin/content/simple-voting/options/add",
 *     "edit-form" = "/admin/content/simple-voting/options/{simple_voting_option}/edit",
 *     "delete-form" = "/admin/content/simple-voting/options/{simple_voting_option}/delete",
 *     "collection" = "/admin/content/simple-voting/options"
 *   }
 * )
 */
class Option extends ContentEntityBase {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['question_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Question'))
      ->setDescription(t('Question associated with this option.'))
      ->setSetting('target_type', 'simple_voting_question')
      ->setRequired(TRUE);

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255);

    $fields['description'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Description'))
      ->setRequired(FALSE);

    $fields['image'] = BaseFieldDefinition::create('image')
      ->setLabel(t('Image'))
      ->setDescription(t('Optional image for the response option.'))
      ->setRequired(FALSE)
      ->setSettings([
        'file_directory' => 'simple-voting/options',
        'alt_field' => TRUE,
        'alt_field_required' => FALSE,
        'title_field' => FALSE,
        'max_filesize' => '5 MB',
        'file_extensions' => 'png jpg jpeg webp',
      ]);

    $fields['weight'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Weight'))
      ->setDefaultValue(0);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Status'))
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => 90,
      ]);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'));

    return $fields;
  }

}
