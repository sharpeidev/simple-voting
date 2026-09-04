<?php

namespace Drupal\simple_voting\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Question entities.
 */
class QuestionForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state): void {
    $entity = $this->getEntity();
    $status = $entity->isNew()
      ? $this->t('created')
      : $this->t('updated');

    $entity->save();

    $this->messenger()->addStatus(
      $this->t('Question %label has been @status.', [
        '%label' => $entity->label(),
        '@status' => $status,
      ])
    );

    $form_state->setRedirect('entity.simple_voting_question.collection');
  }

}
