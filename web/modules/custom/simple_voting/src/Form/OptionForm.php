<?php

namespace Drupal\simple_voting\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Option entities.
 */
class OptionForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state): void {
    $entity = $this->getEntity();

    $is_new = $entity->isNew();
    $entity->save();

    $this->messenger()->addStatus(
      $is_new
        ? $this->t('Option %label has been created.', [
        '%label' => $entity->label(),
      ])
        : $this->t('Option %label has been updated.', [
        '%label' => $entity->label(),
      ])
    );

    $form_state->setRedirect('entity.simple_voting_option.collection');
  }

}
