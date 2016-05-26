<ul class="nav nav-pills pull-right">
    <li>
        <a href="<?=$_base?>/dte/dte_recibidos/listar" title="Volver a los documentos recibidos">
            Volver a documentos recibidos
        </a>
    </li>
</ul>

<h1><?=$DteRecibido->getEmisor()->razon_social?> <small><?=$DteRecibido->getTipo()->tipo?> N° <?=$DteRecibido->folio?></small></h1>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
echo $f->input([
    'name' => 'emisor',
    'label' => 'RUT emisor',
    'value' => \sowerphp\app\Utility_Rut::addDV($DteRecibido->emisor),
    'check' => 'notempty rut',
    'attr' => $DteRecibido->intercambio ? 'readonly="readonly"' : '',
]);
if (!$DteRecibido->intercambio) {
    echo $f->input([
        'type' => 'select',
        'name' => 'dte',
        'label' => 'Documento',
        'options' => [''=>'Seleccionar tipo de documento'] + $tipos_documentos,
        'value' => $DteRecibido->dte,
        'check' => 'notempty',
    ]);
} else {
    echo $f->input([
        'name' => 'dte',
        'label' => 'Documento',
        'value' => $DteRecibido->dte,
        'check' => 'notempty',
        'attr' => $DteRecibido->intercambio ? 'readonly="readonly"' : '',
    ]);
}
echo $f->input([
    'name' => 'folio',
    'label' => 'Folio',
    'value' => $DteRecibido->folio,
    'check' => 'notempty integer',
    'attr' => $DteRecibido->intercambio ? 'readonly="readonly"' : '',
]);
echo $f->input([
    'type' => $DteRecibido->intercambio ? 'text' : 'date',
    'name' => 'fecha',
    'label' => 'Fecha documento',
    'value' => $DteRecibido->fecha,
    'check' => 'notempty date',
    'attr' => $DteRecibido->intercambio ? 'readonly="readonly"' : '',
]);
echo $f->input([
    'name' => 'tasa',
    'label' => 'Tasa IVA',
    'value' => $DteRecibido->tasa,
    'check' => 'integer',
    'attr' => $DteRecibido->intercambio ? 'readonly="readonly"' : '',
]);
echo $f->input([
    'name' => 'exento',
    'label' => 'Monto exento',
    'value' => $DteRecibido->exento,
    'check' => 'integer',
    'attr' => $DteRecibido->intercambio ? 'readonly="readonly"' : '',
]);
echo $f->input([
    'name' => 'neto',
    'label' => 'Neto',
    'value' => $DteRecibido->neto,
    'check' => 'integer',
    'attr' => $DteRecibido->intercambio ? 'readonly="readonly"' : '',
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
/*echo $f->input([
    'name' => 'periodo',
    'label' => 'Período',
    'value' => $DteRecibido->periodo,
    'check' => 'integer',
    'help' => 'Período en el que registrar el documento (si es diferente al mes de la fecha de emisión). Formato: AAAAMM',
]);*/
echo $f->end('Guardar cambios al documento');
