<h1>Documentos temporales</h1>
<p>Aquí se listan los documentos temporales del emisor <?=$Emisor->razon_social?> que ya están normalizados pero que aun no han sido generados oficialmente (no poseen folio, ni timbre, ni firma).</p>
<?php
$documentos = [['RUT receptor', 'Razón social receptor', 'DTE', 'Fecha', 'Total', 'Acciones']];
foreach ($dtes as &$dte) {
    $acciones = '<a href="dte_tmps/eliminar/'.$dte->receptor.'/'.$dte->dte.'/'.$dte->codigo.'" title="Eliminar DTE temporal"><span class="fa fa-times-circle btn btn-default" onclick="return eliminar(\'DteTmp\', \''.$dte->receptor.', '.$dte->dte.', '.$dte->codigo.'\')"></span></a>';
    $acciones .= ' <a href="dte_tmps/pdf/'.$dte->receptor.'/'.$dte->dte.'/'.$dte->codigo.'" title="Ver PDF de previsualización del DTE"><span class="fa fa-file-pdf-o btn btn-default"></span></a>';
    $acciones .= ' <a href="documentos/generar/'.$dte->receptor.'/'.$dte->dte.'/'.$dte->codigo.'" title="Generar DTE y enviar al SII"><span class="fa fa-send-o btn btn-default"></span></a>';
    $documentos[] = [
        $dte->getReceptor()->rut.'-'.$dte->getReceptor()->dv,
        $dte->getReceptor()->razon_social,
        $dte->getDte()->tipo,
        $dte->fecha,
        num($dte->total),
        $acciones
    ];
}
new \sowerphp\general\View_Helper_Table($documentos);
