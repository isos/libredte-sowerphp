<h1>Emitir DTE de <?=$Emisor->razon_social?> (<?=$Emisor->getRUT()?>)</h1>
<?php
$f = new \sowerphp\general\View_Helper_Form(false);
echo $f->begin(['id'=>'emitir_dte', 'focus'=>'RUTRecepField', 'action'=>'previsualizacion', 'onsubmit'=>'DTE.check()']);
?>
    <!-- DATOS DEL DOCUMENTO -->
    <div class="row">
        <div class="form-group col-md-8"><?=$f->input(['name'=>'TpoDoc', 'type'=>'select', 'options'=> $tipos_dte, 'value'=>33, 'attr'=>'onblur="DTE.setTipo(this.value)"'])?></div>
        <div class="form-group col-md-4"><?=$f->input(['type' => 'date', 'name' => 'FchEmis', 'placeholder'=>'Fecha emisión DTE', 'popover'=>'Día en que se emite el documento', 'value'=>date('Y-m-d'), 'check' => 'notempty date'])?></div>
    </div>
    <!-- DATOS DEL EMISOR -->
    <div class="row">
        <div class="form-group col-md-3"><?=$f->input(['name'=>'GiroEmis', 'placeholder' => 'Giro del emisor', 'value'=>$Emisor->giro, 'check' => 'notempty', 'attr' => 'maxlength="80"'])?></div>
        <div class="form-group col-md-3"><?=$f->input(['type' => 'select', 'name' => 'Acteco', 'options' => $actividades_economicas, 'value'=>$Emisor->actividad_economica, 'check' => 'notempty'])?></div>
        <div class="form-group col-md-3"><?=$f->input(['name' => 'DirOrigen', 'placeholder' => 'Dirección del emisor', 'value'=>$Emisor->direccion, 'check' => 'notempty', 'attr' => 'maxlength="70"'])?></div>
        <div class="form-group col-md-3"><?=$f->input(['type' => 'select', 'name' => 'CmnaOrigen', 'value' => $Emisor->comuna, 'options' => $comunas, 'check' => 'notempty'])?></div>
    </div>
    <p>(*) modificar los datos del emisor (giro, actividad económica y/o dirección) sólo afectará a la emisión de este documento, no se guardarán estos cambios.</p>
    <!-- DATOS DEL RECEPTOR -->
    <div class="row">
        <div class="form-group col-md-3"><?=$f->input(['name' => 'RUTRecep', 'placeholder' => 'RUT del receptor', 'check' => 'notempty rut', 'attr' => 'maxlength="12" onblur="Receptor.setDatos(\'emitir_dte\')"'])?></div>
        <div class="form-group col-md-5"><?=$f->input(['name' => 'RznSocRecep', 'placeholder' => 'Razón social del receptor', 'check' => 'notempty', 'attr' => 'maxlength="100"'])?></div>
        <div class="form-group col-md-4"><?=$f->input(['name' => 'GiroRecep', 'placeholder' => 'Giro del receptor', 'check' => 'notempty', 'attr' => 'maxlength="80"'])?></div>
    </div>
    <div class="row">
        <div class="form-group col-md-3"><?=$f->input([ 'name' => 'DirRecep', 'placeholder' => 'Dirección del receptor', 'check' => 'notempty', 'attr' => 'maxlength="70"'])?></div>
        <div class="form-group col-md-3"><?=$f->input(['type' => 'select', 'name' => 'CmnaRecep', 'options' => [''=>'Comuna del receptor'] + $comunas, 'check' => 'notempty'])?></div>
        <div class="form-group col-md-3"><?=$f->input(['name' => 'Contacto', 'placeholder' => 'Teléfono del receptor (opcional)', 'check'=>'telephone', 'attr' => 'maxlength="20"'])?></div>
        <div class="form-group col-md-3"><?=$f->input(['name' => 'CorreoRecep', 'placeholder' => 'Email del receptor (opcional)', 'check'=>'email', 'attr' => 'maxlength="80"'])?></div>
    </div>
    <!-- DATOS DE TRANSPORTE EN CASO QUE SEA GUÍA DE DESPACHO -->
    <div class="row" id="datosTransporte" style="display:none">
        <div class="form-group col-md-12">
            <?php new \sowerphp\general\View_Helper_Table([
                ['Tipo traslado', 'Dirección', 'Comuna', 'Transportista', 'Patente', 'RUT chofer', 'Nombre chofer'],
                [
                    $f->input(['type'=>'select', 'name'=>'IndTraslado', 'options'=>$IndTraslado, 'attr'=>'style="width:8em"']),
                    $f->input(['name'=>'DirDest', 'attr'=>'maxlength="70"']),
                    $f->input(['type' => 'select', 'name' => 'CmnaDest', 'options' => [''=>''] + $comunas, 'attr'=>'style="width:7em"']),
                    $f->input(['name'=>'RUTTrans', 'placeholder'=>'99.999.999-9', 'check'=>'rut', 'attr'=>'style="width:8em"']),
                    $f->input(['name'=>'Patente', 'attr'=>'maxlength="6" style="width:6em"']),
                    $f->input(['name'=>'RUTChofer', 'check'=>'rut', 'attr'=>'style="width:8em"']),
                    $f->input(['name'=>'NombreChofer', 'attr'=>'maxlength="30" style="width:8em"']),
                ]
            ]); ?>
        </div>
    </div>
    <!-- DETALLE DEL DOCUMENTO -->
    <div class="row">
        <div class="form-group col-md-12">
