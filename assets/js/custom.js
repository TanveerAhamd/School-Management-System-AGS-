/**
 *
 * You can write your JS code here, DO NOT touch the default style file
 * because it will make it harder for you to update.
 * 
 */

"use strict";

$(document).ready(function () {
    // Jis input field par 'upper-case' class hogi, ye usay auto-capitalize karega
    $(document).on('keyup change', '.upper-case', function () {
        this.value = this.value.toUpperCase();
    });
});
