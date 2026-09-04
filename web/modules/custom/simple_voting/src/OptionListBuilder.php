<?php

namespace Drupal\simple_voting;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

/**
 * Provides a list controller for Option entities.
 */
class OptionListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader(): array {
    $header['question'] = $this->t('Question');
    $header['title'] = $this->t('Title');
    $header['weight'] = $this->t('Weight');
    $header['status'] = $this->t('Status');

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity): array {
    $question = $entity->get('question_id')->entity;

    $row['question'] = $question ? $question->label() : '-';
    $row['title'] = $entity->label();
    $row['weight'] = $entity->get('weight')->value;
    $row['status'] = $entity->get('status')->value
      ? $this->t('Active')
      : $this->t('Inactive');

    return $row + parent::buildRow($entity);
  }

}
