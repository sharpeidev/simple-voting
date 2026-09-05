<?php

namespace Drupal\simple_voting\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\simple_voting\Entity\Question;

class QuestionService {

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * Returns all active questions.
   */
  public function getAvailableQuestions(): array {
    $storage = $this->entityTypeManager
      ->getStorage('simple_voting_question');

    $ids = $storage
      ->getQuery()
      ->accessCheck(FALSE)
      ->condition('status', 1)
      ->sort('created', 'DESC')
      ->execute();

    if (!$ids) {
      return [];
    }

    return $storage->loadMultiple($ids);
  }

  /**
   * Finds an active question by its unique identifier.
   */
  public function getByIdentifier(string $identifier): ?Question {
    $storage = $this->entityTypeManager
      ->getStorage('simple_voting_question');

    $ids = $storage
      ->getQuery()
      ->accessCheck(FALSE)
      ->condition('identifier', $identifier)
      ->condition('status', 1)
      ->range(0, 1)
      ->execute();

    if (!$ids) {
      return NULL;
    }

    return $storage->load(reset($ids));
  }

  /**
   * Returns the active options for a question.
   */
  public function getOptions(Question $question): array {
    $storage = $this->entityTypeManager
      ->getStorage('simple_voting_option');

    $ids = $storage
      ->getQuery()
      ->accessCheck(FALSE)
      ->condition('question_id', $question->id())
      ->condition('status', 1)
      ->sort('weight', 'ASC')
      ->sort('id', 'ASC')
      ->execute();

    if (!$ids) {
      return [];
    }

    return $storage->loadMultiple($ids);
  }

}
