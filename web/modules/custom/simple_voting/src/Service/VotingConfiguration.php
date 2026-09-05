<?php

namespace Drupal\simple_voting\Service;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Provides access to voting configuration.
 */
class VotingConfiguration {

  /**
   * Constructs the service.
   */
  public function __construct(
    private readonly ConfigFactoryInterface $configFactory,
  ) {}

  /**
   * Determines whether voting is globally enabled.
   */
  public function isVotingEnabled(): bool {
    return (bool) $this->configFactory
      ->get('simple_voting.settings')
      ->get('voting_enabled');
  }

}
