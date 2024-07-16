document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const correoInput = document.querySelector('input[name="correo"]');
    const savedCorreo = localStorage.getItem('savedCorreo');
    if (savedCorreo) {
        correoInput.value = savedCorreo;
    }
    if (urlParams.has('error')) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Correo o contraseña incorrectos',
            confirmButtonText: 'Intentar de nuevo'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = window.location.pathname;
            }
        });
    }
    correoInput.addEventListener('input', function() {
        localStorage.setItem('savedCorreo', correoInput.value);
    });
});
