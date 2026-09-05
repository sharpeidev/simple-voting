<?php

namespace Drupal\simple_voting;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

/**
 * Provides a list controller for Question entities.
 */
class QuestionListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader(): array {
    $header['identifier'] = $this->t('Identifier');
    $header['title'] = $this->t('Title');
    $header['show_results'] = $this->t('Show results');
    $header['status'] = $this->t('Status');

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity): array {
    $row['identifier'] = $entity->get('identifier')->value;
    $row['title'] = $entity->label();
    $row['show_results'] = $entity->get('show_results')->value
      ? $this->t('Yes')
      : $this->t('No');
    $row['status'] = $entity->get('status')->value
      ? $this->t('Active')
      : $this->t('Inactive');

    $row['operations']['data']['#links']['vote'] = [
      'title' => $this->t('Vote'),
      'url' => $entity->toUrl('vote'),
    ];

    return $row + parent::buildRow($entity);
  }

}
