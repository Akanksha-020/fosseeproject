(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.eventRegistrationAdminListing = {
    attach: function (context, settings) {
      var selectedDate = '';
      var selectedEventId = '';

      // Event date change handler
      $('#event-date-filter', context).once('event-date-change').on('change', function() {
        selectedDate = $(this).val();
        selectedEventId = '';
        
        if (selectedDate) {
          // Fetch event names for selected date
          $.ajax({
            url: '/admin/event-registration/ajax/event-names',
            data: { date: selectedDate },
            success: function(data) {
              var options = '<option value="">- Select Event -</option>';
              $.each(data, function(id, name) {
                options += '<option value="' + id + '">' + name + '</option>';
              });
              $('#event-name-filter').html(options).prop('disabled', false);
              $('#registrations-table').html('');
              $('#participant-count').html('');
              $('#export-csv-btn').hide();
            }
          });
        } else {
          $('#event-name-filter').html('<option value="">- Select Date First -</option>').prop('disabled', true);
          $('#registrations-table').html('');
          $('#participant-count').html('');
          $('#export-csv-btn').hide();
        }
      });

      // Event name change handler
      $('#event-name-filter', context).once('event-name-change').on('change', function() {
        selectedEventId = $(this).val();
        
        if (selectedEventId) {
          loadRegistrations();
        } else {
          $('#registrations-table').html('');
          $('#participant-count').html('');
          $('#export-csv-btn').hide();
        }
      });

      // Load registrations
      function loadRegistrations() {
        $.ajax({
          url: '/admin/event-registration/ajax/registrations',
          data: { 
            event_date: selectedDate,
            event_id: selectedEventId
          },
          success: function(response) {
            displayRegistrations(response.registrations);
            displayParticipantCount(response.total_count);
            $('#export-csv-btn').show();
          }
        });
      }

      // Display registrations in table
      function displayRegistrations(registrations) {
        if (registrations.length === 0) {
          $('#registrations-table').html('<p>No registrations found.</p>');
          return;
        }

        var table = '<table class="registrations-table" style="width:100%; border-collapse: collapse;">';
        table += '<thead><tr style="background-color: #f0f0f0;">';
        table += '<th style="border: 1px solid #ddd; padding: 8px;">Name</th>';
        table += '<th style="border: 1px solid #ddd; padding: 8px;">Email</th>';
        table += '<th style="border: 1px solid #ddd; padding: 8px;">Event Date</th>';
        table += '<th style="border: 1px solid #ddd; padding: 8px;">College Name</th>';
        table += '<th style="border: 1px solid #ddd; padding: 8px;">Department</th>';
        table += '<th style="border: 1px solid #ddd; padding: 8px;">Submission Date</th>';
        table += '</tr></thead><tbody>';

        $.each(registrations, function(index, reg) {
          var submissionDate = new Date(reg.created * 1000);
          table += '<tr>';
          table += '<td style="border: 1px solid #ddd; padding: 8px;">' + reg.full_name + '</td>';
          table += '<td style="border: 1px solid #ddd; padding: 8px;">' + reg.email + '</td>';
          table += '<td style="border: 1px solid #ddd; padding: 8px;">' + reg.event_date + '</td>';
          table += '<td style="border: 1px solid #ddd; padding: 8px;">' + reg.college_name + '</td>';
          table += '<td style="border: 1px solid #ddd; padding: 8px;">' + reg.department + '</td>';
          table += '<td style="border: 1px solid #ddd; padding: 8px;">' + submissionDate.toLocaleString() + '</td>';
          table += '</tr>';
        });

        table += '</tbody></table>';
        $('#registrations-table').html(table);
      }

      // Display participant count
      function displayParticipantCount(count) {
        $('#participant-count').html('<h3>Total Participants: ' + count + '</h3>');
      }

      // Export CSV button handler
      $('#export-csv-btn', context).once('export-csv').on('click', function() {
        var url = '/admin/event-registration/export?event_date=' + selectedDate;
        if (selectedEventId) {
          url += '&event_id=' + selectedEventId;
        }
        window.location.href = url;
      });
    }
  };

})(jQuery, Drupal);
