/**
 * assets/js/main.js
 *
 * This file contains custom JavaScript for the Project Management System.
 * It primarily handles the sidebar toggle functionality.
 */

$(document).ready(function () {
    // Sidebar toggle functionality
    // This uses jQuery, which is loaded in header.php
    $('#sidebarCollapse').on('click', function () {
        $('#sidebar').toggleClass('active');
        $('#content').toggleClass('active');
        $('.custom-menu').toggleClass('active'); // Toggle active class on the custom menu button itself for positioning
    });

    // Auto-hide alert functionality (re-used across files for consistency)
    window.setupAutoHideAlerts = function() {
        const alertElement = document.querySelector('.alert.fade.show');
        if (alertElement) {
            setTimeout(function() {
                // Check if Bootstrap's Alert instance exists, otherwise use custom fade-out
                const bootstrapAlert = bootstrap.Alert.getInstance(alertElement);
                if (bootstrapAlert) {
                    bootstrapAlert.close();
                } else {
                    alertElement.classList.add('fade-out');
                    setTimeout(() => alertElement.remove(), 500);
                }
            }, 5000); // 5 seconds
        }
    };

    // Call the function on DOMContentLoaded to apply to any alerts present on page load
    setupAutoHideAlerts();

    // Initialize tooltips for elements with data-bs-toggle="tooltip"
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })
});
