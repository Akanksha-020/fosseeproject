<?php

namespace Drupal\event_registration\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure event registration settings for this site.
 */
class EventRegistrationConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'event_registration_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['event_registration.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('event_registration.settings');

    $form['admin_notification_email'] = [
      '#type' => 'email',
      '#title' => $this->t('Admin Notification Email'),
      '#description' => $this->t('Email address where admin notifications will be sent.'),
      '#default_value' => $config->get('admin_notification_email'),
      '#required' => TRUE,
    ];

    $form['enable_admin_notifications'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Admin Notifications'),
      '#description' => $this->t('Check this box to enable email notifications to admin for each registration.'),
      '#default_value' => $config->get('enable_admin_notifications') ?? TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('event_registration.settings')
      ->set('admin_notification_email', $form_state->getValue('admin_notification_email'))
      ->set('enable_admin_notifications', $form_state->getValue('enable_admin_notifications'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
