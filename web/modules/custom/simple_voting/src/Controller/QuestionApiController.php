<?php

namespace Drupal\simple_voting\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\simple_voting\Entity\Question;
use Drupal\simple_voting\Exception\InvalidVoteException;
use Drupal\simple_voting\Exception\VoteAlreadyExistsException;
use Drupal\simple_voting\Exception\VotingDisabledException;
use Drupal\simple_voting\Service\QuestionService;
use Drupal\simple_voting\Service\VotingConfiguration;
use Drupal\simple_voting\Service\VotingResultService;
use Drupal\simple_voting\Service\VotingService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class QuestionApiController extends ControllerBase {

  public function __construct(
    private readonly QuestionService $questionService,
    private readonly VotingService $votingService,
    private readonly VotingResultService $votingResultService,
    private readonly VotingConfiguration $votingConfiguration,
  ) {}

  /**
   * Lists available questions.
   */
  public function list(): JsonResponse {
    $questions = $this->questionService->getAvailableQuestions();

    $data = [];

    foreach ($questions as $question) {
      $data[] = $this->serializeQuestion($question, FALSE);
    }

    return new JsonResponse([
      'data' => $data,
    ]);
  }

  /**
   * Displays a question and its options.
   */
  public function show(string $identifier): JsonResponse {
    $question = $this->questionService->getByIdentifier($identifier);

    if (!$question) {
      return new JsonResponse([
        'error' => 'Question not found.',
      ], 404);
    }

    return new JsonResponse([
      'data' => $this->serializeQuestion($question, TRUE),
    ]);
  }

  /**
   * Registers a vote.
   */
  public function vote(
    string $identifier,
    Request $request,
  ): JsonResponse {
    if (!$this->votingConfiguration->isVotingEnabled()) {
      return new JsonResponse([
        'error' => 'Voting is currently disabled.',
      ], 403);
    }

    $data = json_decode($request->getContent(), TRUE);

    if (!is_array($data) || empty($data['option_id'])) {
      return new JsonResponse([
        'error' => 'The option_id field is required.',
      ], 400);
    }

    try {
      $vote = $this->votingService->vote(
        $identifier,
        (int) $data['option_id'],
        $this->currentUser(),
      );
    }
    catch (VotingDisabledException $exception) {
      return new JsonResponse([
        'error' => $exception->getMessage(),
      ], 403);
    }
    catch (VoteAlreadyExistsException $exception) {
      return new JsonResponse([
        'error' => $exception->getMessage(),
      ], 409);
    }
    catch (InvalidVoteException $exception) {
      return new JsonResponse([
        'error' => $exception->getMessage(),
      ], 400);
    }

    $response = [
      'message' => 'Vote registered successfully.',
      'vote_id' => (int) $vote->id(),
    ];

    $question = $this->questionService->getByIdentifier($identifier);

    if (
      $question
      && $question->get('show_results')->value
    ) {
      $response['results'] = $this->votingResultService
        ->getResults($question);
    }

    return new JsonResponse($response, 201);
  }

  /**
   * Displays voting results.
   */
  public function results(string $identifier): JsonResponse {
    $question = $this->questionService->getByIdentifier($identifier);

    if (!$question) {
      return new JsonResponse([
        'error' => 'Question not found.',
      ], 404);
    }

    if (!$question->get('show_results')->value) {
      return new JsonResponse([
        'error' => 'Results are not available for this question.',
      ], 403);
    }

    if (!$this->hasUserVoted($question, $this->currentUser())) {
      return new JsonResponse([
        'error' => 'You must vote before viewing the results.',
      ], 403);
    }

    return new JsonResponse([
      'data' => $this->votingResultService->getResults($question),
    ]);
  }

  /**
   * Serializes a question.
   */
  private function serializeQuestion(
    Question $question,
    bool $includeOptions,
  ): array {
    $data = [
      'id' => (int) $question->id(),
      'identifier' => $question->get('identifier')->value,
      'title' => $question->get('title')->value,
    ];

    if (!$includeOptions) {
      return $data;
    }

    $data['options'] = [];

    foreach ($this->questionService->getOptions($question) as $option) {
      $image = NULL;

      if (!$option->get('image')->isEmpty()) {
        $file = $option->get('image')->entity;

        if ($file) {
          $image = [
            'url' => $file->createFileUrl(),
            'alt' => $option->get('image')->alt,
          ];
        }
      }

      $data['options'][] = [
        'id' => (int) $option->id(),
        'title' => $option->get('title')->value,
        'description' => $option->get('description')->value,
        'image' => $image,
      ];
    }

    return $data;
  }

  /**
   * Determines whether the current user has already voted.
   */
  private function hasUserVoted(
    Question $question,
    AccountInterface $account,
  ): bool {
    if ($account->isAnonymous()) {
      return FALSE;
    }

    $ids = $this->entityTypeManager()
      ->getStorage('simple_voting_vote')
      ->getQuery()
      ->accessCheck(FALSE)
      ->condition('question_id', $question->id())
      ->condition('user_id', $account->id())
      ->range(0, 1)
      ->execute();

    return !empty($ids);
  }

}
