<h1>Documentos temporales</h1>
<p>Aquí se listan los documentos temporales del emisor <?=$Emisor->razon_social?> que ya están normalizados pero que aun no han sido generados oficialmente (no poseen folio, ni timbre, ni firma).</p>
<?php
$documentos = [['Receptor', 'Documento', 'Fecha', 'Total', 'Acciones']];
foreach ($dtes as &$dte) {
    $acciones = '<a href="dte_tmps/cotizacion/'.$dte->receptor.'/'.$dte->dte.'/'.$dte->codigo.'" title="Descargar cotización basada en DTE temporal"><span class="fa fa-dollar btn btn-default"></span></a>';
    $acciones .= ' <a href="dte_tmps/pdf/'.$dte->receptor.'/'.$dte->dte.'/'.$dte->codigo.'" title="Ver PDF de previsualización del DTE"><span class="fa fa-file-pdf-o btn btn-default"></span></a>';
    //$acciones .= ' <a href="dte_tmps/xml/'.$dte->receptor.'/'.$dte->dte.'/'.$dte->codigo.'" title="Ver XML de previsualización del DTE"><span class="fa fa-file-code-o btn btn-default"></span></a>';
    $acciones .= ' <a href="dte_tmps/actualizar/'.$dte->receptor.'/'.$dte->dte.'/'.$dte->codigo.'" title="Actualizar DTE al día de hoy" onclick="return Form.checkSend(\'¿Desea actualizar al día de hoy?\')"><span class="fa fa-refresh btn btn-default"></span></a>';
    $acciones .= ' <a href="dte_tmps/eliminar/'.$dte->receptor.'/'.$dte->dte.'/'.$dte->codigo.'" title="Eliminar DTE temporal"><span class="fa fa-times-circle btn btn-default" onclick="return eliminar(\'DteTmp\', \''.$dte->receptor.', '.$dte->dte.', '.$dte->codigo.'\')"></span></a>';
    $acciones .= ' <a href="documentos/generar/'.$dte->receptor.'/'.$dte->dte.'/'.$dte->codigo.'" title="Generar DTE y enviar al SII" onclick="return Form.checkSend(\'¿Está seguro de querer generar el DTE?\')"><span class="fa fa-send-o btn btn-default"></span></a>';
    $documentos[] = [
        $dte->getReceptor()->razon_social.'<span>'.$dte->getReceptor()->rut.'-'.$dte->getReceptor()->dv.'</span>',
        $dte->getDte()->tipo.'<span>'.$dte->getFolio().'</span>',
        \sowerphp\general\Utility_Date::format($dte->fecha),
        num($dte->total),
        $acciones
    ];
}
$t = new \sowerphp\general\View_Helper_Table();
$t->setColsWidth([null, null, null, null, 230]);
echo $t->generate($documentos);
