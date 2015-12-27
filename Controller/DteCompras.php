<?php

/**
 * LibreDTE
 * Copyright (C) SASCO SpA (https://sasco.cl)
 *
 * Este programa es software libre: usted puede redistribuirlo y/o
 * modificarlo bajo los términos de la Licencia Pública General GNU
 * publicada por la Fundación para el Software Libre, ya sea la versión
 * 3 de la Licencia, o (a su elección) cualquier versión posterior de la
 * misma.
 *
 * Este programa se distribuye con la esperanza de que sea útil, pero
 * SIN GARANTÍA ALGUNA; ni siquiera la garantía implícita
 * MERCANTIL o de APTITUD PARA UN PROPÓSITO DETERMINADO.
 * Consulte los detalles de la Licencia Pública General GNU para obtener
 * una información más detallada.
 *
 * Debería haber recibido una copia de la Licencia Pública General GNU
 * junto a este programa.
 * En caso contrario, consulte <http://www.gnu.org/licenses/gpl.html>.
 */

// namespace del controlador
namespace website\Dte;

/**
 * Controlador de compras
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2015-12-25
 */
class Controller_DteCompras extends Controller_Libros
{

    protected $config = [
        'model' => [
            'singular' => 'Compra',
            'plural' => 'Compras',
        ]
    ]; ///< Configuración para las acciones del controlador

    protected $libro_cols = [
        'dte' => 'TpoDoc',
        'folio' => 'NroDoc',
        'tasa' => 'TasaImp',
        'fecha' => 'FchDoc',
        'sucursal_sii' => 'CdgSIISucur',
        'rut' => 'RUTDoc',
        'razon_social' => 'RznSoc',
        'exento' => 'MntExe',
        'neto' => 'MntNeto',
        'iva' => 'MntIVA',
        'iva_no_recuperable' => 'CodIVANoRec',
        'iva_no_recuperable_monto' => 'MntIVANoRec',
        'iva_uso_comun' => 'FctProp',
        'impuesto_codigo' => 'CodImp',
        'impuesto_tasa' => 'TasaImp',
        'impuesto_monto' => 'MntImp',
        'total' => 'MntTotal',
    ]; ///< Columnas del archivo CSV del libro

    /**
     * Acción que envía el archivo XML del libro de compras al SII
     * Si no hay documentos en el período se enviará sin movimientos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-12-27
     */
    public function enviar_sii($periodo)
    {
        $Emisor = \sowerphp\core\Model_Datasource_Session::read('dte.Emisor');
        // si el periodo es mayor o igual al actual no se puede enviar
        if ($periodo >= date('Ym')) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No puede enviar el libro de compras del período '.$periodo.', debe esperar al mes siguiente del período', 'error'
            );
            $this->redirect(str_replace('enviar_sii', 'ver', $this->request->request));
        }
        // obtener compras
        $compras = $Emisor->getCompras($periodo);
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
        foreach ($compras as $compra) {
            $documentos++;
            // armar detalle para agregar al libro
            $d = [];
            foreach ($compra as $k => $v) {
                if (strpos($k, 'impuesto_adicional')!==0 and strpos($k, 'iva_no_recuperable')!==0) {
                    if ($v!==null)
                        $d[$this->libro_cols[$k]] = $v;
                }
            }
            // agregar iva no recuperable
            if (!empty($compra['iva_no_recuperable'])) {
                $d['IVANoRec'] = [
                    'CodIVANoRec' => $compra['iva_no_recuperable'],
                    'MntIVANoRec' => $compra['iva_no_recuperable_monto'],
                ];
            }
            // agregar otros impuestos
            if (!empty($compra['impuesto_adicional'])) {
                $d['OtrosImp'] = [
                    'CodImp' => $compra['impuesto_adicional'],
                    'TasaImp' => $compra['impuesto_adicional_tasa'],
                    'MntImp' => $compra['impuesto_adicional_monto'],
                ];
            }
            // agregar detalle al libro
            $Libro->agregar($d);
        }
        // agregar carátula al libro
        $Libro->setCaratula([
            'RutEmisorLibro' => $Emisor->rut.'-'.$Emisor->dv,
            'RutEnvia' => $Firma->getID(),
            'PeriodoTributario' => substr($periodo, 0, 4).'-'.substr($periodo, 4),
            'FchResol' => $Emisor->certificacion ? $Emisor->certificacion_resolucion : $Emisor->resolucion_fecha,
            'NroResol' =>  $Emisor->certificacion ? 0 : $Emisor->resolucion_numero,
            'TipoOperacion' => 'COMPRA',
            'TipoLibro' => 'MENSUAL',
            'TipoEnvio' => 'TOTAL',
        ]);
        // obtener XML
        $Libro->setFirma($Firma);
        $xml = $Libro->generar();
        if (!$xml) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible generar el libro de compras<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'error'
            );
            $this->redirect(str_replace('enviar_sii', 'ver', $this->request->request));
        }
        // enviar al SII
        $track_id = $Libro->enviar();
        if (!$track_id) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible enviar el libro de compras al SII<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'error'
            );
            $this->redirect(str_replace('enviar_sii', 'ver', $this->request->request));
        }
        // guardar libro de compras
        $DteCompra = new Model_DteCompra($Emisor->rut, $periodo, (int)$Emisor->certificacion);
        $DteCompra->documentos = $documentos;
        $DteCompra->xml = base64_encode($xml);
        $DteCompra->track_id = $track_id;
        $DteCompra->save();
        \sowerphp\core\Model_Datasource_Session::message(
            'Libro de compras período '.$periodo.' envíado', 'ok'
        );
        $this->redirect(str_replace('enviar_sii', 'ver', $this->request->request));
    }

    /**
     * Acción que genera la imagen del gráfico de torta de compras
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-28
     */
    public function grafico_tipos($periodo)
    {
        $Emisor = \sowerphp\core\Model_Datasource_Session::read('dte.Emisor');
        $compras = $Emisor->getComprasPorTipo($periodo);
        $chart = new \sowerphp\general\View_Helper_Chart();
        $chart->pie('Compras por tipo de DTE del período '.$periodo, $compras);
    }

}
