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
 * Controlador de dte temporales
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2015-09-26
 */
class Controller_DteTmps extends \Controller_App
{

    /**
     * Método que muestra los documentos temporales disponibles
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2015-09-22
     */
    public function index()
    {
        $Emisor = \sowerphp\core\Model_Datasource_Session::read('dte.Emisor');
        $DteTmps = new Model_DteTmps();
        $DteTmps->setWhereStatement(['emisor = :rut'], [':rut'=>$Emisor->rut]);
        $this->set([
            'Emisor' => $Emisor,
            'dtes' => $DteTmps->getObjects(),
        ]);
    }

    /**
     * Método que genera la previsualización del PDF del DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2015-09-24
     */
    public function pdf($receptor, $dte, $codigo)
    {
        $Emisor = \sowerphp\core\Model_Datasource_Session::read('dte.Emisor');
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
     * Método que elimina un DTE temporal
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2015-09-23
     */
    public function eliminar($receptor, $dte, $codigo)
    {
        $Emisor = \sowerphp\core\Model_Datasource_Session::read('dte.Emisor');
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

}
