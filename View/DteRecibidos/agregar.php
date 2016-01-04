<a href="<?=$_base?>/dte/dte_recibidos/listar" title="Volver a los documentos recibidos" class="pull-right"><span class="btn btn-default">Volver a DTE recibidos</span></a>

<h1>Agregar DTE recibido</h1>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
echo $f->input([
    'name' => 'emisor',
    'label' => 'RUT emisor',
    'check' => 'notempty rut',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'dte',
    'label' => 'Documento',
    'options' => [''=>'Seleccionar tipo de DTE'] + $tipos_documentos,
    'check' => 'notempty',
]);
echo $f->input([
    'name' => 'folio',
    'label' => 'Folio',
    'check' => 'notempty integer',
]);
echo $f->input([
    'type' => 'date',
    'name' => 'fecha',
    'label' => 'Fecha documento',
    'check' => 'notempty date',
]);
echo $f->input([
    'name' => 'tasa',
    'label' => 'Tasa IVA',
    'check' => 'integer',
]);
echo $f->input([
    'name' => 'exento',
    'label' => 'Monto exento',
    'check' => 'integer',
]);
echo $f->input([
    'name' => 'neto',
    'label' => 'Neto',
    'check' => 'integer',
]);
echo $f->input([
    'name' => 'iva_uso_comun',
    'label' => 'IVA uso común',
    'check' => 'integer',
    'help' => 'Si el IVA es de uso común aquí va el factor de proporcionalidad',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'iva_no_recuperable',
    'label' => 'IVA no recuperable',
    'options' => [''=>'El IVA es recuperable'] + $iva_no_recuperables,
]);
echo $f->input([
    'type' => 'select',
    'name' => 'impuesto_adicional',
    'label' => 'Impuesto adicional',
    'options' => [''=>'Sin impuesto adicional'] + $impuesto_adicionales,
]);
echo $f->input([
    'name' => 'impuesto_adicional_tasa',
    'label' => 'Tasa Imp. adic.',
    'check' => 'integer',
    'help' => 'Tasa del impuesto adicional (obligatorio si hay impuesto adicional)'
]);
echo $f->input([
    'type' => 'select',
    'name' => 'impuesto_tipo',
    'label' => 'Tipo de impuesto',
    'options' => [1=>'IVA', 2=>'Ley 18211'],
]);
echo $f->input([
    'type' => 'checkbox',
    'name' => 'anulado',
    'label' => '¿Anulado?',
]);
echo $f->input([
    'name' => 'impuesto_sin_credito',
    'label' => 'Impuesto sin crédito',
    'check' => 'integer',
]);
echo $f->input([
    'name' => 'monto_activo_fijo',
    'label' => 'Monto activo fijo',
    'check' => 'integer',
]);
echo $f->input([
    'name' => 'monto_iva_activo_fijo',
    'label' => 'IVA activo fijo',
    'check' => 'integer',
]);
echo $f->input([
    'name' => 'iva_no_retenido',
    'label' => 'IVA no retenido',
    'check' => 'integer',
]);
echo $f->end('Agregar DTE recibido');
