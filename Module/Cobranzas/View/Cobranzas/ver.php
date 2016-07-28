<h1>Pago programado <small><?=$Pago->getDte()->getTipo()->tipo?> N° <?=$Pago->folio?></small></h1>
<div class="panel panel-default">
    <div class="panel-heading">
        <i class="fa fa-info"></i>
        Información del pago programado
    </div>
    <div class="panel-body">
<?php
new \sowerphp\general\View_Helper_Table([
    ['Receptor', 'RUT', 'Total DTE', 'Fecha programada', 'Monto programado', 'Glosa'],
    [
        $Pago->getDte()->getReceptor()->razon_social,
        \sowerphp\app\Utility_Rut::addDV($Pago->getDte()->getReceptor()->rut),
        '$'.num($Pago->getDte()->total).'.-',
        \sowerphp\general\Utility_Date::format($Pago->fecha),
        '$'.num($Pago->monto).'.-',
        $Pago->glosa,
    ],
]);
?>
    </div>
</div>
<div class="row">
    <div class="col-md-5">
<?php
$f = new \sowerphp\general\View_Helper_Form();
$f->setColsLabel(5);
echo $f->begin(['onsubmit'=>'pago_check()']);
echo $f->input([
    'type' => 'date',
    'name' => 'modificado',
    'label' => 'Última modificación',
    'value' => $Pago->modificado ? $Pago->modificado : date('Y-m-d'),
    'check' => 'notempty date',
    'attr' => 'readonly="readonly"',
]);
echo $f->input([
    'type' => 'hidden',
    'name' => 'monto_original',
    'value' => (int)$Pago->monto,
]);
echo $f->input([
    'type' => 'hidden',
    'name' => 'pagado_original',
    'value' => (int)$Pago->pagado,
]);
echo $f->input([
    'name' => 'abono',
    'label' => 'Pago o abono',
    'check' => 'notempty integer',
    'attr' => 'onblur="pago_actualizar()"',
]);
echo $f->input([
    'name' => 'pagado',
    'label' => 'Monto pagado',
    'value' => (int)$Pago->pagado,
    'check' => 'notempty integer',
    'attr' => 'readonly="readonly"',
]);
echo $f->input([
    'name' => 'pendiente',
    'label' => 'Monto pendiente',
    'value' => $Pago->monto - (int)$Pago->pagado,
    'check' => 'notempty integer',
    'attr' => 'readonly="readonly"',
]);
echo $f->input([
    'type' => 'textarea',
    'name' => 'observacion',
    'label' => 'Observación',
]);
echo $f->end('Guardar');
?>
    </div>
    <div class="col-md-7">
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="fa fa-location-arrow"></i>
                Datos de contacto
            </div>
            <div class="panel-body">
<?php
new \sowerphp\general\View_Helper_Table([
    ['Dirección', 'Teléfono', 'Email'],
    [
        $Pago->getDte()->getReceptor()->direccion.', '.$Pago->getDte()->getReceptor()->getComuna()->comuna,
        $Pago->getDte()->getReceptor()->telefono,
        $Pago->getDte()->getReceptor()->email,
    ],
]);
?>
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="fa fa-calendar"></i>
                Otros pagos programados asociados al DTE
            </div>
            <div class="panel-body">
<?php
$otros = $Pago->otrosPagos();
foreach ($otros as &$otro) {
    $otro['fecha'] = \sowerphp\general\Utility_Date::format($otro['fecha']);
    $otro['monto'] = num($otro['monto']);
    $otro['pagado'] = num($otro['pagado']);
}
array_unshift($otros, ['Fecha', 'Monto', 'Glosa', 'Pagado', 'Observación']);
new \sowerphp\general\View_Helper_Table($otros);
?>
            </div>
        </div>
        <div class="row">
                <div class="col-md-4">
                    <a class="btn btn-default btn-block" href="<?=$_base?>/dte/dte_emitidos/ver/<?=$Pago->getDte()->dte?>/<?=$Pago->getDte()->folio?>" role="button">
                        <span class="fa fa-search"></span>
                        Ver DTE
                    </a>
                </div>
                <div class="col-md-4">
                    <a class="btn btn-default btn-block" href="<?=$_base?>/dte/dte_emitidos/pdf/<?=$Pago->getDte()->dte?>/<?=$Pago->getDte()->folio?>/<?=$Emisor->config_pdf_dte_cedible?>" role="button">
                        <span class="fa fa-file-pdf-o"></span>
                        Ver PDF
                    </a>
                </div>
                <div class="col-md-4">
                    <a class="btn btn-default btn-block" href="<?=$_base?>/dte/dte_emitidos/xml/<?=$Pago->getDte()->dte?>/<?=$Pago->getDte()->folio?>" role="button">
                        <span class="fa fa-file-code-o"></span>
                        Ver XML
                    </a>
                </div>
            </div>
    </div>
</div>
