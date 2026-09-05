<?php

namespace Drupal\simple_voting\Service;

use Psr\Log\LoggerInterface;

class VotingLogger {

  public function __construct(
    private readonly LoggerInterface $logger,
  ) {}

  public function voteRegistered(
    int $userId,
    int $questionId,
    int $optionId,
  ): void {
    $this->logger->info(
      'Vote registered. User: @user, Question: @question, Option: @option.',
      [
        '@user' => $userId,
        '@question' => $questionId,
        '@option' => $optionId,
      ],
    );
  }

  public function voteRejected(
    string $reason,
    int $userId,
    ?int $questionId = NULL,
  ): void {
    $this->logger->warning(
      'Vote rejected. Reason: @reason. User: @user. Question: @question.',
      [
        '@reason' => $reason,
        '@user' => $userId,
        '@question' => $questionId ?? 'unknown',
      ],
    );
  }

}
