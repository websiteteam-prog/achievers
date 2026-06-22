document.addEventListener('DOMContentLoaded', function () {
document.getElementById('add_event').addEventListener('click', function(e){
        e.preventDefault();
    
    const eventDate = document.getElementById('event_date').value;
    const eventTitle = document.getElementById('event_title').value;
    const eventAgenda = document.getElementById('event_agenda').value;
    
    if(!eventDate || !eventTitle || !eventAgenda){
        alert ("Please fill all the details");
        return;
    }
    
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "insert_event.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    
    xhr.onreadystatechange = function () {
        if(xhr.readyState === 4 && xhr.status === 200) {
            alert(xhr.responseText);
        }
    };
    
    const data = `date=${encodeURIComponent(eventDate)}&title=${encodeURIComponent(eventTitle)}&agenda=${encodeURIComponent(eventAgenda)}`;
    xhr.send(data);
    
    document.getElementById('add_event').disabled = true;

setTimeout(() => {
    document.getElementById('add_event').disabled = false;
}, 1000);
});


    document.getElementById('add_meeting').addEventListener('click', function(e) {
        e.preventDefault();
        
        console.log('clicked');
        const meetingDate = document.getElementById('meeting_date').value;
        const studentId = document.getElementById('student_id').value;
        const agenda = document.getElementById('agenda').value;
        
        if(!meetingDate || !studentId || !agenda){
            alert ("Please Fill all the details");
            return;
        }
        
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "insert_meeting.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        
        xhr.onreadystatechange = function () {
            if(xhr.readyState === 4 && xhr.status === 200) {
                alert(xhr.responseText);
            }
        };
        
        const data =`date=${encodeURIComponent(meetingDate)}&id=${encodeURIComponent(studentId)}&agenda=${encodeURIComponent(agenda)}`;
        xhr.send(data);
        

        });
        
        
    document.getElementById('course_suggest').addEventListener('click', function(e) {
        e.preventDefault();
        
        
        console.log('clicked');
        const suggestion = document.getElementById('suggestion').value;
        const subject = document.getElementById('subject').value;
        
        if(!suggestion || !subject){
            alert ("Please fill all details");
            return;
        }
        
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "insert_suggestion.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        
        xhr.onreadystatechange = function () {
            if(xhr.readyState === 4 && xhr.status === 200){
                alert(xhr.responseText);
            }
        };
        
        const data = `suggestion=${encodeURIComponent(suggestion)}&subject=${encodeURIComponent(subject)}`;
        xhr.send(data);
        
    })    
});