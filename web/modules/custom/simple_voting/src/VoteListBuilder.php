<?php

namespace Drupal\simple_voting;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

/**
 * Provides a list controller for Vote entities.
 */
class VoteListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader(): array {
    $header['question'] = $this->t('Question');
    $header['option'] = $this->t('Option');
    $header['user'] = $this->t('User');
    $header['created'] = $this->t('Created');

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity): array {
    $question = $entity->get('question_id')->entity;
    $option = $entity->get('option_id')->entity;
    $user = $entity->get('user_id')->entity;

    $row['question'] = $question ? $question->label() : '-';
    $row['option'] = $option ? $option->label() : '-';
    $row['user'] = $user ? $user->getAccountName() : '-';
    $row['created'] = \Drupal::service('date.formatter')
      ->format($entity->get('created')->value);

    return $row + parent::buildRow($entity);
  }

}
