function pago_actualizar() {
    var abono, pagado, pendiente, monto_original, pagado_original;
    abono = document.getElementById("abonoField");
    if (!__.empty(abono)) {
        pagado = document.getElementById("pagadoField");
        pendiente = document.getElementById("pendienteField");
        monto_original = document.getElementById("monto_originalField");
        pagado_original = document.getElementById("pagado_originalField");
        pagado.value = parseInt(pagado_original.value) + parseInt(abono.value);
        pendiente.value = parseInt(monto_original.value) - parseInt(pagado.value);
    }
}

function pago_check() {
    var pendiente = document.getElementById("pendienteField");
    if (!Form.check())
        return false;
    pago_actualizar();
    if (parseInt(pendiente.value) < 0) {
        alert('No puede pagar más del monto del pago programado');
        return false;
    }
    return Form.checkSend('¿Desea registrar el pago?');
}
