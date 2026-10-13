// resources/js/admin.js
import $ from 'jquery';
// Import Toastr and its CSS
import toastr from 'toastr';
import 'toastr/build/toastr.min.css';
// Make jQuery globally available
window.$ = window.jQuery = $;

// Optional: Test that it works
$(document).ready(function() {
    console.log("jQuery is loaded and ready in Admin!");
});

// Make toastr globally available
window.toastr = toastr;

// Optional: Configure default toastr options
toastr.options = {
    "closeButton": true,
    "progressBar": true,
    "positionClass": "toast-top-right",
    "timeOut": "3500",
};
