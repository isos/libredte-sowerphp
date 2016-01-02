<?php

/**
 * SowerPHP: Minimalist Framework for PHP
 * Copyright (C) SowerPHP (http://sowerphp.org)
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
 * Controlador de libro de guías de despacho
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2015-12-27
 */
class Controller_DteGuias extends Controller_Libros
{

    protected $config = [
        'model' => [
            'singular' => 'Guia',
            'plural' => 'Guias',
        ]
    ]; ///< Configuración para las acciones del controlador

    protected $libro_cols = [
        'folio' => 'Folio',
        'anulado' => 'Anulado',
        'operacion' => 'Operacion',
        'tipo' => 'TpoOper',
        'fecha' => 'FchDoc',
        'rut' => 'RUTDoc',
        'razon_social' => 'RznSoc',
        'neto' => 'MntNeto',
        'tasa' => 'TasaImp',
        'iva' => 'IVA',
        'total' => 'MntTotal',
        'modificado' => 'MntModificado',
        'ref_dte' => 'TpoDocRef',
        'ref_folio' => 'FolioDocRef',
        'ref_fecha' => 'FchDocRef',
    ]; ///< Columnas del archivo CSV del libro

    /**
     * Acción que envía el archivo XML del libro de guías al SII
     * Si no hay documentos en el período se enviará sin movimientos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-12-27
     */
    public function enviar_sii($periodo)
    {
        $Emisor = $this->getContribuyente();
        // si el periodo es mayor o igual al actual no se puede enviar
        if ($periodo >= date('Ym')) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No puede enviar el libro de guías del período '.$periodo.', debe esperar al mes siguiente del período', 'error'
            );
            $this->redirect(str_replace('enviar_sii', 'ver', $this->request->request));
        }
        // obtener guías
        $guias = $Emisor->getGuias($periodo);
        // crear libro
        $Libro = new \sasco\LibreDTE\Sii\LibroGuia();
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
        foreach ($guias as $guia) {
            $documentos++;
            // armar detalle para agregar al libro
            $d = [];
            foreach ($guia as $k => $v) {
                if ($v!==null)
                    $d[$this->libro_cols[$k]] = $v;
            }
            // agregar detalle al libro
            $Libro->agregar($d);
        }
        // agregar carátula al libro
        $Libro->setFirma($Firma);
        $Libro->setCaratula([
            'RutEmisorLibro' => $Emisor->rut.'-'.$Emisor->dv,
            'PeriodoTributario' => substr($periodo, 0, 4).'-'.substr($periodo, 4),
            'FchResol' => $Emisor->certificacion ? $Emisor->certificacion_resolucion : $Emisor->resolucion_fecha,
            'NroResol' =>  $Emisor->certificacion ? 0 : $Emisor->resolucion_numero,
            'TipoLibro' => 'ESPECIAL',
            'TipoEnvio' => 'TOTAL',
            'FolioNotificacion' => 1,
        ]);
        // obtener XML
        $xml = $Libro->generar();
        if (!$xml) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible generar el libro de guías<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'error'
            );
            $this->redirect(str_replace('enviar_sii', 'ver', $this->request->request));
        }
        // enviar al SII
        $track_id = $Libro->enviar();
        if (!$track_id) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible enviar el libro de guías al SII<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'error'
            );
            $this->redirect(str_replace('enviar_sii', 'ver', $this->request->request));
        }
        // guardar libro de ventas
        $DteGuia = new Model_DteGuia($Emisor->rut, $periodo, (int)$Emisor->certificacion);
        $DteGuia->documentos = $documentos;
        $DteGuia->xml = base64_encode($xml);
        $DteGuia->track_id = $track_id;
        $DteGuia->save();
        \sowerphp\core\Model_Datasource_Session::message(
            'Libro de guías período '.$periodo.' envíado', 'ok'
        );
        $this->redirect(str_replace('enviar_sii', 'ver', $this->request->request));
    }

}
