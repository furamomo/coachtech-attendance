document.addEventListener('DOMContentLoaded', function () {
    const currentTimeElement = document.getElementById('currentTime');

    if (!currentTimeElement) {
        return;
    }

    function updateCurrentTime() {
        const now = new Date();

        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');

        currentTimeElement.textContent = hours + ':' + minutes;
    }

    updateCurrentTime();
    setInterval(updateCurrentTime, 1000);
});