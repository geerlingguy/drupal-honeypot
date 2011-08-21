<?php

/**
 * @file
 *
 * Honeypot administration form.
 */

/**
 * Honeypot administration page.
 */
function honeypot_admin_form($form, &$form_state) {
  // Honeypot Configuration.
  $form['configuration'] = array(
    '#type' => 'fieldset',
    '#title' => t('Honeypot Configuration'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
  );
    $form['configuration']['honeypot_protect_all_forms'] = array(
      '#type' => 'checkbox',
      '#title' => t('Protect all forms with Honeypot'),
      '#description' => t('This will enable Honeypot protection for ALL forms on this site, regardless of the settings in the Honeypot enabled forms section below.'),
      '#default_value' => variable_get('honeypot_protect_all_forms', 0),
    );
    $form['configuration']['honeypot_element_name'] = array(
      '#type' => 'textfield',
      '#title' => t('Honeypot element name'),
      '#description' => t("The name of the Honeypot form field. It's usually most effective to use a generic name like email, homepage, or name, but this should be changed if it interferes with fields that are already in your forms."),
      '#default_value' => variable_get('honeypot_element_name', 'homepage'),
      '#required' => TRUE,
      '#size' => 30,
    );
    $form['configuration']['honeypot_time_limit'] = array(
      '#type' => 'textfield',
      '#title' => t('Honeypot time limit'),
      '#description' => t('Minimum time required before form should be considered entered by a human instead of a bot. Set to 0 to disable.'),
      '#default_value' => variable_get('honeypot_time_limit', 5),
      '#required' => TRUE,
      '#size' => 5,
    );

  // Honeypot Enabled forms.
  $form['enabled_forms'] = array(
    '#type' => 'fieldset',
    '#title' => t('Honeypot Enabled Forms'),
    '#description' => t("Check the boxes next to individual forms on which you'd like Honeypot protection enabled."),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
    '#states' => array(
      // Hide this fieldset when all forms are protected.
      'invisible' => array(
        'input[name="honeypot_protect_all_forms"]' => array('checked' => TRUE),
      ),
    ),
  );
    $form['enabled_forms']['honeypot_form_user_register_form'] = array(
      '#type' => 'checkbox',
      '#title' => t('User Registration form'),
      '#default_value' => variable_get('honeypot_form_user_register_form', 0),
    );
    // @todo - Add node forms, comment forms, etc...

  return system_settings_form($form);
}

/**
 * Validate the admin form.
 */
function honeypot_admin_form_validate($form, &$form_state) {
  // Make sure the time limit is a positive integer or 0.
  $time_limit = $form_state['values']['honeypot_time_limit'];
  if ((is_numeric($time_limit) && $time_limit > 0) || $time_limit === '0') {
    if (ctype_digit($time_limit)) {
      // Good to go.
    }
    else {
      form_set_error('honeypot_time_limit', t("The time limit must be a positive integer or 0."));
    }
  }
  else {
    form_set_error('honeypot_time_limit', t("The time limit must be a positive integer or 0."));
  }
}
