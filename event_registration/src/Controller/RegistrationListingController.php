<?php

namespace Drupal\event_registration\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\event_registration\Service\EventRegistrationDatabaseService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for registration listing and export.
 */
class RegistrationListingController extends ControllerBase {

  /**
   * The database service.
   *
   * @var \Drupal\event_registration\Service\EventRegistrationDatabaseService
   */
  protected $databaseService;

  /**
   * Constructs a new RegistrationListingController object.
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
   * Display registrations listing page.
   *
   * @return array
   *   Render array.
   */
  public function listRegistrations() {
    $event_dates = $this->databaseService->getAllEventDates();

    $build = [];

    $build['filters'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['registration-filters']],
    ];

    $build['filters']['event_date'] = [
      '#type' => 'select',
      '#title' => $this->t('Event Date'),
      '#options' => ['' => $this->t('- Select Date -')] + $event_dates,
      '#attributes' => ['id' => 'event-date-filter'],
    ];

    $build['filters']['event_name'] = [
      '#type' => 'select',
      '#title' => $this->t('Event Name'),
      '#options' => ['' => $this->t('- Select Date First -')],
      '#attributes' => ['id' => 'event-name-filter'],
      '#disabled' => TRUE,
    ];

    $build['filters']['export'] = [
      '#type' => 'markup',
      '#markup' => '<button id="export-csv-btn" class="button" style="display:none;">' . $this->t('Export as CSV') . '</button>',
    ];

    $build['participant_count'] = [
      '#type' => 'markup',
      '#markup' => '<div id="participant-count"></div>',
    ];

    $build['registrations_table'] = [
      '#type' => 'markup',
      '#markup' => '<div id="registrations-table"></div>',
    ];

    $build['#attached']['library'][] = 'event_registration/admin_listing';

    return $build;
  }

  /**
   * Export registrations as CSV.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   CSV file response.
   */
  public function exportCsv(Request $request) {
    $event_date = $request->query->get('event_date');
    $event_id = $request->query->get('event_id');

    $registrations = $this->databaseService->getRegistrations($event_date, $event_id);

    // Create CSV content.
    $csv_data = [];
    $csv_data[] = [
      'ID',
      'Full Name',
      'Email',
      'College Name',
      'Department',
      'Event Category',
      'Event Date',
      'Submission Date',
    ];

    foreach ($registrations as $registration) {
      $event = $this->databaseService->getEventById($registration['event_id']);
      $event_name = $event ? $event->event_name : '';

      $csv_data[] = [
        $registration['id'],
        $registration['full_name'],
        $registration['email'],
        $registration['college_name'],
        $registration['department'],
        $registration['event_category'],
        $registration['event_date'],
        date('Y-m-d H:i:s', $registration['created']),
      ];
    }

    // Generate CSV string.
    $csv_string = '';
    foreach ($csv_data as $row) {
      $csv_string .= implode(',', array_map(function($field) {
        return '"' . str_replace('"', '""', $field) . '"';
      }, $row)) . "\n";
    }

    // Create response.
    $response = new Response($csv_string);
    $response->headers->set('Content-Type', 'text/csv');
    $response->headers->set('Content-Disposition', 'attachment; filename="event_registrations_' . date('Y-m-d_H-i-s') . '.csv"');

    return $response;
  }

}
