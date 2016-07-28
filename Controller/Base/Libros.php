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
 * Controlador base para libros
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2016-06-14
 */
abstract class Controller_Base_Libros extends \Controller_App
{

    /**
     * Acción que muestra el resumen de los períodos del libro
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-12-25
     */
    public function index()
    {
        $Emisor = $this->getContribuyente();
        $this->set([
            'periodos' => $Emisor->{'getResumen'.$this->config['model']['plural'].'Periodos'}(),
        ]);
    }

    /**
     * Acción que muestra la información del libro para cierto período
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-02-12
     */
    public function ver($periodo)
    {
        $Emisor = $this->getContribuyente();
        $detalle = $Emisor->{'get'.$this->config['model']['plural']}($periodo);
        $class = __NAMESPACE__.'\Model_Dte'.$this->config['model']['singular'];
        $Libro = new $class($Emisor->rut, (int)$periodo, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$detalle and !$Libro->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No hay documentos ni libro del período '.$periodo, 'error'
            );
            $this->redirect('/dte/'.$this->request->params['controller']);
        }
        $this->set([
            'Emisor' => $Emisor,
            'Libro' => $Libro,
            'resumen' => $Libro->getResumen(),
            'detalle' => $detalle,
            'libro_cols' => $class::$libro_cols,
        ]);
    }

    /**
     * Acción que descarga los datos del libro del período en un archivo CSV
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-02-12
     */
    public function csv($periodo)
    {
        $Emisor = $this->getContribuyente();
        $detalle = $Emisor->{'get'.$this->config['model']['plural']}($periodo);
        if (!$detalle) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No hay documentos en el período '.$periodo, 'error'
            );
            $this->redirect('/dte/'.$this->request->params['controller']);
        }
        $class = __NAMESPACE__.'\Model_Dte'.$this->config['model']['singular'];
        array_unshift($detalle, $class::$libro_cols);
        \sowerphp\general\Utility_Spreadsheet_CSV::generate($detalle, strtolower($this->config['model']['plural']).'_'.$Emisor->rut.'-'.$Emisor->dv.'_'.$periodo);
    }

    /**
     * Acción que descarga el archivo PDF del libro
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-06-08
     */
    public function pdf($periodo)
    {
        $Emisor = $this->getContribuyente();
        // crear objeto del libro
        $class = __NAMESPACE__.'\Model_Dte'.$this->config['model']['singular'];
        $Libro = new $class($Emisor->rut, (int)$periodo, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$Libro->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Aun no se ha generado el XML del período '.$periodo, 'error'
            );
            $this->redirect(str_replace('pdf', 'ver', $this->request->request));
        }
        // definir xml y nombre archivo
        $xml = base64_decode($Libro->xml);
        $file = strtolower($this->config['model']['plural']).'_'.$Emisor->rut.'-'.$Emisor->dv.'_'.$periodo.'.pdf';
        // entregar PDF de Compra o Venta
        if (in_array($this->config['model']['singular'], ['Compra', 'Venta'])) {
            $LibroCompraVenta = new \sasco\LibreDTE\Sii\LibroCompraVenta();
            $LibroCompraVenta->loadXML($xml);
            $pdf = new \sasco\LibreDTE\Sii\PDF\LibroCompraVenta();
            $pdf->setFooterText(\sowerphp\core\Configure::read('dte.pdf.footer'));
            $pdf->agregar($LibroCompraVenta->toArray());
            $pdf->Output($file, 'D');
        }
        // entregar libro de guías
        else {
            \sowerphp\core\Model_Datasource_Session::message(
                'Libro en PDF no está implementado', 'error'
            );
            $this->redirect(str_replace('pdf', 'ver', $this->request->request));
        }
        exit;
    }

    /**
     * Acción que descarga el archivo XML del libro
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-12-25
     */
    public function xml($periodo)
    {
        $Emisor = $this->getContribuyente();
        // crear objeto del libro
        $class = __NAMESPACE__.'\Model_Dte'.$this->config['model']['singular'];
        $Libro = new $class($Emisor->rut, (int)$periodo, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$Libro->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Aun no se ha generado el XML del período '.$periodo, 'error'
            );
            $this->redirect(str_replace('xml', 'ver', $this->request->request));
        }
        // entregar XML
        $file = strtolower($this->config['model']['plural']).'_'.$Emisor->rut.'-'.$Emisor->dv.'_'.$periodo.'.xml';
        $xml = base64_decode($Libro->xml);
        header('Content-Type: application/xml; charset=ISO-8859-1');
        header('Content-Length: '.strlen($xml));
        header('Content-Disposition: attachement; filename="'.$file.'"');
        print $xml;
        exit;
    }

    /**
     * Acción que envía el archivo XML del libro al SII
     * Si no hay documentos en el período se enviará sin movimientos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-12-25
     */
    abstract public function enviar_sii($periodo);

    /**
     * Acción que permite solicitar código de autorización para rectificar un
     * libro ya enviado al SII
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-02-13
     */
    public function enviar_rectificacion($periodo)
    {
        $Emisor = $this->getContribuyente();
        // crear objeto del libro
        $class = __NAMESPACE__.'\Model_Dte'.$this->config['model']['singular'];
        $Libro = new $class($Emisor->rut, (int)$periodo, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$Libro->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No ha enviado el libro del período '.$periodo.' SII, no puede rectificar', 'error'
            );
            $this->redirect(str_replace('enviar_rectificacion', 'ver', $this->request->request));
        }
        // asignar variables vista
        $this->set([
            'Emisor' => $Emisor,
            'periodo' => $periodo,
        ]);
    }

    /**
     * Acción para enviar el libro de un período sin movimientos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-12-25
     */
    public function sin_movimientos()
    {
        // procesar sólo si se envío el período
        if (!empty($_POST['periodo'])) {
            // verificar período
            $periodo = (int)$_POST['periodo'];
            if (strlen($_POST['periodo'])!=6 or !$periodo) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Período no es correcto, usar formato AAAAMM', 'error'
                );
                return;
            }
            // redirigir a la página que envía el libro sin movimientos
            $this->redirect('/dte/'.$this->request->params['controller'].'/enviar_sii/'.$periodo);
        }
    }

    /**
     * Acción que solicita se envíe una nueva revisión del libro al email
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-06-14
     */
    public function solicitar_revision($periodo)
    {
        $Emisor = $this->getContribuyente();
        // obtener libro envíado
        $class = __NAMESPACE__.'\Model_Dte'.$this->config['model']['singular'];
        $Libro = new $class($Emisor->rut, (int)$periodo, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$Libro->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Aun no se ha generado el libro del período '.$periodo, 'error'
            );
            $this->redirect(str_replace('solicitar_revision', 'ver', $this->request->request));
        }
        // solicitar envío de nueva revisión
        $estado = $Libro->solicitarRevision($this->Auth->User->id);
        if ($estado===false) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible solicitar una nueva revisión del libro.<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'error'
            );
        } else if ((int)$estado->xpath('/SII:RESPUESTA/SII:RESP_HDR/SII:ESTADO')[0]) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible solicitar una nueva revisión del libro: '.$estado->xpath('/SII:RESPUESTA/SII:RESP_HDR/SII:GLOSA')[0], 'error'
            );
        } else {
            \sowerphp\core\Model_Datasource_Session::message(
                'Se solicitó nueva revisión del libro, verificar estado en unos segundos', 'ok'
            );
        }
        // redireccionar
        $this->redirect(str_replace('solicitar_revision', 'ver', $this->request->request));
    }

    /**
     * Acción que actualiza el estado del envío del libro
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-12-26
     */
    public function actualizar_estado($periodo)
    {
        $Emisor = $this->getContribuyente();
        // obtener libro envíado
        $class = __NAMESPACE__.'\Model_Dte'.$this->config['model']['singular'];
        $Libro = new $class($Emisor->rut, (int)$periodo, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$Libro->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Aun no se ha generado el libro del período '.$periodo, 'error'
            );
            $this->redirect(str_replace('actualizar_estado', 'ver', $this->request->request));
        }
        // si no tiene track id error
        if (!$Libro->track_id) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Libro del período '.$periodo.' no tiene Track ID, primero debe enviarlo al SII', 'error'
            );
            $this->redirect(str_replace('actualizar_estado', 'ver', $this->request->request));
        }
        // buscar correo con respuesta
        $Imap = $Emisor->getEmailImap('sii');
        if (!$Imap) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible conectar mediante IMAP a '.$Emisor->config_email_sii_imap.', verificar mailbox, usuario y/o contraseña de contacto SII:<br/>'.implode('<br/>', imap_errors()), 'error'
            );
            $this->redirect(str_replace('actualizar_estado', 'ver', $this->request->request));
        }
        $asunto = 'Revision Envio de Libro Normal '.$Libro->track_id.' - '.$Emisor->rut.'-'.$Emisor->dv;
        $uids = $Imap->search('FROM @sii.cl SUBJECT "'.$asunto.'" UNSEEN');
        if (!$uids) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No se encontró respuesta de envío del libro, espere unos segundos o solicite nueva revisión.'
            );
            $this->redirect(str_replace('actualizar_estado', 'ver', $this->request->request));
        }
        // procesar emails recibidos
        foreach ($uids as $uid) {
            $m = $Imap->getMessage($uid);
            if (!$m)
                continue;
            foreach ($m['attachments'] as $file) {
                if ($file['type']!='application/xml')
                    continue;
                $status = $Libro->saveRevision($file['data']);
                if ($status===true) {
                    \sowerphp\core\Model_Datasource_Session::message(
                        'Se actualizó el estado del envío del libro', 'ok'
                    );
                } else {
                    \sowerphp\core\Model_Datasource_Session::message(
                        'El estado del libro se obtuvo pero no fue posible guardarlo en la base de datos<br/>'.$status, 'error'
                    );
                }
                break;
            }
            // marcar email como leído
            $Imap->setSeen($uid);
        }
        // redireccionar
        $this->redirect(str_replace('actualizar_estado', 'ver', $this->request->request));
    }

    /**
     * Acción que permite subir un XML con el resultado de la revisión
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-12-26
     */
    public function subir_revision($periodo)
    {
        $Emisor = $this->getContribuyente();
        // obtener libro envíado
        $class = __NAMESPACE__.'\Model_Dte'.$this->config['model']['singular'];
        $Libro = new $class($Emisor->rut, (int)$periodo, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$Libro->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Aun no se ha generado el libro del período '.$periodo, 'error'
            );
            $this->redirect(str_replace('subir_revision', 'ver', $this->request->request));
        }
        // si no tiene track id error
        if (!$Libro->track_id) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Libro del período '.$periodo.' no tiene Track ID, primero debe enviarlo al SII', 'error'
            );
            $this->redirect(str_replace('subir_revision', 'ver', $this->request->request));
        }
        // si no se viene por post o el archivo no se subió o dió error
        if (!isset($_POST['submit']) or !isset($_FILES['xml']) or $_FILES['xml']['error']) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Debe subir archivo XML con la revisión', 'ok'
            );
            $this->redirect(str_replace('subir_revision', 'ver', $this->request->request).'#revision');
        }
        // guardar revisión
        $status = $Libro->saveRevision(file_get_contents($_FILES['xml']['tmp_name']));
        if ($status===true) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Se actualizó el estado del envío del libro', 'ok'
            );
        } else {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible guardar el estado del libro en la base de datos<br/>'.$status, 'error'
            );
        }
        $this->redirect(str_replace('subir_revision', 'ver', $this->request->request));
    }

    /**
     * Acción que genera la imagen del gráfico de barras de con los documentos
     * diarios del libro
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-01-07
     */
    public function grafico_documentos_diarios($periodo)
    {
        $Emisor = $this->getContribuyente();
        $detalle = $Emisor->{'get'.$this->config['model']['plural'].'Diarias'}($periodo);
        for ($dia=1; $dia<=31; $dia++) {
            if (!isset($detalle[$dia]))
                $detalle[$dia] = 0;
        }
        ksort($detalle);
        $chart = new \sowerphp\general\View_Helper_Chart();
        $chart->line(
            $this->config['model']['plural'].' diarias período '.$periodo,
            [$this->config['model']['plural']=>$detalle]
        );
    }

}
