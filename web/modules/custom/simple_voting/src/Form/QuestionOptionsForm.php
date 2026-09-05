<?php

namespace Drupal\simple_voting\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\simple_voting\Service\QuestionService;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class QuestionOptionsForm extends FormBase {

  public function __construct(
    private readonly QuestionService $questionService,
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {}

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('simple_voting.question'),
      $container->get('entity_type.manager'),
    );
  }

  public function getFormId(): string {
    return 'simple_voting_question_options_form';
  }

  public function buildForm(
    array $form,
    FormStateInterface $form_state,
    string $identifier = '',
  ): array {
    $question = $this->questionService->getByIdentifier($identifier);

    if (!$question) {
      $form['message'] = [
        '#markup' => $this->t('Question not found.'),
      ];

      return $form;
    }

    $form['question'] = [
      '#markup' => '<h2>' . $question->label() . '</h2>',
    ];

    $form['options'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Title'),
        $this->t('Weight'),
        $this->t('Status'),
      ],
      '#empty' => $this->t('No options registered.'),
    ];

    foreach ($this->questionService->getOptions($question) as $option) {
      $id = $option->id();

      $form['options'][$id]['title'] = [
        '#markup' => $option->label(),
      ];

      $form['options'][$id]['weight'] = [
        '#markup' => $option->get('weight')->value,
      ];

      $form['options'][$id]['status'] = [
        '#markup' => $option->get('status')->value
          ? $this->t('Active')
          : $this->t('Inactive'),
      ];
    }

    $form['add'] = [
      '#type' => 'link',
      '#title' => $this->t('Add option'),
      '#url' => \Drupal\Core\Url::fromRoute(
        'entity.simple_voting_option.add_form',
        [],
        [
          'query' => [
            'question' => $question->id(),
          ],
        ],
      ),
      '#attributes' => [
        'class' => ['button'],
      ],
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {}

}
