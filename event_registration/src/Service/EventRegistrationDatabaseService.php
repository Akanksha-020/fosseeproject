<?php

namespace Drupal\event_registration\Service;

use Drupal\Core\Database\Connection;

/**
 * Service for database operations related to event registration.
 */
class EventRegistrationDatabaseService {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructs a new EventRegistrationDatabaseService object.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * Save event configuration.
   *
   * @param array $data
   *   Event configuration data.
   *
   * @return int
   *   The event ID.
   */
  public function saveEventConfiguration(array $data) {
    return $this->database->insert('event_configuration')
      ->fields([
        'event_name' => $data['event_name'],
        'event_category' => $data['event_category'],
        'event_date' => $data['event_date'],
        'registration_start_date' => $data['registration_start_date'],
        'registration_end_date' => $data['registration_end_date'],
        'created' => \Drupal::time()->getRequestTime(),
      ])
      ->execute();
  }

  /**
   * Get all event categories.
   *
   * @return array
   *   Array of event categories.
   */
  public function getEventCategories() {
    $query = $this->database->select('event_configuration', 'ec')
      ->fields('ec', ['event_category'])
      ->distinct()
      ->condition('registration_start_date', date('Y-m-d'), '<=')
      ->condition('registration_end_date', date('Y-m-d'), '>=');
    
    $results = $query->execute()->fetchCol();
    return array_combine($results, $results);
  }

  /**
   * Get event dates by category.
   *
   * @param string $category
   *   Event category.
   *
   * @return array
   *   Array of event dates.
   */
  public function getEventDatesByCategory($category) {
    $query = $this->database->select('event_configuration', 'ec')
      ->fields('ec', ['event_date'])
      ->distinct()
      ->condition('event_category', $category)
      ->condition('registration_start_date', date('Y-m-d'), '<=')
      ->condition('registration_end_date', date('Y-m-d'), '>=')
      ->orderBy('event_date', 'ASC');
    
    $results = $query->execute()->fetchCol();
    return array_combine($results, $results);
  }

  /**
   * Get event names by category and date.
   *
   * @param string $category
   *   Event category.
   * @param string $date
   *   Event date.
   *
   * @return array
   *   Array of event names with IDs.
   */
  public function getEventNamesByCategoryAndDate($category, $date) {
    $query = $this->database->select('event_configuration', 'ec')
      ->fields('ec', ['id', 'event_name'])
      ->condition('event_category', $category)
      ->condition('event_date', $date)
      ->condition('registration_start_date', date('Y-m-d'), '<=')
      ->condition('registration_end_date', date('Y-m-d'), '>=')
      ->orderBy('event_name', 'ASC');
    
    $results = $query->execute()->fetchAllKeyed();
    return $results;
  }

  /**
   * Save event registration.
   *
   * @param array $data
   *   Registration data.
   *
   * @return int
   *   The registration ID.
   */
  public function saveRegistration(array $data) {
    return $this->database->insert('event_registration')
      ->fields([
        'full_name' => $data['full_name'],
        'email' => $data['email'],
        'college_name' => $data['college_name'],
        'department' => $data['department'],
        'event_category' => $data['event_category'],
        'event_date' => $data['event_date'],
        'event_id' => $data['event_id'],
        'created' => \Drupal::time()->getRequestTime(),
      ])
      ->execute();
  }

  /**
   * Check if a registration already exists.
   *
   * @param string $email
   *   Email address.
   * @param string $event_date
   *   Event date.
   *
   * @return bool
   *   TRUE if registration exists, FALSE otherwise.
   */
  public function registrationExists($email, $event_date) {
    $query = $this->database->select('event_registration', 'er')
      ->fields('er', ['id'])
      ->condition('email', $email)
      ->condition('event_date', $event_date)
      ->range(0, 1);
    
    return (bool) $query->execute()->fetchField();
  }

  /**
   * Get all registrations.
   *
   * @param string|null $event_date
   *   Optional event date filter.
   * @param int|null $event_id
   *   Optional event ID filter.
   *
   * @return array
   *   Array of registrations.
   */
  public function getRegistrations($event_date = NULL, $event_id = NULL) {
    $query = $this->database->select('event_registration', 'er')
      ->fields('er', [
        'id',
        'full_name',
        'email',
        'college_name',
        'department',
        'event_category',
        'event_date',
        'event_id',
        'created',
      ]);
    
    if ($event_date) {
      $query->condition('er.event_date', $event_date);
    }
    
    if ($event_id) {
      $query->condition('er.event_id', $event_id);
    }
    
    $query->orderBy('er.created', 'DESC');
    
    return $query->execute()->fetchAll(\PDO::FETCH_ASSOC);
  }

  /**
   * Get event by ID.
   *
   * @param int $event_id
   *   Event ID.
   *
   * @return object|null
   *   Event object or NULL.
   */
  public function getEventById($event_id) {
    $query = $this->database->select('event_configuration', 'ec')
      ->fields('ec')
      ->condition('id', $event_id);
    
    return $query->execute()->fetchObject();
  }

  /**
   * Get all unique event dates for admin listing.
   *
   * @return array
   *   Array of event dates.
   */
  public function getAllEventDates() {
    $query = $this->database->select('event_configuration', 'ec')
      ->fields('ec', ['event_date'])
      ->distinct()
      ->orderBy('event_date', 'DESC');
    
    $results = $query->execute()->fetchCol();
    return array_combine($results, $results);
  }

  /**
   * Get event names by date for admin listing.
   *
   * @param string $date
   *   Event date.
   *
   * @return array
   *   Array of event names with IDs.
   */
  public function getEventNamesByDate($date) {
    $query = $this->database->select('event_configuration', 'ec')
      ->fields('ec', ['id', 'event_name'])
      ->condition('event_date', $date)
      ->orderBy('event_name', 'ASC');
    
    return $query->execute()->fetchAllKeyed();
  }

  /**
   * Get total participant count.
   *
   * @param string|null $event_date
   *   Optional event date filter.
   * @param int|null $event_id
   *   Optional event ID filter.
   *
   * @return int
   *   Total count of participants.
   */
  public function getParticipantCount($event_date = NULL, $event_id = NULL) {
    $query = $this->database->select('event_registration', 'er')
      ->fields('er', ['id']);
    
    if ($event_date) {
      $query->condition('er.event_date', $event_date);
    }
    
    if ($event_id) {
      $query->condition('er.event_id', $event_id);
    }
    
    return (int) $query->countQuery()->execute()->fetchField();
  }

}