<?=$f->input([
    'type'=>'js',
    'id'=>'detalle',
    'label'=>'Detalle',
    'titles'=>['Código', 'Item', 'Detalle', 'Exento', 'Cant.', 'Unidad', 'P. Unitario', 'Desc.', '% / $', 'Subtotal'],
    'inputs'=>[
        ['name'=>'VlrCodigo', 'attr'=>'maxlength="35" style="text-align:center;width:5em" onblur="DTE.setItem('.$Emisor->rut.', this)"'],
        ['name'=>'NmbItem', 'attr'=>'maxlength="80"'],
        ['name'=>'DscItem', 'attr'=>'maxlength="1000"'],
        ['name'=>'IndExe', 'type'=>'select', 'options'=>['no', 'si'], 'attr'=>'style="width:5em" onblur="DTE.calcular()"'],
        ['name'=>'QtyItem', 'value'=>1, 'attr'=>'maxlength="19" style="text-align:center;width:4em" onblur="DTE.calcular()"'],
        ['name'=>'UnmdItem', 'attr'=>'maxlength="4" style="width:5em"'],
        ['name'=>'PrcItem', 'attr'=>'maxlength="12" style="text-align:center;width:7em" onblur="DTE.calcular()"'],
        ['name'=>'ValorDR', 'value'=>0, 'attr'=>'maxlength="12" style="text-align:center;width:5em" onblur="DTE.calcular()"'],
        ['name'=>'TpoValor', 'type'=>'select', 'options'=>['%'=>'%','$'=>'$'], 'attr'=>'style="width:5em" onblur="DTE.calcular()"'],
        ['name'=>'subtotal', 'value'=>0, 'attr'=>'readonly="readonly" style="text-align:center;width:7em"']
    ],
    'accesskey' => 'D',
])?>
        </div>
    </div>
    <!-- REFERENCIAS DEL DOCUMENTO -->
    <div class="row">
        <div class="form-group col-md-12">
<?=$f->input([
    'type'=>'js',
    'id'=>'referencias',
    'label'=>'Referencias',
    'titles'=>['Fecha DTE ref.', 'DTE referenciado', 'Folio DTE ref.', 'Código ref.', 'Razón referencia'],
    'inputs'=>[
        ['name'=>'FchRef', 'type'=>'date', 'check'=>'date'],
        ['name'=>'TpoDocRef', 'type'=>'select', 'options'=>[''=>'Tipo de documento referenciado'] + $tipos_dte, 'attr'=>'onblur="DTE.setFechaReferencia('.$Emisor->rut.', this)"'],
        ['name'=>'FolioRef', 'check'=>'integer', 'attr'=>'maxlength="18" onblur="DTE.setFechaReferencia('.$Emisor->rut.', this)"'],
        ['name'=>'CodRef', 'type'=>'select', 'options'=>$tipos_referencia],
        ['name'=>'RazonRef', 'attr'=>'maxlength="90"'],
    ],
    'values' => [],
    'accesskey' => 'R',
])?>
        </div>
    </div>
    <!-- RESUMEN DE LOS MONTOS DEL DOCUMENTO -->
    <div class="row">
        <div class="form-group col-md-12">
            <?php new \sowerphp\general\View_Helper_Table([
                ['Desc. glogal', '% / $', 'Neto', 'Exento', 'Tasa IVA', 'IVA', 'Total'],
                [
                    $f->input(['name'=>'ValorDR_global', 'placeholder'=>'Descuento global', 'value'=>0, 'check'=>'notempty integer', 'attr'=>'maxlength="12" style="text-align:center;width:7em" onblur="DTE.calcular()"', 'popover'=>'Descuento global que se aplica a todos los items del DTE']),
                    $f->input(['name'=>'TpoValor_global', 'type'=>'select', 'options'=>['%'=>'%','$'=>'$'], 'attr'=>'style="width:5em" onblur="DTE.calcular()"']),
                    $f->input(['name'=>'neto', 'value'=>0, 'attr'=>'readonly="readonly"']),
                    $f->input(['name'=>'exento', 'value'=>0, 'attr'=>'readonly="readonly"']),
                    $f->input(['name'=>'tasa', 'label'=>'Tasa IVA', 'value'=>$tasa, 'check'=>'notempty integer', 'attr'=>'readonly="readonly"']),
                    $f->input(['name'=>'iva', 'value'=>0, 'attr'=>'readonly="readonly"']),
                    $f->input(['name'=>'total', 'value'=>0, 'attr'=>'readonly="readonly"']),
                ]
            ]); ?>
        </div>
    </div>
    <!-- BOTÓN PARA GENERAR DOCUMENTO -->
    <div class="row">
        <div class="form-group col-md-offset-4 col-md-4">
            <button type="submit" name="submit" class="btn btn-primary" style="width:100%">
                Generar documento temporal y previsualización
            </button>
        </div>
    </div>
</form>
