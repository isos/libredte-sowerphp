function Contribuyente() {
    'use strict';
    return;
}

Contribuyente.setDatos = function (form) {
    var f = document.getElementById(form);
    // resetear campos
    f.razon_social.value  = "";
    f.giro.value  = "";
    f.actividad_economica.value  = "";
    f.direccion.value  = "";
    f.comuna.value  = "";
    f.telefono.value  = "";
    f.email.value  = "";
    f.sucursal_sii.value  = "";
    f.resolucion_fecha.value  = "";
    f.resolucion_numero.value  = "";
    // si no se indicó el rut no se hace nada más
    if (__.empty(f.rut.value))
        return;
    // verificar validez del rut
    if (Form.check_rut(f.rut) !== true) {
        alert('RUT contribuyente es incorrecto');
        return;
    }
    // buscar datos del rut en el servicio web y asignarlos si existen
    var dv = f.rut.value.charAt(f.rut.value.length - 1),
        rut = f.rut.value.replace(/\./g, "").replace("-", "");
    rut = rut.substr(0, rut.length - 1);
    $.ajax({
        type: "GET",
        url: _url+'/api/dte/contribuyentes/info/'+rut,
        dataType: "json",
        success: function (contribuyente) {
            f.razon_social.value  = contribuyente.razon_social;
            f.giro.value  = contribuyente.giro;
            f.actividad_economica.value  = contribuyente.actividad_economica;
            f.direccion.value  = contribuyente.direccion;
            f.comuna.value  = contribuyente.comuna;
            f.telefono.value  = contribuyente.telefono;
            f.email.value  = contribuyente.email;
            f.sucursal_sii.value  = contribuyente.sucursal_sii;
            f.resolucion_fecha.value  = contribuyente.resolucion_fecha;
            f.resolucion_numero.value  = contribuyente.resolucion_numero;
        },
        error: function (jqXHR) {
            console.log(jqXHR.responseJSON);
        }
    });
}

function Emisor() {
    'use strict';
    return;
}

Emisor.setDatos = function (form) {
    var f = document.getElementById(form);
    // resetear campos
    f.RznSoc.value  = "";
    f.GiroEmis.value  = "";
    f.Acteco.value  = "";
    f.DirOrigen.value  = "";
    f.CmnaOrigen.value  = "";
    f.Telefono.value  = "";
    f.CorreoEmisor.value  = "";
    f.CdgSIISucur.value  = "";
    f.FchResol.value  = "";
    f.NroResol.value  = "";
    // si no se indicó el rut no se hace nada más
    if (__.empty(f.RUTEmisor.value))
        return;
    // verificar validez del rut
    if (Form.check_rut(f.RUTEmisor) !== true) {
        alert('RUT emisor es incorrecto');
        return;
    }
    // buscar datos del rut en el servicio web y asignarlos si existen
    var dv = f.RUTEmisor.value.charAt(f.RUTEmisor.value.length - 1),
        rut = f.RUTEmisor.value.replace(/\./g, "").replace("-", "");
    rut = rut.substr(0, rut.length - 1);
    $.ajax({
        type: "GET",
        url: _url+'/api/dte/contribuyentes/info/'+rut,
        dataType: "json",
        success: function (contribuyente) {
            f.RznSoc.value  = contribuyente.razon_social;
            f.GiroEmis.value  = contribuyente.giro;
            f.Acteco.value  = contribuyente.actividad_economica;
            f.DirOrigen.value  = contribuyente.direccion;
            f.CmnaOrigen.value  = contribuyente.comuna;
            f.Telefono.value  = contribuyente.telefono;
            f.CorreoEmisor.value  = contribuyente.email;
            f.CdgSIISucur.value  = contribuyente.sucursal_sii;
            f.FchResol.value  = contribuyente.resolucion_fecha;
            f.NroResol.value  = contribuyente.resolucion_numero;
        },
        error: function (jqXHR) {
            console.log(jqXHR.responseJSON);
        }
    });
}

function Receptor() {
    'use strict';
    return;
}

