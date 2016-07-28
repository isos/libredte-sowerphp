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
 * Controlador de dte temporales
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2016-06-12
 */
class Controller_DteTmps extends \Controller_App
{

    /**
     * Método que muestra los documentos temporales disponibles
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-06-13
     */
    public function index()
    {
        $Emisor = $this->getContribuyente();
        $DteTmps = new Model_DteTmps();
        $DteTmps->setWhereStatement(['emisor = :rut'], [':rut'=>$Emisor->rut]);
        $DteTmps->setOrderByStatement('fecha DESC', 'receptor');
        $this->set([
            'Emisor' => $Emisor,
            'dtes' => $DteTmps->getObjects(),
        ]);
    }

    /**
     * Método que genera la cotización en PDF del DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-06-13
     */
    public function cotizacion($receptor, $dte, $codigo)
    {
        $Emisor = $this->getContribuyente();
        // obtener datos JSON del DTE
        $DteTmp = new Model_DteTmp($Emisor->rut, $receptor, $dte, $codigo);
        if (!$DteTmp->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el DTE temporal solicitado', 'error'
            );
            $this->redirect('/dte/dte_tmps');
        }
        $datos = json_decode($DteTmp->datos, true);
        $datos['Encabezado']['IdDoc']['TipoDTE'] = 0;
        $datos['Encabezado']['IdDoc']['Folio'] = $DteTmp->getFolio();
        // generar PDF
        $pdf = new \sasco\LibreDTE\Sii\PDF\Dte();
        $pdf->setFooterText(\sowerphp\core\Configure::read('dte.pdf.footer'));
        $logo = \sowerphp\core\Configure::read('dte.logos.dir').'/'.$Emisor->rut.'.png';
        if (is_readable($logo)) {
            $pdf->setLogo($logo);
        }
        $pdf->agregar($datos);
        $file = 'cotizacion_'.$Emisor->rut.'-'.$Emisor->dv.'_'.$DteTmp->getFolio().'.pdf';
        $pdf->Output($file, 'D');
        exit;
    }

    /**
     * Método que genera la previsualización del PDF del DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-03-10
     */
    public function pdf($receptor, $dte, $codigo)
    {
        $Emisor = $this->getContribuyente();
        // obtener datos JSON del DTE
        $DteTmp = new Model_DteTmp($Emisor->rut, $receptor, $dte, $codigo);
        if (!$DteTmp->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el DTE temporal solicitado', 'error'
            );
            $this->redirect('/dte/dte_tmps');
        }
        // armar xml a partir de datos del dte temporal
        $xml = $DteTmp->getEnvioDte()->generar();
        if (!$xml) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible crear el PDF para previsualización:<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'error'
            );
            $this->redirect('/dte/dte_tmps');
        }
        // armar datos con archivo XML y flag para indicar si es cedible o no
        $data = [
            'xml' => base64_encode($xml),
            'cedible' => false,
            'papelContinuo' => $Emisor->config_pdf_dte_papel,
            'compress' => false,
        ];
        // si hay un logo para la empresa se usa
        $logo = \sowerphp\core\Configure::read('dte.logos.dir').'/'.$Emisor->rut.'.png';
        if (is_readable($logo)) {
            $data['logo'] = base64_encode(file_get_contents($logo));
        }
        // realizar consulta a la API
        $rest = new \sowerphp\core\Network_Http_Rest();
        $rest->setAuth($this->Auth->User ? $this->Auth->User->hash : $this->token);
        $response = $rest->post($this->request->url.'/api/dte/documentos/generar_pdf', $data);
        if ($response['status']['code']!=200) {
            \sowerphp\core\Model_Datasource_Session::message($response['body'], 'error');
            return;
        }
        // si dió código 200 se entrega la respuesta del servicio web
        foreach (['Content-Disposition', 'Content-Length', 'Content-Type'] as $header) {
            if (isset($response['header'][$header]))
                header($header.': '.$response['header'][$header]);
        }
        echo $response['body'];
        exit;
    }

    /**
     * Método que genera la previsualización del XML del DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-01-30
     */
    public function xml($receptor, $dte, $codigo)
    {
        $Emisor = $this->getContribuyente();
        // obtener datos JSON del DTE
        $DteTmp = new Model_DteTmp($Emisor->rut, $receptor, $dte, $codigo);
        if (!$DteTmp->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el DTE temporal solicitado', 'error'
            );
            $this->redirect('/dte/dte_tmps');
        }
        // armar xml a partir de datos del dte temporal
        $xml = $DteTmp->getEnvioDte()->generar();
        if (!$xml) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible crear el XML para previsualización:<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'error'
            );
            $this->redirect('/dte/dte_tmps');
        }
        // entregar xml
        header('Content-Type: application/xml; charset=ISO-8859-1');
        header('Content-Length: '.strlen($xml));
        header('Content-Disposition: attachement; filename="'.$receptor.'_'.$dte.'_'.$codigo.'.xml"');
        print $xml;
        exit;
    }

    /**
     * Método que elimina un DTE temporal
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-23
     */
    public function eliminar($receptor, $dte, $codigo)
    {
        $Emisor = $this->getContribuyente();
        // obtener DTE temporal
        $DteTmp = new Model_DteTmp($Emisor->rut, $receptor, $dte, $codigo);
        if (!$DteTmp->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el DTE temporal solicitado', 'error'
            );
            $this->redirect('/dte/dte_tmps');
        }
        // eliminar
        try {
            $DteTmp->delete();
            \sowerphp\core\Model_Datasource_Session::message(
                'DTE temporal eliminado', 'ok'
            );
            $this->redirect('/dte/dte_tmps');
        } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible eliminar el DTE temporal: '.$e->getMessage()
            );
            $this->redirect('/dte/dte_tmps');
        }
    }

    /**
     * Método que actualiza un DTE temporal
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-06-13
     */
    public function actualizar($receptor, $dte, $codigo, $fecha = null, $actualizar_precios = true)
    {
        $Emisor = $this->getContribuyente();
        // obtener DTE temporal
        $DteTmp = new Model_DteTmp($Emisor->rut, $receptor, $dte, $codigo);
        if (!$DteTmp->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el DTE temporal solicitado', 'error'
            );
            $this->redirect('/dte/dte_tmps');
        }
        // nueva fecha de actualización
        if (!$fecha) {
            $fecha = date('Y-m-d');
        }
        if ($DteTmp->fecha==$fecha) {
            \sowerphp\core\Model_Datasource_Session::message(
                'DTE temporal ya está con fecha '.$fecha, 'warning'
            );
            $this->redirect('/dte/dte_tmps');
        }
        // actualizar fechas del DTE temporal
        $datos = json_decode($DteTmp->datos, true);
        $FchEmis = $datos['Encabezado']['IdDoc']['FchEmis'];
        $datos['Encabezado']['IdDoc']['FchEmis'] = $fecha;
        $datos['Encabezado']['IdDoc']['FchCancel'] = false;
        if ($datos['Encabezado']['IdDoc']['FchVenc']) {
            $dias = \sowerphp\general\Utility_Date::count($datos['Encabezado']['IdDoc']['FchVenc'], $FchEmis);
            $datos['Encabezado']['IdDoc']['FchVenc'] = date('Y-m-d', strtotime($fecha)+$dias*86400);
        }
        // actualizar precios de items (siempre que esten codificados)
        if ($actualizar_precios) {
            // actualizar precios de items si es que corresponde: existe código
            // del item, existe el item, existe un precio y es diferente al que
            // ya está asignado
            $fecha_calculo = !empty($datos['Encabezado']['IdDoc']['FchVenc']) ? $datos['Encabezado']['IdDoc']['FchVenc'] : $fecha;
            $precios_actualizados = false;
            foreach ($datos['Detalle'] as &$d) {
                if (empty($d['CdgItem']['VlrCodigo'])) {
                    continue;
                }
                $Item = (new \website\Dte\Admin\Model_Itemes())->get(
                    $Emisor->rut,
                    $d['CdgItem']['VlrCodigo'],
                    !empty($d['CdgItem']['TpoCodigo']) ? $d['CdgItem']['TpoCodigo'] : null
                );
                if ($Item->exists()) {
                    $precio = $Item->getPrecio($fecha_calculo);
                    if ($precio and $d['PrcItem']!=$precio) {
                        $precios_actualizados = true;
                        $d['PrcItem'] = $precio;
                        if ($d['DescuentoPct']) {
                            $d['DescuentoMonto'] = false;
                        }
                        if ($d['RecargoPct']) {
                            $d['RecargoMonto'] = false;
                        }
                        $d['MontoItem'] = false;
                    }
                }
            }
            // si se actualizó algún precio se deben recalcular los totales
            if ($precios_actualizados) {
                $datos['Encabezado']['Totales'] = [];
                $datos = (new \sasco\LibreDTE\Sii\Dte($datos))->getDatos();
            }
        }
        // guardar nuevo dte temporal
        $DteTmp->fecha = $fecha;
        $DteTmp->total = $datos['Encabezado']['Totales']['MntTotal'];
        $DteTmp->datos = json_encode($datos);
        $DteTmp->codigo = md5($DteTmp->datos);
        try {
            $DteTmp->save();
            \sowerphp\core\Model_Datasource_Session::message(
                'Se actualizó el DTE temporal al '.$fecha, 'ok'
            );
        } catch (\Exception $e) {
             \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible actualizar el DTE temporal al '.$fecha, 'error'
            );
        }
        $this->redirect('/dte/dte_tmps');
    }

}
