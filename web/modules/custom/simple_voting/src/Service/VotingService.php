<?php

namespace Drupal\simple_voting\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\simple_voting\Entity\Vote;
use Drupal\simple_voting\Exception\InvalidVoteException;
use Drupal\simple_voting\Exception\VoteAlreadyExistsException;
use Drupal\simple_voting\Exception\VotingDisabledException;
use Drupal\Core\Database\IntegrityConstraintViolationException;

class VotingService {

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly VotingConfiguration $votingConfiguration,
  ) {}

  /**
   * Registers a vote.
   *
   * @throws VotingDisabledException
   * @throws InvalidVoteException
   * @throws VoteAlreadyExistsException
   */
  public function vote(
    string $identifier,
    int $optionId,
    AccountInterface $account,
  ): Vote {
    if (!$this->votingConfiguration->isVotingEnabled()) {
      throw new VotingDisabledException('Voting is currently disabled.');
    }

    if ($account->isAnonymous()) {
      throw new InvalidVoteException('Authentication is required to vote.');
    }

    $question = $this->loadQuestionByIdentifier($identifier);

    if (!$question || !(bool) $question->get('status')->value) {
      throw new InvalidVoteException('Question not found or inactive.');
    }

    $option = $this->entityTypeManager
      ->getStorage('simple_voting_option')
      ->load($optionId);

    if (!$option || !$option->get('status')->value) {
      throw new InvalidVoteException('Option not found or inactive.');
    }

    if ((int) $option->get('question_id')->target_id !== (int) $question->id()) {
      throw new InvalidVoteException('Option does not belong to the question.');
    }

    $existing_vote = $this->findUserVote(
      (int) $question->id(),
      (int) $account->id(),
    );

    if ($existing_vote) {
      throw new VoteAlreadyExistsException('User has already voted on this question.');
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
      throw new VoteAlreadyExistsException(
        'User has already voted on this question.',
        0,
        $exception,
      );
    }

    return $vote;
  }

  /**
   * Loads a question by its unique identifier.
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
   * Finds a user's vote for a question.
   */
  private function findUserVote(int $questionId, int $userId): ?Vote {
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