Receptor.setDatos = function (form) {
    var f = document.getElementById(form);
    // resetear campos
    f.RznSocRecep.value  = "";
    f.GiroRecep.value  = "";
    f.DirRecep.value  = "";
    f.CmnaRecep.value  = "";
    f.Contacto.value  = "";
    f.CorreoRecep.value  = "";
    // si no se indicó el rut no se hace nada más
    if (__.empty(f.RUTRecep.value))
        return;
    // verificar validez del rut
    if (Form.check_rut(f.RUTRecep) !== true) {
        alert('RUT receptor es incorrecto');
        return;
    }
    // buscar datos del rut en el servicio web y asignarlos si existen
    var dv = f.RUTRecep.value.charAt(f.RUTRecep.value.length - 1),
        rut = f.RUTRecep.value.replace(/\./g, "").replace("-", "");
    rut = rut.substr(0, rut.length - 1);
    $.ajax({
        type: "GET",
        url: _url+'/api/dte/contribuyentes/info/'+rut,
        dataType: "json",
        success: function (contribuyente) {
            f.RznSocRecep.value  = contribuyente.razon_social;
            f.GiroRecep.value  = contribuyente.giro;
            f.DirRecep.value  = contribuyente.direccion;
            f.CmnaRecep.value  = contribuyente.comuna;
            f.Contacto.value  = contribuyente.telefono;
            f.CorreoRecep.value  = contribuyente.email;
        },
        error: function (jqXHR) {
            console.log(jqXHR.responseJSON);
        }
    });
}

function DTE() {
    'use strict';
    return;
}

DTE.setTipo = function (tipo) {
    // habilitar o ocultar datos para guía de despacho
    if (tipo==52) {
        $('#datosTransporte').show();
    } else {
        $('#datosTransporte').hide();
    }
}

DTE.setItem = function (contribuyente, codigo) {
    var f = document.getElementById("emitir_dte");
    var cols = codigo.parentNode.parentNode.parentNode.childNodes;
    if (codigo.value) {
        $.ajax({
            type: "GET",
            url: _url+'/api/dte/items/info/'+codigo.value+'/'+contribuyente,
            dataType: "json",
            success: function (item) {
                // asignar valores del item
                cols[0].childNodes[0].childNodes[0].value = item.VlrCodigo;
                cols[1].childNodes[0].childNodes[0].value = item.NmbItem;
                cols[2].childNodes[0].childNodes[0].value = item.DscItem;
                cols[3].childNodes[0].childNodes[0].value = item.IndExe;
                cols[5].childNodes[0].childNodes[0].value = item.UnmdItem;
                cols[6].childNodes[0].childNodes[0].value = item.PrcItem;
                cols[7].childNodes[0].childNodes[0].value = item.ValorDR;
                cols[8].childNodes[0].childNodes[0].value = item.TpoValor;
                // foco en cantidad
                cols[4].childNodes[0].childNodes[0].focus();
                cols[4].childNodes[0].childNodes[0].select();
                // calcular valores del dte
                DTE.calcular();
            },
            error: function (jqXHR) {
                console.log(jqXHR.responseJSON);
            }
        });
    }
}

DTE.setFechaReferencia = function (contribuyente, field) {
    var cols = field.parentNode.parentNode.parentNode.childNodes;
    var dte = cols[1].childNodes[0].childNodes[0].value;
    var folio = cols[2].childNodes[0].childNodes[0].value;
    if (!__.empty(dte) && !__.empty(folio)) {
        $.ajax({
            type: "GET",
            url: _url+'/api/dte/dte_emitidos/info/'+dte+'/'+folio+'/'+contribuyente,
            dataType: "json",
            success: function (dte) {
                cols[0].childNodes[0].childNodes[0].value = dte.fecha;
            },
            error: function (jqXHR) {
                console.log(jqXHR.responseJSON);
                cols[0].childNodes[0].childNodes[0].value = "";
            }
        });
    }
}

DTE.calcular = function () {
    var neto = 0, exento = 0, descuento = 0;
    // realizar cálculo de detalles
    $('input[name="QtyItem[]"]').each(function (i, e) {
        if (!__.empty($(e).val()) && !__.empty($('input[name="PrcItem[]"]').get(i).value)) {
            // calcular subtotal sin aplicar descuento
            $('input[name="subtotal[]"]').get(i).value = parseFloat($('input[name="QtyItem[]"]').get(i).value) * parseInt($('input[name="PrcItem[]"]').get(i).value);
            // agregar descuento si aplica
            if (!__.empty($('input[name="ValorDR[]"]').get(i).value) && $('input[name="ValorDR[]"]').get(i).value!=0) {
                if ($('select[name="TpoValor[]"]').get(i).selectedOptions[0].value=="%")
                    descuento = Math.round($('input[name="subtotal[]"]').get(i).value * (parseInt($('input[name="ValorDR[]"]').get(i).value)/100.0));
                else
                    descuento = parseInt($('input[name="ValorDR[]"]').get(i).value);
                $('input[name="subtotal[]"]').get(i).value -= descuento;
            }
            if (parseInt($('select[name="IndExe[]"]').get(i).selectedOptions[0].value)===1)
                exento += parseInt($('input[name="subtotal[]"]').get(i).value);
            else
                neto += parseInt($('input[name="subtotal[]"]').get(i).value);
        }
    });
    // calcular descuento global para neto
    if ($('select[name="TpoValor_global"]').get(0).selectedOptions[0].value=="%")
        descuento = Math.round(neto * (parseInt($('input[name="ValorDR_global"]').get(0).value)/100.0));
    else
        descuento = parseInt($('input[name="ValorDR_global"]').get(0).value);
    neto -= descuento;
    if (neto<0)
        neto = 0;
    // calcular descuento global para exento
    if ($('select[name="TpoValor_global"]').get(0).selectedOptions[0].value=="%")
        descuento = Math.round(exento * (parseInt($('input[name="ValorDR_global"]').get(0).value)/100.0));
    else
        descuento = parseInt($('input[name="ValorDR_global"]').get(0).value);
    exento -= descuento;
    if (exento<0)
        exento = 0;
    // asignar neto y exento
    $('input[name="neto"]').val(neto);
    $('input[name="exento"]').val(exento)
    // asignar IVA y monto total
    $('input[name="iva"]').val(Math.round(neto*(parseInt($('input[name="tasa"]').val())/100)));
    $('input[name="total"]').val(neto + exento + parseInt($('input[name="iva"]').val()));
}

