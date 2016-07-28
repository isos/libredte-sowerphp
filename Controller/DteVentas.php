<?php

/**
 * LibreDTE
 * Copyright (C) SASCO SpA (https://sasco.cl)
 *
 * Este programa es software libre: usted puede redistribuirlo y/o
 * modificarlo bajo los términos de la Licencia Pública General Affero de GNU
 * publicada por la Fundación para el Software Libre, ya sea la versión
 * 3 de la Licencia, o (a su elección) cualquier versión posterior de la
 * misma.
 *
 * Este programa se distribuye con la esperanza de que sea útil, pero
 * SIN GARANTÍA ALGUNA; ni siquiera la garantía implícita
 * MERCANTIL o de APTITUD PARA UN PROPÓSITO DETERMINADO.
 * Consulte los detalles de la Licencia Pública General Affero de GNU para
 * obtener una información más detallada.
 *
 * Debería haber recibido una copia de la Licencia Pública General Affero de GNU
 * junto a este programa.
 * En caso contrario, consulte <http://www.gnu.org/licenses/agpl.html>.
 */

// namespace del controlador
namespace website\Dte;

/**
 * Controlador de ventas
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2016-06-14
 */
class Controller_DteVentas extends Controller_Base_Libros
{

    protected $config = [
        'model' => [
            'singular' => 'Venta',
            'plural' => 'Ventas',
        ]
    ]; ///< Configuración para las acciones del controlador

    /**
     * Acción que envía el archivo XML del libro de ventas al SII
     * Si no hay documentos en el período se enviará sin movimientos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-03-02
     */
    public function enviar_sii($periodo)
    {
        $Emisor = $this->getContribuyente();
        // si el libro fue enviado y no es rectifica error
        $DteVenta = new Model_DteVenta($Emisor->rut, $periodo, (int)$Emisor->config_ambiente_en_certificacion);
        if ($DteVenta->track_id and empty($_POST['CodAutRec']) and $DteVenta->getEstado()!='LRH') {
            \sowerphp\core\Model_Datasource_Session::message(
                'Libro del período '.$periodo.' ya fue enviado, ahora sólo puede  hacer rectificaciones', 'error'
            );
            $this->redirect(str_replace('enviar_sii', 'ver', $this->request->request));
        }
        // si el periodo es mayor o igual al actual no se puede enviar
        if ($periodo >= date('Ym')) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No puede enviar el libro de ventas del período '.$periodo.', debe esperar al mes siguiente del período', 'error'
            );
            $this->redirect(str_replace('enviar_sii', 'ver', $this->request->request));
        }
        // obtener ventas
        $ventas = $Emisor->getVentas($periodo);
        // crear libro
        $Libro = new \sasco\LibreDTE\Sii\LibroCompraVenta();
        // obtener firma
        $Firma = $Emisor->getFirma($this->Auth->User->id);
        if (!$Firma) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No hay firma electrónica asociada a la empresa (o bien no se pudo cargar), debe agregar su firma antes de generar DTE', 'error'
            );
            $this->redirect('/dte/admin/firma_electronicas');
        }
        // agregar detalle
        $documentos = 0;
        foreach ($ventas as $venta) {
            $documentos++;
            // armar detalle para agregar al libro
            $d = [];
            foreach ($venta as $k => $v) {
                if (strpos($k, 'impuesto_')!==0) {
                    if ($v!==null)
                        $d[Model_DteVenta::$libro_cols[$k]] = $v;
                }
            }
            // agregar otros impuestos
            if (!empty($venta['impuesto_codigo'])) {
                $d['OtrosImp'] = [
                    'CodImp' => $venta['impuesto_codigo'],
                    'TasaImp' => $venta['impuesto_tasa'],
                    'MntImp' => $venta['impuesto_monto'],
                ];
            }
            // agregar detalle al libro
            $Libro->agregar($d);
        }
        // agregar carátula al libro
        $caratula = [
            'RutEmisorLibro' => $Emisor->rut.'-'.$Emisor->dv,
            'RutEnvia' => $Firma->getID(),
            'PeriodoTributario' => substr($periodo, 0, 4).'-'.substr($periodo, 4),
            'FchResol' => $Emisor->config_ambiente_en_certificacion ? $Emisor->config_ambiente_certificacion_fecha : $Emisor->config_ambiente_produccion_fecha,
            'NroResol' =>  $Emisor->config_ambiente_en_certificacion ? 0 : $Emisor->config_ambiente_produccion_numero,
            'TipoOperacion' => 'VENTA',
            'TipoLibro' => 'MENSUAL',
            'TipoEnvio' => 'TOTAL',
        ];
        if (!empty($_POST['CodAutRec'])) {
            $caratula['TipoLibro'] = 'RECTIFICA';
            $caratula['CodAutRec'] = $_POST['CodAutRec'];
        }
        $Libro->setCaratula($caratula);
        // se setean resúmenes manuales enviados por post
        if (isset($_POST['TpoDoc'])) {
            $resumen = [];
            $n_tipos = count($_POST['TpoDoc']);
            for ($i=0; $i<$n_tipos; $i++) {
                $cols = [
                    'TpoDoc',
                    'TotDoc',
                    'TotAnulado',
                    'TotOpExe',
                    'TotMntExe',
                    'TotMntNeto',
                    'TotMntIVA',
                    'TotIVAPropio',
                    'TotIVATerceros',
                    'TotLey18211',
                    'TotMntTotal',
                    'TotMntNoFact',
                    'TotMntPeriodo',
                ];
                $row = [];
                foreach ($cols as $col) {
                    if (!empty($_POST[$col][$i])) {
                        $row[$col] = $_POST[$col][$i];
                    }
                }
                $resumen[] = $row;
            }
            $Libro->setResumen($resumen);
        }
        // obtener XML
        $Libro->setFirma($Firma);
        $xml = $Libro->generar();
        if (!$xml) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible generar el libro de ventas<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'error'
            );
            $this->redirect(str_replace('enviar_sii', 'ver', $this->request->request));
        }
        // enviar al SII
        $track_id = $Libro->enviar();
        if (!$track_id) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible enviar el libro de ventas al SII<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'error'
            );
            $this->redirect(str_replace('enviar_sii', 'ver', $this->request->request));
        }
        // guardar libro de ventas
        $DteVenta->documentos = $documentos;
        $DteVenta->xml = base64_encode($xml);
        $DteVenta->track_id = $track_id;
        $DteVenta->revision_estado = null;
        $DteVenta->revision_detalle = null;
        $DteVenta->save();
        \sowerphp\core\Model_Datasource_Session::message(
            'Libro de ventas período '.$periodo.' envíado', 'ok'
        );
        $this->redirect(str_replace('enviar_sii', 'ver', $this->request->request));
    }

    /**
     * Acción que genera la imagen del gráfico de torta de ventas
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-26
     */
    public function grafico_tipos($periodo)
    {
        $Emisor = $this->getContribuyente();
        $ventas = $Emisor->getVentasPorTipo($periodo);
        $chart = new \sowerphp\general\View_Helper_Chart();
        $chart->pie('Ventas por tipo de DTE del período '.$periodo, $ventas);
    }

}
