<?php

namespace Drupal\simple_voting\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the Vote entity.
 *
 * @ContentEntityType(
 *   id = "simple_voting_vote",
 *   label = @Translation("Vote"),
 *   label_collection = @Translation("Votes"),
 *   handlers = {
 *     "list_builder" = "Drupal\simple_voting\VoteListBuilder",
 *     "form" = {
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm"
 *     },
 *     "access" = "Drupal\Core\Entity\EntityAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider"
 *     }
 *   },
 *   base_table = "simple_voting_vote",
 *   admin_permission = "administer simple voting",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/content/simple-voting/votes/{simple_voting_vote}",
 *     "delete-form" = "/admin/content/simple-voting/votes/{simple_voting_vote}/delete",
 *     "collection" = "/admin/content/simple-voting/votes"
 *   }
 * )
 */
class Vote extends ContentEntityBase {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['question_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Question'))
      ->setRequired(TRUE)
      ->setSetting('target_type', 'simple_voting_question');

    $fields['option_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Option'))
      ->setRequired(TRUE)
      ->setSetting('target_type', 'simple_voting_option');

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('User'))
      ->setRequired(TRUE)
      ->setSetting('target_type', 'user');

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'));

    return $fields;
  }

}
