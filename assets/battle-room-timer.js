(function () {
    'use strict';
    function refreshTimer() {
        var timer = document.getElementById('battle-timer');
        if (!timer) return;
        var stateUrl = location.pathname.replace('/battle/room/', '/battle/state/');
        fetch(stateUrl, {headers: {'X-Requested-With': 'XMLHttpRequest'}})
            .then(function (response) { return response.json(); })
            .then(function (payload) {
                if (!payload.ok) return;
                var seconds = Math.max(0, Number(payload.state.remaining) || 0);
                timer.textContent = String(Math.floor(seconds / 60)).padStart(2, '0') + ':' + String(seconds % 60).padStart(2, '0');
                timer.parentElement.classList.toggle('bg-danger', seconds <= 30 && payload.state.status === 'playing');
            })
            .catch(function () {});
    }
    document.addEventListener('DOMContentLoaded', refreshTimer);
    window.setInterval(refreshTimer, 1000);
})();
