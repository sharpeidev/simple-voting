<?php

namespace Drupal\simple_voting\Service;

use Drupal\Core\Database\IntegrityConstraintViolationException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\simple_voting\Entity\Vote;
use Drupal\simple_voting\Exception\InvalidVoteException;
use Drupal\simple_voting\Exception\VoteAlreadyExistsException;
use Drupal\simple_voting\Exception\VotingDisabledException;

/**
 * Provides voting business logic.
 */
class VotingService {

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly VotingConfiguration $votingConfiguration,
    private readonly VotingLogger $votingLogger,
  ) {}

  /**
   * Registers a vote.
   */
  public function vote(
    string $identifier,
    int $optionId,
    AccountInterface $account,
  ): Vote {
    if (!$this->votingConfiguration->isVotingEnabled()) {
      $this->votingLogger->voteRejected(
        'Voting is disabled.',
        (int) $account->id(),
      );

      throw new VotingDisabledException('Voting is currently disabled.');
    }

    if ($account->isAnonymous()) {
      $this->votingLogger->voteRejected(
        'Authentication is required.',
        (int) $account->id(),
      );

      throw new InvalidVoteException('Authentication is required to vote.');
    }

    $question = $this->loadQuestionByIdentifier($identifier);

    if (!$question || !(bool) $question->get('status')->value) {
      $this->votingLogger->voteRejected(
        'Question not found or inactive.',
        (int) $account->id(),
      );

      throw new InvalidVoteException('Question not found or inactive.');
    }

    $option = $this->entityTypeManager
      ->getStorage('simple_voting_option')
      ->load($optionId);

    if (!$option || !$option->get('status')->value) {
      $this->votingLogger->voteRejected(
        'Option not found or inactive.',
        (int) $account->id(),
        (int) $question->id(),
      );

      throw new InvalidVoteException('Option not found or inactive.');
    }

    if ((int) $option->get('question_id')->target_id !== (int) $question->id()) {
      $this->votingLogger->voteRejected(
        'Option does not belong to the question.',
        (int) $account->id(),
        (int) $question->id(),
      );

      throw new InvalidVoteException(
        'Option does not belong to the question.'
      );
    }

    $existingVote = $this->findUserVote(
      (int) $question->id(),
      (int) $account->id(),
    );

    if ($existingVote) {
      $this->votingLogger->voteRejected(
        'User has already voted on this question.',
        (int) $account->id(),
        (int) $question->id(),
      );

      throw new VoteAlreadyExistsException(
        'User has already voted on this question.'
      );
    }

    try {
      $vote = $this->entityTypeManager
        ->getStorage('simple_voting_vote')
        ->create([
          'question_id' => $question->id(),
          'option_id' => $option->id(),
          'user_id' => $account->id(),
        ]);

      $vote->save();
    }
    catch (IntegrityConstraintViolationException $exception) {
      $this->votingLogger->voteRejected(
        'Duplicate vote detected by database constraint.',
        (int) $account->id(),
        (int) $question->id(),
      );

      throw new VoteAlreadyExistsException(
        'User has already voted on this question.',
        0,
        $exception,
      );
    }

    $this->votingLogger->voteRegistered(
      (int) $account->id(),
      (int) $question->id(),
      (int) $option->id(),
    );

    return $vote;
  }

  /**
   * Loads an active question by identifier.
   */
  private function loadQuestionByIdentifier(string $identifier): ?object {
    $ids = $this->entityTypeManager
      ->getStorage('simple_voting_question')
      ->getQuery()
      ->accessCheck(FALSE)
      ->condition('identifier', $identifier)
      ->condition('status', 1)
      ->range(0, 1)
      ->execute();

    if (!$ids) {
      return NULL;
    }

    return $this->entityTypeManager
      ->getStorage('simple_voting_question')
      ->load(reset($ids));
  }

  /**
   * Finds an existing vote by user and question.
   */
  private function findUserVote(
    int $questionId,
    int $userId,
  ): ?Vote {
    $ids = $this->entityTypeManager
      ->getStorage('simple_voting_vote')
      ->getQuery()
      ->accessCheck(FALSE)
      ->condition('question_id', $questionId)
      ->condition('user_id', $userId)
      ->range(0, 1)
      ->execute();

    if (!$ids) {
      return NULL;
    }

    return $this->entityTypeManager
      ->getStorage('simple_voting_vote')
      ->load(reset($ids));
  }

}
