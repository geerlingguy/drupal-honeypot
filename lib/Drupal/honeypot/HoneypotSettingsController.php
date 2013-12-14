<?php

/**
 * @file
 * Contains Drupal\honeypot\HoneypotSettingsController.
 */

namespace Drupal\honeypot;

use Drupal\Core\Form\FormInterface;

/**
 * Returns responses for Honeypot module routes.
 */
class HoneypotSettingsController implements FormInterface {

  /**
   * Get a value from the retrieved form settings array.
   */
  public function getFormSettingsValue($form_settings, $form_id) {
    // If there are settings in the array and the form ID already has a setting,
    // return the saved setting for the form ID.
    if (!empty($form_settings) && isset($form_settings[$form_id])) {
      return $form_settings[$form_id];
    }
    // Default to false.
    else {
      return 0;
    }
  }

  /**
   * Implements \Drupal\Core\Form\FormInterface::getFormID().
   */
  public function getFormID() {
    return 'honeypot_settings_form';
  }

  /**
   * Implements \Drupal\Core\Form\FormInterface::buildForm().
   */
  public function buildForm(array $form, array &$form_state) {
    // Honeypot Configuration.
    $form['configuration'] = array(
      '#type' => 'fieldset',
      '#title' => t('Honeypot Configuration'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    );
    $form['configuration']['protect_all_forms'] = array(
      '#type' => 'checkbox',
      '#title' => t('Protect all forms with Honeypot'),
      '#description' => t('Enable Honeypot protection for ALL forms on this site (it is best to only enable Honeypot for the forms you need below).'),
      '#default_value' => config('honeypot.settings')->get('protect_all_forms'),
    );
    $form['configuration']['protect_all_forms']['#description'] .= '<br />' . t('<strong>Page caching will be disabled on any page where a form is present if the Honeypot time limit is not set to 0.</strong>');
    $form['configuration']['log'] = array(
      '#type' => 'checkbox',
      '#title' => t('Log blocked form submissions'),
      '#description' => t('Log submissions that are blocked due to Honeypot protection.'),
      '#default_value' => config('honeypot.settings')->get('log'),
    );
    $form['configuration']['element_name'] = array(
      '#type' => 'textfield',
      '#title' => t('Honeypot element name'),
      '#description' => t("The name of the Honeypot form field. It's usually most effective to use a generic name like email, homepage, or name, but this should be changed if it interferes with fields that are already in your forms. Must not contain spaces or special characters."),
      '#default_value' => config('honeypot.settings')->get('element_name'),
      '#required' => TRUE,
      '#size' => 30,
    );
    $form['configuration']['time_limit'] = array(
      '#type' => 'textfield',
      '#title' => t('Honeypot time limit'),
      '#description' => t('Minimum time required before form should be considered entered by a human instead of a bot. Set to 0 to disable.'),
      '#default_value' => config('honeypot.settings')->get('time_limit'),
      '#required' => TRUE,
      '#size' => 5,
      '#field_suffix' => t('seconds'),
    );
    $form['configuration']['time_limit']['#description'] .= '<br />' . t('<strong>Page caching will be disabled if there is a form protected by time limit on the page.</strong>');

    // Honeypot Enabled forms.
    $form_settings = config('honeypot.settings')->get('form_settings');
    $form['form_settings'] = array(
      '#type' => 'fieldset',
      '#title' => t('Honeypot Enabled Forms'),
      '#description' => t("Check the boxes next to individual forms on which you'd like Honeypot protection enabled."),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
      '#tree' => TRUE,
      '#states' => array(
        // Hide this fieldset when all forms are protected.
        'invisible' => array(
          'input[name="protect_all_forms"]' => array('checked' => TRUE),
        ),
      ),
    );

    // Generic forms.
    $form['form_settings']['general_forms'] = array('#markup' => '<h5>' . t('General Forms') . '</h5>');
    // User register form.
    $form['form_settings']['user_register_form'] = array(
      '#type' => 'checkbox',
      '#title' => t('User Registration form'),
      '#default_value' => $this->getFormSettingsValue($form_settings, 'user_register_form'),
    );
    // User password form.
    $form['form_settings']['user_pass'] = array(
      '#type' => 'checkbox',
      '#title' => t('User Password Reset form'),
      '#default_value' => $this->getFormSettingsValue($form_settings, 'user_pass'),
    );

    // If webform.module enabled, add webforms.
    // TODO D8 - See if D8 version of Webform.module still uses this form ID.
    if (module_exists('webform')) {
      $form['form_settings']['webforms'] = array(
        '#type' => 'checkbox',
        '#title' => t('Webforms (all)'),
        '#default_value' => $this->getFormSettingsValue($form_settings, 'webforms'),
      );
    }

    // If contact.module enabled, add contact forms.
    if (module_exists('contact')) {
      // TODO D8 - Sitewide contact forms are now dynamically-named.
      $form['form_settings']['contact_forms'] = array('#markup' => '<h5>' . t('Contact Forms') . '</h5>');
      // Sitewide contact form.
      $form['form_settings']['contact_site_form'] = array(
        '#type' => 'checkbox',
        '#title' => t('Sitewide Contact form'),
        '#default_value' => $this->getFormSettingsValue($form_settings, 'contact_site_form'),
      );
      // Sitewide personal form.
      $form['form_settings']['contact_personal_form'] = array(
        '#type' => 'checkbox',
        '#title' => t('Personal Contact forms'),
        '#default_value' => $this->getFormSettingsValue($form_settings, '_contact_message_form'),
      );
    }

    // Get node types for node forms and node comment forms.
    $types = node_type_get_types();
    if (!empty($types)) {
      // Node forms.
      $form['form_settings']['node_forms'] = array('#markup' => '<h5>' . t('Node Forms') . '</h5>');
      foreach ($types as $type) {
        $id = $type->type . '_node_form';
        $form['form_settings'][$id] = array(
          '#type' => 'checkbox',
          '#title' => t('@name node form', array('@name' => $type->name)),
          '#default_value' => $this->getFormSettingsValue($form_settings, $id),
        );
      }

      // Comment forms.
      if (module_exists('comment')) {
        $form['form_settings']['comment_forms'] = array('#markup' => '<h5>' . t('Comment Forms') . '</h5>');
        foreach ($types as $type) {
          $id = 'comment_node_' . $type->type . '_comment_form';
          $form['form_settings'][$id] = array(
            '#type' => 'checkbox',
            '#title' => t('@name comment form', array('@name' => $type->name)),
            '#default_value' => $this->getFormSettingsValue($form_settings, $id),
          );
        }
      }
    }

    // For now, manually add submit button. Hopefully, by the time D8 is
    // released, there will be something like system_settings_form() in D7.
    $form['actions']['#type'] = 'container';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save configuration'),
    );

    return $form;
  }

