<?php

namespace Drupal\simple_voting\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\simple_voting\Exception\InvalidVoteException;
use Drupal\simple_voting\Exception\VoteAlreadyExistsException;
use Drupal\simple_voting\Exception\VotingDisabledException;
use Drupal\simple_voting\Service\QuestionService;
use Drupal\simple_voting\Service\VotingService;
use Symfony\Component\DependencyInjection\ContainerInterface;

class VoteForm extends FormBase {

  public function __construct(
    private readonly QuestionService $questionService,
    private readonly VotingService $votingService,
  ) {}

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('simple_voting.question'),
      $container->get('simple_voting.voting'),
    );
  }

  public function getFormId(): string {
    return 'simple_voting_vote_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, string $identifier = ''): array {
    $question = $this->questionService->getByIdentifier($identifier);

    if (!$question) {
      $form['message'] = [
        '#markup' => $this->t('Question not found.'),
      ];
      return $form;
    }

    $form['question'] = [
      '#markup' => '<h2>' . $question->get('title')->value . '</h2>',
    ];

    $options = [];

    foreach ($this->questionService->getOptions($question) as $option) {
      $options[$option->id()] = $option->get('title')->value;
    }

    $form['option_id'] = [
      '#type' => 'radios',
      '#title' => $this->t('Select an option'),
      '#options' => $options,
      '#required' => TRUE,
    ];

    $form['identifier'] = [
      '#type' => 'hidden',
      '#value' => $identifier,
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Vote'),
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $identifier = $form_state->getValue('identifier');
    $option_id = (int) $form_state->getValue('option_id');

    try {
      $vote = $this->votingService->vote(
        $identifier,
        $option_id,
        $this->currentUser(),
      );

      $this->messenger()->addStatus(
        $this->t('Vote registered successfully.')
      );

      $question = $this->questionService->getByIdentifier($identifier);

      if ($question && $question->get('show_results')->value) {
        $form_state->setRedirect(
          'simple_voting.cms_results',
          ['identifier' => $identifier]
        );
        return;
      }

      $form_state->setRedirect(
        'entity.simple_voting_question.collection'
      );
    }
    catch (VotingDisabledException|VoteAlreadyExistsException|InvalidVoteException $exception) {
      $this->messenger()->addError($exception->getMessage());
    }
  }

}
