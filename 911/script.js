// script.js
document.getElementById('incidentForm').addEventListener('submit', function(event) {
    event.preventDefault();

    const formData = new FormData(event.target);

    fetch('submit_incident.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        alert(data);
        document.getElementById('incidentForm').reset(); // Reset form after submission
    })
    .catch(error => console.error('Error:', error));
});
