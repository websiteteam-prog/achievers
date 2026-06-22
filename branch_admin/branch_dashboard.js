document.addEventListener('DOMContentLoaded', function () {
  // Shared POST helper: sends form data, expects a JSON {success, message} reply.
  function postForm(url, data, button, onSuccess) {
    if (button) button.disabled = true;

    fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: data
    })
      .then(function (res) {
        return res.json().catch(function () {
          return { success: false, message: 'Unexpected server response.' };
        });
      })
      .then(function (json) {
        alert(json.message || (json.success ? 'Done.' : 'Something went wrong.'));
        if (json.success && typeof onSuccess === 'function') onSuccess();
      })
      .catch(function () {
        alert('Network error. Please try again.');
      })
      .finally(function () {
        if (button) button.disabled = false;
      });
  }

  // ----- Add Event -----
  var addEventBtn = document.getElementById('add_event');
  if (addEventBtn) {
    addEventBtn.addEventListener('click', function (e) {
      e.preventDefault();
      var date   = document.getElementById('event_date').value;
      var title  = document.getElementById('event_title').value;
      var agenda = document.getElementById('event_agenda').value;
      if (!date || !title || !agenda) { alert('Please fill all the details'); return; }

      var data = 'date=' + encodeURIComponent(date) +
                 '&title=' + encodeURIComponent(title) +
                 '&agenda=' + encodeURIComponent(agenda);
      postForm('insert_event.php', data, addEventBtn, function () {
        document.getElementById('event_date').value = '';
        document.getElementById('event_title').value = '';
        document.getElementById('event_agenda').value = '';
      });
    });
  }

  // ----- Schedule Meeting -----
  var addMeetingBtn = document.getElementById('add_meeting');
  if (addMeetingBtn) {
    addMeetingBtn.addEventListener('click', function (e) {
      e.preventDefault();
      var date   = document.getElementById('meeting_date').value;
      var id     = document.getElementById('student_id').value;
      var agenda = document.getElementById('agenda').value;
      if (!date || !id || !agenda) { alert('Please fill all the details'); return; }

      var data = 'date=' + encodeURIComponent(date) +
                 '&id=' + encodeURIComponent(id) +
                 '&agenda=' + encodeURIComponent(agenda);
      postForm('insert_meeting.php', data, addMeetingBtn, function () {
        document.getElementById('meeting_date').value = '';
        document.getElementById('student_id').value = '';
        document.getElementById('agenda').value = '';
      });
    });
  }

  // ----- Course Suggestion -----
  var suggestBtn = document.getElementById('course_suggest');
  if (suggestBtn) {
    suggestBtn.addEventListener('click', function (e) {
      e.preventDefault();
      var suggestion = document.getElementById('suggestion').value;
      var subject    = document.getElementById('subject').value;
      if (!suggestion || !subject) { alert('Please fill all details'); return; }

      var data = 'suggestion=' + encodeURIComponent(suggestion) +
                 '&subject=' + encodeURIComponent(subject);
      postForm('insert_suggestion.php', data, suggestBtn, function () {
        document.getElementById('suggestion').value = '';
        document.getElementById('subject').value = '';
      });
    });
  }
});
