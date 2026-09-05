<?php

namespace Drupal\simple_voting\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Simple Voting settings.
 */
class SimpleVotingSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['simple_voting.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'simple_voting_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('simple_voting.settings');

    $form['voting_enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Voting enabled'),
      '#description' => $this->t(
        'When disabled, voting is unavailable throughout the system, including external access.'
      ),
      '#default_value' => $config->get('voting_enabled'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->configFactory()
      ->getEditable('simple_voting.settings')
      ->set('voting_enabled', (bool) $form_state->getValue('voting_enabled'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
