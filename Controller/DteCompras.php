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
 * @version 2015-09-28
 */
class Controller_DteCompras extends \Controller_App
{

    private $compras_cols = [
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
        'total' => 'MntTotal'
    ];

    /**
     * Acción que muestra el resumen de los períodos con las compras
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-28
     */
    public function index()
    {
        $Emisor = \sowerphp\core\Model_Datasource_Session::read('dte.Emisor');
        $this->set([
            'periodos' => $Emisor->getResumenComprasPeriodos(),
        ]);
    }

    /**
     * Acción que muestra la información del libro de compras de un período
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-11-03
     */
    public function ver($periodo)
    {
        $Emisor = \sowerphp\core\Model_Datasource_Session::read('dte.Emisor');
        $compras = $Emisor->getCompras($periodo);
        $DteCompra = new Model_DteCompra($Emisor->rut, (int)$periodo, (int)$Emisor->certificacion);
        if (!$compras and !$DteCompra->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No hay compras en el período '.$periodo, 'error'
            );
            $this->redirect('/dte/dte_compras');
        }
        $this->set([
            'Emisor' => $Emisor,
            'DteCompra' => $DteCompra,
            'compras' => $compras,
            'compras_cols' => $this->compras_cols,
        ]);
    }

    /**
     * Acción que descarga los datos de compras del período en un archivo CSV
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-28
     */
    public function csv($periodo)
    {
        $Emisor = \sowerphp\core\Model_Datasource_Session::read('dte.Emisor');
        $compras = $Emisor->getCompras($periodo);
        if (!$compras) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No hay compras en el período '.$periodo, 'error'
            );
            $this->redirect('/dte/dte_compras');
        }
        array_unshift($compras, $this->compras_cols);
        \sowerphp\general\Utility_Spreadsheet_CSV::generate($compras, 'compras_'.$Emisor->rut.'-'.$Emisor->dv.'_'.$periodo);
    }

    /**
     * Acción que descarga el archivo XML del libro de compras
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-28
     */
    public function xml($periodo)
    {
        $Emisor = \sowerphp\core\Model_Datasource_Session::read('dte.Emisor');
        // crear objeto del libro de compra
        $DteCompra = new Model_DteCompra($Emisor->rut, $periodo, (int)$Emisor->certificacion);
        if (!$DteCompra->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Aun no se ha generado el XML del período '.$periodo, 'error'
            );
            $this->redirect(str_replace('xml', 'ver', $this->request->request));
        }
        // entregar XML
        $file = 'compras_'.$Emisor->rut.'-'.$Emisor->dv.'_'.$periodo.'.xml';
        $xml = base64_decode($DteCompra->xml);
        header('Content-Type: application/xml; charset=ISO-8859-1');
        header('Content-Length: '.strlen($xml));
        header('Content-Disposition: attachement; filename="'.$file.'"');
        print $xml;
        exit;
    }

    /**
     * Acción que envía el archivo XML del libro de compras al SII
     * Si no hay documentos en el período se enviará sin movimientos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-10-27
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
                        $d[$this->compras_cols[$k]] = $v;
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
            'FchResol' => '2006-01-20',
            'NroResol' => 102006,
            'TipoOperacion' => 'COMPRA',
            'TipoLibro' => 'ESPECIAL',
            'TipoEnvio' => 'TOTAL',
            'FolioNotificacion' => 102006,
        ]);
        // obtener XML
        //if (!$Emisor->certificacion)
            //$Libro->setFirma($Firma);
        $xml = $Libro->generar();
        //if ($Emisor->certificacion)
            $Libro->setFirma($Firma);
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
     * Acción para enviar el libro de compras de un período sin movimientos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-10-28
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
            $this->redirect('/dte/dte_compras/enviar_sii/'.$periodo);
        }
    }

    /**
     * Acción que solicita se envíe una nueva revisión del libro de compras al email
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-28
     */
    public function solicitar_revision($periodo)
    {
        $Emisor = \sowerphp\core\Model_Datasource_Session::read('dte.Emisor');
        // obtener libro de compras envíado
        $DteCompra = new Model_DteCompra($Emisor->rut, $periodo, (int)$Emisor->certificacion);
        if (!$DteCompra->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Aun no se ha generado el libro de compras del período '.$periodo, 'error'
            );
            $this->redirect(str_replace('solicitar_revision', 'ver', $this->request->request));
        }
        // si no tiene track id error
        if (!$DteCompra->track_id) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Libro de compras del período '.$periodo.' no tiene Track ID, primero debe enviarlo al SII', 'error'
            );
            $this->redirect(str_replace('solicitar_revision', 'ver', $this->request->request));
        }
        // obtener firma
        $Firma = $Emisor->getFirma($this->Auth->User->id);
        if (!$Firma) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No hay firma electrónica asociada a la empresa (o bien no se pudo cargar), debe agregar su firma antes de generar DTE', 'error'
            );
            $this->redirect('/dte/admin/firma_electronicas');
        }
        // obtener token
        $token = \sasco\LibreDTE\Sii\Autenticacion::getToken($Firma);
        if (!$token) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible obtener el token para el SII<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'error'
            );
            $this->redirect(str_replace('solicitar_revision', 'ver', $this->request->request));
        }
        // solicitar envío de nueva revisión
        $estado = \sasco\LibreDTE\Sii::request('wsDTECorreo', 'reenvioCorreo', [$token, $Emisor->rut, $Emisor->dv, $DteCompra->track_id]);
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
     * @version 2015-09-28
     */
    public function actualizar_estado($periodo)
    {
        $Emisor = \sowerphp\core\Model_Datasource_Session::read('dte.Emisor');
        // obtener libro de compras envíado
        $DteCompra = new Model_DteCompra($Emisor->rut, $periodo, (int)$Emisor->certificacion);
        if (!$DteCompra->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Aun no se ha generado el libro de compras del período '.$periodo, 'error'
            );
            $this->redirect(str_replace('actualizar_estado', 'ver', $this->request->request));
        }
        // si no tiene track id error
        if (!$DteCompra->track_id) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Libro de compras del período '.$periodo.' no tiene Track ID, primero debe enviarlo al SII', 'error'
            );
            $this->redirect(str_replace('actualizar_estado', 'ver', $this->request->request));
        }
        // buscar correo con respuesta
        $Imap = $Emisor->getEmailImap('sii');
        if (!$Imap) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible conectar mediante IMAP a '.$Emisor->sii_imap.', verificar mailbox, usuario y/o contraseña de contacto SII', 'error'
            );
            $this->redirect(str_replace('actualizar_estado', 'ver', $this->request->request));
        }
        $asunto = 'Revision Envio de Libro Simplificado '.$DteCompra->track_id.' - '.$Emisor->rut.'-'.$Emisor->dv;
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
                $status = $DteCompra->saveRevision($file['data']);
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
     * @version 2015-09-28
     */
    public function subir_revision($periodo)
    {
        $Emisor = \sowerphp\core\Model_Datasource_Session::read('dte.Emisor');
        // obtener libro de compras envíado
        $DteCompra = new Model_DteCompra($Emisor->rut, $periodo, (int)$Emisor->certificacion);
        if (!$DteCompra->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Aun no se ha generado el libro de compras del período '.$periodo, 'error'
            );
            $this->redirect(str_replace('subir_revision', 'ver', $this->request->request));
        }
        // si no tiene track id error
        if (!$DteCompra->track_id) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Libro de compras del período '.$periodo.' no tiene Track ID, primero debe enviarlo al SII', 'error'
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
        $status = $DteCompra->saveRevision(file_get_contents($_FILES['xml']['tmp_name']));
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
     * Acción que genera la imagen del gráfico de barras de compras
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-28
     */
    public function grafico_compras_diarias($periodo)
    {
        $Emisor = \sowerphp\core\Model_Datasource_Session::read('dte.Emisor');
        $compras = $Emisor->getComprasDiarias($periodo);
        $chart = new \sowerphp\general\View_Helper_Chart();
        $chart->vertical_bar('Compras diarias período '.$periodo, ['Compras'=>$compras]);
    }

    /**
     * Acción que genera la imagen del gráfico de torta de compras
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-28
     */
    public function grafico_compras_tipo($periodo)
    {
        $Emisor = \sowerphp\core\Model_Datasource_Session::read('dte.Emisor');
        $compras = $Emisor->getComprasPorTipo($periodo);
        $chart = new \sowerphp\general\View_Helper_Chart();
        $chart->pie('Compras por tipo de DTE del período '.$periodo, $compras);
    }

}
