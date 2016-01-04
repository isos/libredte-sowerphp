<a href="<?=$_base?>/dte/dte_recibidos/listar" title="Volver a los documentos recibidos" class="pull-right"><span class="btn btn-default">Volver a DTE recibidos</span></a>

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
    'type' => 'date',
    'name' => 'fecha',
    'label' => 'Fecha documento',
    'value' => $DteRecibido->fecha,
    'check' => 'notempty date',
]);
echo $f->input([
    'name' => 'tasa',
    'label' => 'Tasa IVA',
    'value' => $DteRecibido->tasa,
    'check' => 'integer',
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
echo $f->input([
    'type' => 'select',
    'name' => 'impuesto_tipo',
    'label' => 'Tipo de impuesto',
    'value' => $DteRecibido->impuesto_tipo,
    'options' => [1=>'IVA', 2=>'Ley 18211'],
]);
echo $f->input([
    'type' => 'checkbox',
    'name' => 'anulado',
    'checked' => $DteRecibido->anulado == 'A' ? true : false,
    'label' => '¿Anulado?',
]);
echo $f->input([
    'name' => 'impuesto_sin_credito',
    'label' => 'Impuesto sin crédito',
    'value' => $DteRecibido->impuesto_sin_credito,
    'check' => 'integer',
]);
echo $f->input([
    'name' => 'monto_activo_fijo',
    'label' => 'Monto activo fijo',
    'value' => $DteRecibido->monto_activo_fijo,
    'check' => 'integer',
]);
echo $f->input([
    'name' => 'monto_iva_activo_fijo',
    'label' => 'IVA activo fijo',
    'value' => $DteRecibido->monto_iva_activo_fijo,
    'check' => 'integer',
]);
echo $f->input([
    'name' => 'iva_no_retenido',
    'label' => 'IVA no retenido',
    'value' => $DteRecibido->iva_no_retenido,
    'check' => 'integer',
]);
echo $f->end('Editar DTE recibido');
