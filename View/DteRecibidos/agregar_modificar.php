<ul class="nav nav-pills pull-right">
    <li>
        <a href="<?=$_base?>/dte/dte_recibidos/listar" title="Volver a los documentos recibidos">
            Volver a documentos recibidos
        </a>
    </li>
</ul>

<?php if (isset($DteRecibido)) : ?>
<h1><?=$DteRecibido->getTipo()->tipo?> N° <?=$DteRecibido->folio?> <small><?=$DteRecibido->getEmisor()->razon_social?></small></h1>
<?php else : ?>
<h1>Agregar documento recibido</h1>
<?php
endif;
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
$f->setColsLabel(5);
echo '<div class="row">',"\n";
echo '<div class="col-md-6">',"\n";
echo $f->input([
    'name' => 'emisor',
    'label' => 'RUT emisor',
    'value' => isset($DteRecibido) ? \sowerphp\app\Utility_Rut::addDV($DteRecibido->emisor) : '',
    'placeholder' => '55.666.777-8',
    'check' => 'notempty rut',
    'attr' => (isset($DteRecibido) and $DteRecibido->intercambio) ? 'readonly="readonly"' : '',
]);
if (!isset($DteRecibido) or !$DteRecibido->intercambio) {
    echo $f->input([
        'type' => 'select',
        'name' => 'dte',
        'label' => 'Documento',
        'options' => [''=>'Seleccionar tipo de documento'] + $tipos_documentos,
        'value' => isset($DteRecibido) ? $DteRecibido->dte : '',
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
    'value' => isset($DteRecibido) ? $DteRecibido->folio : '',
    'check' => 'notempty integer',
    'attr' => (isset($DteRecibido) and $DteRecibido->intercambio) ? 'readonly="readonly"' : '',
]);
echo $f->input([
    'type' => (isset($DteRecibido) and $DteRecibido->intercambio) ? 'text' : 'date',
    'name' => 'fecha',
    'label' => 'Fecha documento',
    'value' => isset($DteRecibido) ? $DteRecibido->fecha : '',
    'check' => 'notempty date',
    'attr' => (isset($DteRecibido) and $DteRecibido->intercambio) ? 'readonly="readonly"' : '',
]);
echo $f->input([
    'name' => 'exento',
    'label' => 'Monto exento',
    'value' => isset($DteRecibido) ? $DteRecibido->exento : '',
    'check' => 'integer',
    'attr' => (isset($DteRecibido) and $DteRecibido->intercambio) ? 'readonly="readonly"' : '',
]);
echo $f->input([
    'name' => 'neto',
    'label' => 'Neto',
    'value' => isset($DteRecibido) ? $DteRecibido->neto : '',
    'check' => 'integer',
    'attr' => (isset($DteRecibido) and $DteRecibido->intercambio) ? 'readonly="readonly"' : '',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'impuesto_tipo',
    'label' => 'Tipo de impuesto',
    'value' => isset($DteRecibido) ? $DteRecibido->impuesto_tipo : 1,
    'options' => [1=>'IVA', 2=>'Ley 18211'],
]);
echo $f->input([
    'name' => 'tasa',
    'label' => 'Tasa IVA',
    'value' => isset($DteRecibido) ? $DteRecibido->tasa : $iva_tasa,
    'check' => 'integer',
    'attr' => (isset($DteRecibido) and $DteRecibido->intercambio) ? 'readonly="readonly"' : '',
]);
echo $f->input([
    'name' => 'iva',
    'label' => 'IVA',
    'value' => isset($DteRecibido) ? $DteRecibido->iva : '',
    'check' => 'integer',
    'attr' => (isset($DteRecibido) and $DteRecibido->intercambio) ? 'readonly="readonly"' : '',
]);
echo $f->input([
    'name' => 'periodo',
    'label' => 'Período',
    'value' => isset($DteRecibido) ? $DteRecibido->periodo : '',
    'placeholder' => \sowerphp\general\Utility_Date::nextPeriod(),
    'help' => 'Período en el que registrar el documento, sólo si es diferente al mes de la fecha de emisión. Formato: AAAAMM',
]);
echo $f->input([
    'type' => 'checkbox',
    'name' => 'anulado',
    'checked' => (isset($DteRecibido) and $DteRecibido->anulado == 'A') ? true : false,
    'label' => '¿Anulado?',
]);
echo '</div>',"\n";
echo '<div class="col-md-6">',"\n";
echo $f->input([
    'name' => 'iva_uso_comun',
    'label' => 'IVA uso común',
    'check' => 'integer',
    'value' => isset($DteRecibido) ? $DteRecibido->iva_uso_comun : '',
    'help' => 'Si el IVA es de uso común aquí va el factor de proporcionalidad',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'iva_no_recuperable',
    'label' => 'IVA no recuperable',
    'options' => [''=>'El IVA es recuperable'] + $iva_no_recuperables,
    'value' => isset($DteRecibido) ? $DteRecibido->iva_no_recuperable : '',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'impuesto_adicional',
    'label' => 'Impuesto adicional',
    'options' => [''=>'Sin impuesto adicional'] + $impuesto_adicionales,
    'value' => isset($DteRecibido) ? $DteRecibido->impuesto_adicional : '',
]);
echo $f->input([
    'name' => 'impuesto_adicional_tasa',
    'label' => 'Tasa Imp. adic.',
    'value' => isset($DteRecibido) ? $DteRecibido->impuesto_adicional_tasa : '',
    'check' => 'integer',
    'help' => 'Tasa del impuesto adicional (obligatorio si hay impuesto adicional)'
]);
echo $f->input([
    'name' => 'impuesto_sin_credito',
    'label' => 'Impuesto sin crédito',
    'value' => isset($DteRecibido) ? $DteRecibido->impuesto_sin_credito : '',
    'check' => 'integer',
]);
echo $f->input([
    'name' => 'monto_activo_fijo',
    'label' => 'Monto activo fijo',
    'value' => isset($DteRecibido) ? $DteRecibido->monto_activo_fijo : '',
    'check' => 'integer',
]);
echo $f->input([
    'name' => 'monto_iva_activo_fijo',
    'label' => 'IVA activo fijo',
    'value' => isset($DteRecibido) ? $DteRecibido->monto_iva_activo_fijo : '',
    'check' => 'integer',
]);
echo $f->input([
    'name' => 'iva_no_retenido',
    'label' => 'IVA no retenido',
    'value' => isset($DteRecibido) ? $DteRecibido->iva_no_retenido : '',
    'check' => 'integer',
]);
echo '</div>',"\n";
echo '</div>',"\n";
echo $f->end('Guardar el documento recibido');
