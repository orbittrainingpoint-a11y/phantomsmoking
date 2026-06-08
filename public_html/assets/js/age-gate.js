// age-gate.js
document.addEventListener('DOMContentLoaded', () => {
    const dobField = document.getElementById('dobField');
    const requireDob = document.body.dataset.requireDob === '1';
    if (requireDob && dobField) dobField.style.display = 'block';
});
