let seleccion = {
    verificacion: false,
    tipoVehiculo: '',
    entradaSalida: ''
};

document.getElementById("boton-verificacion").addEventListener("click", function(event) {
    event.preventDefault();
    this.style.display = "none";
    document.getElementById("botones-extra").style.display = "block";
    document.getElementById("contenedor-regresar").style.display = "block";
    seleccion.verificacion = true;
});

document.getElementById("boton-regresar").addEventListener("click", function() {
    if (document.getElementById("checklist-form").style.display === "block") {
        document.getElementById("checklist-form").style.display = "none";
        document.getElementById("botones-entrada-salida").style.display = "block";
    } else if (document.getElementById("botones-entrada-salida").style.display === "block") {
        document.getElementById("botones-entrada-salida").style.display = "none";
        document.getElementById("botones-extra").style.display = "block";
        seleccion.entradaSalida = '';
    } else {
        document.getElementById("botones-extra").style.display = "none";
        document.getElementById("contenedor-regresar").style.display = "none";
        document.getElementById("boton-verificacion").style.display = "inline-block";
        seleccion.verificacion = false;
    }
});

document.getElementById("boton-tractos").addEventListener("click", function() {
    document.getElementById("botones-extra").style.display = "none";
    document.getElementById("desplegable").style.display = "block";
});

document.getElementById("boton-continuar").addEventListener("click", function() {
    let opcionSeleccionada = document.getElementById("seleccion-opcion").value;
    if (opcionSeleccionada !== "") {
        document.getElementById("id_unidad_input").value = opcionSeleccionada; // Guardar id_unidad en campo oculto
        showEntradaSalidaButtons();
    }
});

function showEntradaSalidaButtons() {
    document.getElementById("desplegable").style.display = "none";
    document.getElementById("botones-entrada-salida").style.display = "block";
}

function showChecklist() {
    document.getElementById("botones-entrada-salida").style.display = "none";
    document.getElementById("contenedor-regresar").style.display = "block";
    document.getElementById("checklist-form").style.display = "block";
}

document.getElementById("boton-entrada").addEventListener("click", function() {
    seleccion.entradaSalida = 'Entrada';
    showChecklist();
});

document.getElementById("boton-salida").addEventListener("click", function() {
    seleccion.entradaSalida = 'Salida';
    showChecklist();
});

document.getElementById("boton-dolly").addEventListener("click", function() {
    alert('Dolly en Mantenimiento');
});

document.getElementById("boton-cajas").addEventListener("click", function() {
    alert('Cajas en Mantenimiento.');
});