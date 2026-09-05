<?php

namespace Drupal\simple_voting\Service;

use Drupal\Core\Database\Connection;
use Drupal\simple_voting\Entity\Question;

class VotingResultService {

  public function __construct(
    private readonly Connection $database,
  ) {}

  /**
   * Returns the voting results for a question.
   */
  public function getResults(Question $question): array {
    $query = $this->database
      ->select('simple_voting_vote', 'vote');

    $option_alias = $query->leftJoin(
      'simple_voting_option',
      'option',
      'option.id = vote.option_id'
    );

    $query->addField('vote', 'option_id', 'option_id');
    $query->addField($option_alias, 'title', 'title');
    $query->addExpression('COUNT(vote.id)', 'total_votes');

    $query->condition('vote.question_id', $question->id());
    $query->condition($option_alias . '.status', 1);

    $query->groupBy('vote.option_id');
    $query->groupBy($option_alias . '.title');

    $query->orderBy($option_alias . '.weight', 'ASC');

    $results = [];

    foreach ($query->execute() as $row) {
      $results[] = [
        'option_id' => (int) $row->option_id,
        'title' => $row->title,
        'total_votes' => (int) $row->total_votes,
      ];
    }

    return $results;
  }

}
