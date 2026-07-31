$(document).ready(function () {
    $(window).scrollTop();
    $("body").fadeIn(1000);
});

function dismissError() {
    const toast = document.getElementById('error-toast');
         if (toast) {
            toast.classList.add('hide');
            setTimeout(() => toast.remove(), 300);
        }
    }