  /**
   * Implements \Drupal\Core\Form\FormInterface::validateForm().
   */
  public function validateForm(array &$form, array &$form_state) {
    // Make sure the time limit is a positive integer or 0.
    $time_limit = $form_state['values']['time_limit'];
    if ((is_numeric($time_limit) && $time_limit > 0) || $time_limit === '0') {
      if (ctype_digit($time_limit)) {
        // Good to go.
      }
      else {
        form_set_error('time_limit', $form_state, t("The time limit must be a positive integer or 0."));
      }
    }
    else {
      form_set_error('time_limit', $form_state, t("The time limit must be a positive integer or 0."));
    }

    // Make sure Honeypot element name only contains A-Z, 0-9.
    if (!preg_match("/^[-_a-zA-Z0-9]+$/", $form_state['values']['element_name'])) {
      form_set_error('element_name', $form_state, t("The element name cannot contain spaces or other special characters."));
    }
  }

  /**
   * Implements \Drupal\Core\Form\FormInterface::submitForm().
   */
  public function submitForm(array &$form, array &$form_state) {
    $config = config('honeypot.settings');

    // Save all the non-form-id values from $form_state.
    foreach ($form_state['values'] as $key => $value) {
      if ($key != 'form_settings') {
        $config->set($key, $value);
      }
    }

    // Save the honeypot forms from $form_state into a 'form_settings' array.
    $config->set('form_settings', $form_state['values']['form_settings']);

    $config->save();

    // Clear the honeypot protected forms cache.
    cache_invalidate_tags(array('honeypot_protected_forms' => TRUE));

    // Tell the user the settings have been saved.
    drupal_set_message(t('The configuration options have been saved.'));
  }

}
