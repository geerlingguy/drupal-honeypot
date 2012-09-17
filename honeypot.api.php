<?php

/**
 * @file
 *
 * API Functionality for Honeypot module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Add time to the Honeypot time limit.
 *
 * In certain circumstances (for example, on forms routinely targeted by
 * spammers), you may want to add an additional time delay. You can use this
 * hook to return additional time (in seconds) to honeypot when it is calculates
 * the time limit for a particular form.
 *
 * @param $honeypot_time_limit
 *   The current honeypot time limit (in seconds), to which any additions you
 *   return will be added.
 * @param $form_values
 *   Array of form values (may be empty).
 * @param $number
 *   Number of times the current user has already fallen into the honeypot trap.
 *
 * @return $additions
 *   Additional time to add to the honeypot_time_limit, in seconds (integer).
 */
function hook_honeypot_time_limit($honeypot_time_limit, $form_values, $number) {
  // If 'some_interesting_value' is set in your form, add 10 seconds to limit.
  if (!empty($form_values['some_interesting_value']) && $form_values['some_interesting_value']) {
    $additions = 10;
  }
  return $additions;
}

/**
 * @} End of "addtogroup hooks".
 */
