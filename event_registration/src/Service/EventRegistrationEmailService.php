<?php

namespace Drupal\event_registration\Service;

use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Service for sending emails related to event registration.
 */
class EventRegistrationEmailService {

  /**
   * The mail manager.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected $mailManager;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new EventRegistrationEmailService object.
   *
   * @param \Drupal\Core\Mail\MailManagerInterface $mail_manager
   *   The mail manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(MailManagerInterface $mail_manager, ConfigFactoryInterface $config_factory) {
    $this->mailManager = $mail_manager;
    $this->configFactory = $config_factory;
  }

  /**
   * Send registration confirmation emails.
   *
   * @param array $registration_data
   *   Registration data array.
   * @param string $event_name
   *   Event name.
   */
  public function sendConfirmationEmail(array $registration_data, $event_name) {
    $config = $this->configFactory->get('event_registration.settings');
    
    // Prepare email body.
    $body = $this->buildEmailBody($registration_data, $event_name);
    $subject = 'Event Registration Confirmation - ' . $event_name;
    
    // Send email to user.
    $this->sendEmail(
      $registration_data['email'],
      $subject,
      $body
    );
    
    // Send email to admin if enabled.
    if ($config->get('enable_admin_notifications')) {
      $admin_email = $config->get('admin_notification_email');
      if ($admin_email) {
        $admin_subject = 'New Event Registration - ' . $event_name;
        $this->sendEmail(
          $admin_email,
          $admin_subject,
          $body
        );
      }
    }
  }

  /**
   * Build email body.
   *
   * @param array $registration_data
   *   Registration data.
   * @param string $event_name
   *   Event name.
   *
   * @return string
   *   Email body content.
   */
  protected function buildEmailBody(array $registration_data, $event_name) {
    $body = "Dear {$registration_data['full_name']},\n\n";
    $body .= "Thank you for registering for the event. Below are your registration details:\n\n";
    $body .= "Name: {$registration_data['full_name']}\n";
    $body .= "Event Name: {$event_name}\n";
    $body .= "Event Date: {$registration_data['event_date']}\n";
    $body .= "Category: {$registration_data['event_category']}\n";
    $body .= "College: {$registration_data['college_name']}\n";
    $body .= "Department: {$registration_data['department']}\n\n";
    $body .= "We look forward to seeing you at the event!\n\n";
    $body .= "Best regards,\n";
    $body .= "Event Management Team";
    
    return $body;
  }

  /**
   * Send an email.
   *
   * @param string $to
   *   Recipient email address.
   * @param string $subject
   *   Email subject.
   * @param string $body
   *   Email body.
   */
  protected function sendEmail($to, $subject, $body) {
    $params = [
      'subject' => $subject,
      'body' => $body,
    ];
    
    $langcode = \Drupal::languageManager()->getDefaultLanguage()->getId();
    
    $this->mailManager->mail(
      'event_registration',
      'registration_confirmation',
      $to,
      $langcode,
      $params,
      NULL,
      TRUE
    );
  }

}
