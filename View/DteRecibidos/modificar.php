<a href="<?=$_base?>/dte/dte_recibidos" title="Volver a los documentos recibidos" class="pull-right"><span class="btn btn-default">Volver a DTE recibidos</span></a>

<h1>Editar documento <?=$DteRecibido->getTipo()->tipo?> N° <?=$DteRecibido->folio?></h1>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
echo $f->input([
    'name' => 'emisor',
    'label' => 'RUT emisor',
    'value' => \sowerphp\app\Utility_Rut::addDV($DteRecibido->emisor),
    'check' => 'notempty rut',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'dte',
    'label' => 'Documento',
    'options' => [''=>'Seleccionar tipo de DTE'] + $tipos_documentos,
    'value' => $DteRecibido->dte,
    'check' => 'notempty',
]);
echo $f->input([
    'name' => 'folio',
    'label' => 'Folio',
    'value' => $DteRecibido->folio,
    'check' => 'notempty integer',
]);
echo $f->input([
    'name' => 'tasa',
    'label' => 'Tasa IVA',
    'value' => $DteRecibido->tasa,
    'check' => 'notempty integer',
]);
echo $f->input([
    'type' => 'date',
    'name' => 'fecha',
    'label' => 'Fecha documento',
    'value' => $DteRecibido->fecha,
    'check' => 'notempty date',
]);
echo $f->input([
    'name' => 'exento',
    'label' => 'Monto exento',
    'value' => $DteRecibido->exento,
    'check' => 'integer',
]);
echo $f->input([
    'name' => 'neto',
    'label' => 'Neto',
    'value' => $DteRecibido->neto,
    'check' => 'integer',
]);
echo $f->input([
    'name' => 'iva_uso_comun',
    'label' => 'IVA uso común',
    'check' => 'integer',
    'value' => $DteRecibido->iva_uso_comun,
    'help' => 'Si el IVA es de uso común aquí va el factor de proporcionalidad',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'iva_no_recuperable',
    'label' => 'IVA no recuperable',
    'options' => [''=>'El IVA es recuperable'] + $iva_no_recuperables,
    'value' => $DteRecibido->iva_no_recuperable,
]);
echo $f->input([
    'type' => 'select',
    'name' => 'impuesto_adicional',
    'label' => 'Impuesto adicional',
    'options' => [''=>'Sin impuesto adicional'] + $impuesto_adicionales,
    'value' => $DteRecibido->impuesto_adicional,
]);
echo $f->input([
    'name' => 'impuesto_adicional_tasa',
    'label' => 'Tasa Imp. adic.',
    'value' => $DteRecibido->impuesto_adicional_tasa,
    'check' => 'integer',
    'help' => 'Tasa del impuesto adicional (obligatorio si hay impuesto adicional)'
]);
echo $f->end('Editar DTE recibido');
