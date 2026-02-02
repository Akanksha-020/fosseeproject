<?php

namespace Drupal\event_registration\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\event_registration\Service\EventRegistrationDatabaseService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for AJAX requests.
 */
class AjaxController extends ControllerBase {

  /**
   * The database service.
   *
   * @var \Drupal\event_registration\Service\EventRegistrationDatabaseService
   */
  protected $databaseService;

  /**
   * Constructs a new AjaxController object.
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
   * Get event dates by category.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JSON response with event dates.
   */
  public function getEventDates(Request $request) {
    $category = $request->query->get('category');
    
    if (!$category) {
      return new JsonResponse(['error' => 'Category is required'], 400);
    }

    $dates = $this->databaseService->getEventDatesByCategory($category);
    
    return new JsonResponse($dates);
  }

  /**
   * Get event names by category and date.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JSON response with event names.
   */
  public function getEventNames(Request $request) {
    $category = $request->query->get('category');
    $date = $request->query->get('date');
    
    if (!$category || !$date) {
      return new JsonResponse(['error' => 'Category and date are required'], 400);
    }

    $events = $this->databaseService->getEventNamesByCategoryAndDate($category, $date);
    
    return new JsonResponse($events);
  }

  /**
   * Get event names by date for admin listing.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JSON response with event names.
   */
  public function getAdminEventNames(Request $request) {
    $date = $request->query->get('date');
    
    if (!$date) {
      return new JsonResponse(['error' => 'Date is required'], 400);
    }

    $events = $this->databaseService->getEventNamesByDate($date);
    
    return new JsonResponse($events);
  }

  /**
   * Get registrations by filters.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JSON response with registrations.
   */
  public function getRegistrations(Request $request) {
    $event_date = $request->query->get('event_date');
    $event_id = $request->query->get('event_id');
    
    $registrations = $this->databaseService->getRegistrations($event_date, $event_id);
    $count = $this->databaseService->getParticipantCount($event_date, $event_id);
    
    return new JsonResponse([
      'registrations' => $registrations,
      'total_count' => $count,
    ]);
  }

}
