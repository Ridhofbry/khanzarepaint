import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Livewire scripts
import 'livewire';

// Initialize tooltips, modals, etc.
document.addEventListener('DOMContentLoaded', function () {
    console.log('Khanza Repaint app loaded');
});
