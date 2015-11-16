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
    'name' => 'tasa',
    'label' => 'Tasa IVA',
    'check' => 'notempty integer',
]);
echo $f->input([
    'type' => 'date',
    'name' => 'fecha',
    'label' => 'Fecha documento',
    'check' => 'notempty date',
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
echo $f->end('Agregar DTE recibido');