DTE.check = function () {
    var status = true, TpoDoc = parseInt(document.getElementById("TpoDocField").value);
    var dte_check_detalle = [33, 34];
    var n_itemAfecto = 0, n_itemExento = 0;
    // revisión general formulario
    if (!Form.check())
        return false;
    // revisión detalle del dte
    $('input[name="QtyItem[]"]').each(function (i, e) {
        if (__.empty($('input[name="NmbItem[]"]').get(i).value)) {
            alert ('En la línea '+(i+1)+', item no puede estar en blanco');
            $('input[name="NmbItem[]"]').get(i).focus();
            status = false;
            return false;
        }
        if (dte_check_detalle.indexOf(TpoDoc)!=-1) {
            if (__.empty($(e).val())) {
                alert ('En la línea '+(i+1)+', cantidad no puede estar en blanco');
                $(e).focus();
                status = false;
                return false;
            }
            if (__.empty($('input[name="PrcItem[]"]').get(i).value)) {
                alert ('En la línea '+(i+1)+', precio no puede estar en blanco');
                $('input[name="PrcItem[]"]').get(i).focus();
                status = false;
                return false;
            }
            if (__.empty($('input[name="ValorDR[]"]').get(i).value)) {
                alert ('En la línea '+(i+1)+', descuento no puede estar en blanco');
                $('input[name="ValorDR[]"]').get(i).focus();
                status = false;
                return false;
            }
        }
        // si el documento es 34 forzar que todos los detalles sean exentos
        if (TpoDoc==34) {
            $('select[name="IndExe[]"]').get(i).value = 1;
        }
        // contabilizar items afectos
        if (!parseInt($('select[name="IndExe[]"]').get(i).value))
            n_itemAfecto++;
        else
            n_itemExento++;
    });
    if (!status)
        return false;
    // si no hay afecto pero si exento y el documento es 33 se cambia a 34
    if (!n_itemAfecto && n_itemExento && TpoDoc==33) {
        document.getElementById("TpoDocField").value = 34;
    }
    // revisión referencia del dte
    $('select[name="CodRef[]"]').each(function (i, e) {
        if (__.empty($('select[name="TpoDocRef[]"]').get(i).value)) {
            alert ('En la línea '+(i+1)+' de referencia:'+"\n"+'Tipo de documento referenciado no puede estar en blanco');
            $('select[name="TpoDocRef[]"]').get(i).focus();
            status = false;
            return false;
        }
        if (__.empty($('input[name="FolioRef[]"]').get(i).value)) {
            alert ('En la línea '+(i+1)+' de referencia:'+"\n"+'Folio no puede estar en blanco');
            $('input[name="FolioRef[]"]').get(i).focus();
            status = false;
            return false;
        }
        if (__.empty($('input[name="FchRef[]"]').get(i).value)) {
            alert ('En la línea '+(i+1)+' de referencia:'+"\n"+'Fecha no puede estar en blanco');
            $('input[name="FchRef[]"]').get(i).focus();
            status = false;
            return false;
        }
        if (__.empty($('input[name="RazonRef[]"]').get(i).value)) {
            alert ('En la línea '+(i+1)+' de referencia:'+"\n"+'Razón de la referencia no puede estar en blanco');
            $('input[name="RazonRef[]"]').get(i).focus();
            status = false;
            return false;
        }
    });
    if (!status)
        return false;
    // pedir confirmación de generación de factura
    DTE.calcular();
    return Form.checkSend('Confirmar '+document.getElementById("TpoDocField").selectedOptions[0].textContent+' por $'+$('input[name="total"]').val()+' a '+$('input[name="RUTRecep"]').val());
}
