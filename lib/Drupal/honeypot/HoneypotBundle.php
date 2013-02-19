<?php

/**
 * @file
 * Contains Drupal\honeypot\HoneypotBundle.
 */

namespace Drupal\honeypot;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Provides the honeypot dependency injection container.
 */
class HoneypotBundle extends Bundle {

  /**
   * Overrides \Symfony\Component\HttpKernel\Bundle\Bundle::build().
   */
  public function build(ContainerBuilder $container) {
    // Register the HoneypotSettingsController class with the DIC.
    $container->register('honeypot.settings.form', 'Drupal\honeypot\HoneypotSettingsController');
  }

}