<?php

namespace Drupal\simple_voting\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the Question entity.
 *
 * @ContentEntityType(
 *   id = "simple_voting_question",
 *   label = @Translation("Question"),
 *   label_collection = @Translation("Questions"),
 *   handlers = {
 *     "list_builder" = "Drupal\simple_voting\QuestionListBuilder",
 *     "form" = {
 *       "add" = "Drupal\simple_voting\Form\QuestionForm",
 *       "edit" = "Drupal\simple_voting\Form\QuestionForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm"
 *     },
 *     "access" = "Drupal\Core\Entity\EntityAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider"
 *     }
 *   },
 *   base_table = "simple_voting_question",
 *   admin_permission = "administer simple voting",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "title",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/content/simple-voting/questions/{simple_voting_question}",
 *     "add-form" = "/admin/content/simple-voting/questions/add",
 *     "edit-form" = "/admin/content/simple-voting/questions/{simple_voting_question}/edit",
 *     "delete-form" = "/admin/content/simple-voting/questions/{simple_voting_question}/delete",
 *     "collection" = "/admin/content/simple-voting/questions"
 *   }
 * )
 */
class Question extends ContentEntityBase {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['identifier'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Identifier'))
      ->setDescription(t('Unique identifier used to identify the question.'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
      ->addConstraint('UniqueField');

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setDescription(t('Question title.'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255);

    $fields['show_results'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Show results after voting'))
      ->setDescription(t('Whether the total votes should be displayed after the user votes.'))
      ->setDefaultValue(FALSE);

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
