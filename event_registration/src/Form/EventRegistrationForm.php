<?php

namespace Drupal\event_registration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\event_registration\Service\EventRegistrationDatabaseService;
use Drupal\event_registration\Service\EventRegistrationEmailService;
use Drupal\event_registration\Service\EventRegistrationValidationService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form for event registration.
 */
class EventRegistrationForm extends FormBase {

  /**
   * The database service.
   *
   * @var \Drupal\event_registration\Service\EventRegistrationDatabaseService
   */
  protected $databaseService;

  /**
   * The email service.
   *
   * @var \Drupal\event_registration\Service\EventRegistrationEmailService
   */
  protected $emailService;

  /**
   * The validation service.
   *
   * @var \Drupal\event_registration\Service\EventRegistrationValidationService
   */
  protected $validationService;

  /**
   * Constructs a new EventRegistrationForm object.
   *
   * @param \Drupal\event_registration\Service\EventRegistrationDatabaseService $database_service
   *   The database service.
   * @param \Drupal\event_registration\Service\EventRegistrationEmailService $email_service
   *   The email service.
   * @param \Drupal\event_registration\Service\EventRegistrationValidationService $validation_service
   *   The validation service.
   */
  public function __construct(
    EventRegistrationDatabaseService $database_service,
    EventRegistrationEmailService $email_service,
    EventRegistrationValidationService $validation_service
  ) {
    $this->databaseService = $database_service;
    $this->emailService = $email_service;
    $this->validationService = $validation_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('event_registration.database_service'),
      $container->get('event_registration.email_service'),
      $container->get('event_registration.validation_service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'event_registration_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Check if there are any active events.
    $categories = $this->databaseService->getEventCategories();
    
    if (empty($categories)) {
      $form['no_events'] = [
        '#markup' => '<p>' . $this->t('There are no events available for registration at this time.') . '</p>',
      ];
      return $form;
    }

    $form['#prefix'] = '<div id="event-registration-form-wrapper">';
    $form['#suffix'] = '</div>';

    $form['full_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Full Name'),
      '#required' => TRUE,
      '#maxlength' => 255,
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email Address'),
      '#required' => TRUE,
      '#maxlength' => 255,
    ];

    $form['college_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('College Name'),
      '#required' => TRUE,
      '#maxlength' => 255,
    ];

    $form['department'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Department'),
      '#required' => TRUE,
      '#maxlength' => 255,
    ];

    $form['event_category'] = [
      '#type' => 'select',
      '#title' => $this->t('Category of the Event'),
      '#required' => TRUE,
      '#options' => ['' => $this->t('- Select -')] + $categories,
      '#ajax' => [
        'callback' => '::updateEventDates',
        'wrapper' => 'event-date-wrapper',
        'event' => 'change',
      ],
    ];

    $form['event_date_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'event-date-wrapper'],
    ];

    $selected_category = $form_state->getValue('event_category');
    if ($selected_category) {
      $event_dates = $this->databaseService->getEventDatesByCategory($selected_category);
      
      $form['event_date_wrapper']['event_date'] = [
        '#type' => 'select',
        '#title' => $this->t('Event Date'),
        '#required' => TRUE,
        '#options' => ['' => $this->t('- Select -')] + $event_dates,
        '#ajax' => [
          'callback' => '::updateEventNames',
          'wrapper' => 'event-name-wrapper',
          'event' => 'change',
        ],
      ];
    }
    else {
      $form['event_date_wrapper']['event_date'] = [
        '#type' => 'select',
        '#title' => $this->t('Event Date'),
        '#required' => TRUE,
        '#options' => ['' => $this->t('- Select Category First -')],
        '#disabled' => TRUE,
      ];
    }

    $form['event_name_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'event-name-wrapper'],
    ];

    $selected_date = $form_state->getValue('event_date');
    if ($selected_category && $selected_date) {
      $event_names = $this->databaseService->getEventNamesByCategoryAndDate($selected_category, $selected_date);
      
      $form['event_name_wrapper']['event_name'] = [
        '#type' => 'select',
        '#title' => $this->t('Event Name'),
        '#required' => TRUE,
        '#options' => ['' => $this->t('- Select -')] + $event_names,
      ];
    }
    else {
      $form['event_name_wrapper']['event_name'] = [
        '#type' => 'select',
        '#title' => $this->t('Event Name'),
        '#required' => TRUE,
        '#options' => ['' => $this->t('- Select Date First -')],
        '#disabled' => TRUE,
      ];
    }

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Register'),
    ];

    return $form;
  }

  /**
   * AJAX callback to update event dates.
   */
  public function updateEventDates(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $response->addCommand(new ReplaceCommand('#event-date-wrapper', $form['event_date_wrapper']));
    $response->addCommand(new ReplaceCommand('#event-name-wrapper', $form['event_name_wrapper']));
    return $response;
  }

  /**
   * AJAX callback to update event names.
   */
  public function updateEventNames(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $response->addCommand(new ReplaceCommand('#event-name-wrapper', $form['event_name_wrapper']));
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    // Validate email format.
    if (!$this->validationService->validateEmail($values['email'])) {
      $form_state->setErrorByName('email', 
        $this->t('Please enter a valid email address.'));
    }

    // Validate text fields for special characters.
    $text_fields = ['full_name', 'college_name', 'department'];
    foreach ($text_fields as $field) {
      if (!$this->validationService->validateTextField($values[$field])) {
        $form_state->setErrorByName($field, 
          $this->t($this->validationService->getTextFieldErrorMessage(ucwords(str_replace('_', ' ', $field)))));
      }
    }

    // Check for duplicate registration.
    if (isset($values['event_date']) && $values['event_date']) {
      if ($this->validationService->checkDuplicateRegistration($values['email'], $values['event_date'])) {
        $form_state->setErrorByName('email', 
          $this->t('You have already registered for an event on this date. Duplicate registrations are not allowed.'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $registration_data = [
      'full_name' => $values['full_name'],
      'email' => $values['email'],
      'college_name' => $values['college_name'],
      'department' => $values['department'],
      'event_category' => $values['event_category'],
      'event_date' => $values['event_date'],
      'event_id' => $values['event_name'],
    ];

    // Save registration.
    $this->databaseService->saveRegistration($registration_data);

    // Get event details.
    $event = $this->databaseService->getEventById($values['event_name']);
    $event_name = $event ? $event->event_name : 'Event';

    // Send confirmation emails.
    $this->emailService->sendConfirmationEmail($registration_data, $event_name);

    $this->messenger()->addStatus($this->t('Thank you for registering! A confirmation email has been sent to @email.', 
      ['@email' => $values['email']]));
  }

}
