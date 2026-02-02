<?php

namespace Drupal\event_registration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\event_registration\Service\EventRegistrationDatabaseService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form for configuring events.
 */
class EventConfigForm extends FormBase {

  /**
   * The database service.
   *
   * @var \Drupal\event_registration\Service\EventRegistrationDatabaseService
   */
  protected $databaseService;

  /**
   * Constructs a new EventConfigForm object.
   *
   * @param \Drupal\event_registration\Service\EventRegistrationDatabaseService $database_service
   *   The database service.
   */
  public function __construct(EventRegistrationDatabaseService $database_service) {
    $this->databaseService = $database_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('event_registration.database_service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'event_registration_event_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['event_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Event Name'),
      '#required' => TRUE,
      '#maxlength' => 255,
    ];

    $form['event_category'] = [
      '#type' => 'select',
      '#title' => $this->t('Category of the Event'),
      '#required' => TRUE,
      '#options' => [
        '' => $this->t('- Select -'),
        'Online Workshop' => $this->t('Online Workshop'),
        'Hackathon' => $this->t('Hackathon'),
        'Conference' => $this->t('Conference'),
        'One-day Workshop' => $this->t('One-day Workshop'),
      ],
    ];

    $form['event_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Event Date'),
      '#required' => TRUE,
    ];

    $form['registration_start_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Event Registration Start Date'),
      '#required' => TRUE,
    ];

    $form['registration_end_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Event Registration End Date'),
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save Event'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $registration_start = $form_state->getValue('registration_start_date');
    $registration_end = $form_state->getValue('registration_end_date');
    $event_date = $form_state->getValue('event_date');

    // Validate that registration end date is after start date.
    if ($registration_end < $registration_start) {
      $form_state->setErrorByName('registration_end_date', 
        $this->t('Registration end date must be after the start date.'));
    }

    // Validate that event date is after registration end date.
    if ($event_date < $registration_end) {
      $form_state->setErrorByName('event_date', 
        $this->t('Event date must be after the registration end date.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    
    $data = [
      'event_name' => $values['event_name'],
      'event_category' => $values['event_category'],
      'event_date' => $values['event_date'],
      'registration_start_date' => $values['registration_start_date'],
      'registration_end_date' => $values['registration_end_date'],
    ];

    $this->databaseService->saveEventConfiguration($data);

    $this->messenger()->addStatus($this->t('Event has been successfully configured.'));
  }

}
