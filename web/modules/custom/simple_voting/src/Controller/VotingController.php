<?php

namespace Drupal\simple_voting\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\simple_voting\Service\QuestionService;
use Drupal\simple_voting\Service\VotingResultService;
use Symfony\Component\DependencyInjection\ContainerInterface;

class VotingController extends ControllerBase {

  public function __construct(
    private readonly QuestionService $questionService,
    private readonly VotingResultService $votingResultService,
  ) {}

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('simple_voting.question'),
      $container->get('simple_voting.voting_result'),
    );
  }

  public function results(string $identifier): array {
    $question = $this->questionService->getByIdentifier($identifier);

    if (!$question) {
      return [
        '#markup' => $this->t('Question not found.'),
      ];
    }

    $results = $this->votingResultService->getResults($question);

    $build = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['simple-voting-results'],
      ],
      'title' => [
        '#markup' => '<h2>' . $question->get('title')->value . '</h2>',
      ],
      'results' => [
        '#theme' => 'item_list',
        '#items' => [],
      ],
    ];

    foreach ($results as $result) {
      $build['results']['#items'][] =
        $result['title'] . ': ' . $result['total_votes'];
    }

    return $build;
  }

}
