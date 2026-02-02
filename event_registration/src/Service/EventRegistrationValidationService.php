<?php

namespace Drupal\event_registration\Service;

use Drupal\event_registration\Service\EventRegistrationDatabaseService;

/**
 * Service for validation operations related to event registration.
 */
class EventRegistrationValidationService {

  /**
   * The database service.
   *
   * @var \Drupal\event_registration\Service\EventRegistrationDatabaseService
   */
  protected $databaseService;

  /**
   * Constructs a new EventRegistrationValidationService object.
   *
   * @param \Drupal\event_registration\Service\EventRegistrationDatabaseService $database_service
   *   The database service.
   */
  public function __construct(EventRegistrationDatabaseService $database_service) {
    $this->databaseService = $database_service;
  }

  /**
   * Validate email format.
   *
   * @param string $email
   *   Email address to validate.
   *
   * @return bool
   *   TRUE if valid, FALSE otherwise.
   */
  public function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== FALSE;
  }

  /**
   * Validate text field for special characters.
   *
   * @param string $text
   *   Text to validate.
   *
   * @return bool
   *   TRUE if valid (no special characters), FALSE otherwise.
   */
  public function validateTextField($text) {
    // Allow only letters, numbers, spaces, and basic punctuation (.,-)
    return preg_match('/^[a-zA-Z0-9\s.\-]+$/', $text);
  }

  /**
   * Check for duplicate registration.
   *
   * @param string $email
   *   Email address.
   * @param string $event_date
   *   Event date.
   *
   * @return bool
   *   TRUE if duplicate exists, FALSE otherwise.
   */
  public function checkDuplicateRegistration($email, $event_date) {
    return $this->databaseService->registrationExists($email, $event_date);
  }

  /**
   * Get validation error message for text fields.
   *
   * @param string $field_name
   *   Name of the field.
   *
   * @return string
   *   Error message.
   */
  public function getTextFieldErrorMessage($field_name) {
    return "The {$field_name} field contains invalid characters. Only letters, numbers, spaces, dots, and hyphens are allowed.";
  }

}
