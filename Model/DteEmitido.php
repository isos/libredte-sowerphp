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

// namespace del modelo
namespace website\Dte;

/**
 * Clase para mapear la tabla dte_emitido de la base de datos
 * Comentario de la tabla:
 * Esta clase permite trabajar sobre un registro de la tabla dte_emitido
 * @author SowerPHP Code Generator
 * @version 2015-09-23 11:44:17
 */
class Model_DteEmitido extends \Model_App
{

    // Datos para la conexión a la base de datos
    protected $_database = 'default'; ///< Base de datos del modelo
    protected $_table = 'dte_emitido'; ///< Tabla del modelo

    // Atributos de la clase (columnas en la base de datos)
    public $emisor; ///< integer(32) NOT NULL DEFAULT '' PK FK:contribuyente.rut
    public $dte; ///< smallint(16) NOT NULL DEFAULT '' PK FK:dte_tipo.codigo
    public $folio; ///< integer(32) NOT NULL DEFAULT '' PK
    public $certificacion; ///< boolean() NOT NULL DEFAULT 'false' PK
    public $tasa; ///< smallint(16) NOT NULL DEFAULT '0'
    public $fecha; ///< date() NOT NULL DEFAULT ''
    public $sucursal_sii; ///< integer(32) NULL DEFAULT ''
    public $receptor; ///< integer(32) NOT NULL DEFAULT '' FK:contribuyente.rut
    public $exento; ///< integer(32) NULL DEFAULT ''
    public $neto; ///< integer(32) NULL DEFAULT ''
    public $iva; ///< integer(32) NOT NULL DEFAULT '0'
    public $total; ///< integer(32) NOT NULL DEFAULT ''
    public $usuario; ///< integer(32) NOT NULL DEFAULT '' FK:usuario.id
    public $xml; ///< text() NOT NULL DEFAULT ''
    public $track_id; ///< integer(32) NULL DEFAULT ''
    public $revision_estado; ///< character varying(100) NULL DEFAULT ''
    public $revision_detalle; ///< character text() NULL DEFAULT ''

    // Información de las columnas de la tabla en la base de datos
    public static $columnsInfo = array(
        'emisor' => array(
            'name'      => 'Emisor',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => true,
            'fk'        => array('table' => 'contribuyente', 'column' => 'rut')
        ),
        'dte' => array(
            'name'      => 'Dte',
            'comment'   => '',
            'type'      => 'smallint',
            'length'    => 16,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => true,
            'fk'        => array('table' => 'dte_tipo', 'column' => 'codigo')
        ),
        'folio' => array(
            'name'      => 'Folio',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => true,
            'fk'        => null
        ),
        'certificacion' => array(
            'name'      => 'Certificacion',
            'comment'   => '',
            'type'      => 'boolean',
            'length'    => null,
            'null'      => false,
            'default'   => 'false',
            'auto'      => false,
            'pk'        => true,
            'fk'        => null
        ),
        'tasa' => array(
            'name'      => 'Tasa',
            'comment'   => '',
            'type'      => 'smallint',
            'length'    => 16,
            'null'      => false,
            'default'   => '0',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'fecha' => array(
            'name'      => 'Fecha',
            'comment'   => '',
            'type'      => 'date',
            'length'    => null,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'sucursal_sii' => array(
            'name'      => 'Sucursal Sii',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'receptor' => array(
            'name'      => 'Receptor',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => array('table' => 'contribuyente', 'column' => 'rut')
        ),
        'exento' => array(
            'name'      => 'Exento',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'neto' => array(
            'name'      => 'Neto',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'iva' => array(
            'name'      => 'Iva',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '0',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'total' => array(
            'name'      => 'Total',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'usuario' => array(
            'name'      => 'Usuario',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => array('table' => 'usuario', 'column' => 'id')
        ),
        'xml' => array(
            'name'      => 'Xml',
            'comment'   => '',
            'type'      => 'text',
            'length'    => null,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'track_id' => array(
            'name'      => 'Track Id',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'revision_estado' => array(
            'name'      => 'Revision Estado',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 100,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'revision_detalle' => array(
            'name'      => 'Revision Detalle',
            'comment'   => '',
            'type'      => 'text',
            'length'    => null,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),

    );

    // Comentario de la tabla en la base de datos
    public static $tableComment = '';

    public static $fkNamespace = array(
        'Model_DteTipo' => 'website\Dte\Admin',
        'Model_Contribuyente' => 'website\Dte',
        'Model_Usuario' => '\sowerphp\app\Sistema\Usuarios'
    ); ///< Namespaces que utiliza esta clase

    private $datos; ///< Arreglo con los datos del XML del DTE

    /**
     * Método que entrega el objeto del tipo del dte
     * @return \website\Dte\Admin\Model_DteTipo
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-23
     */
    public function getTipo()
    {
        return (new \website\Dte\Admin\Model_DteTipos())->get($this->dte);
    }

    /**
     * Método que entrega el objeto del receptor del dte
     * @return \website\Dte\Model_Contribuyente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-23
     */
    public function getReceptor()
    {
        return (new \website\Dte\Model_Contribuyentes())->get($this->receptor);
    }

    /**
     * Método que entrega el arreglo con los datos que se usaron para generar el
     * XML del DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-24
     */
    public function getDatos()
    {
        if (!$this->datos) {
            $EnvioDte = new \sasco\LibreDTE\Sii\EnvioDte();
            $EnvioDte->loadXML(base64_decode($this->xml));
            $datos = $EnvioDte->getDocumentos()[0]->getDatos();
        }
        return $datos;
    }

    /**
     * Método que entrega el listado de correos a los que se debería enviar el
     * DTE (correo receptor, correo intercambio y correo del dte)
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-24
     */
    public function getEmails()
    {
        $aux = [
            'Email receptor' => $this->getReceptor()->email,
            'Email intercambio' => $this->getReceptor()->config_email_intercambio_user,
            'DTE T'.$this->dte.'F'.$this->folio => isset($this->getDatos()['Encabezado']['Receptor']['CorreoRecep']) ? $this->getDatos()['Encabezado']['Receptor']['CorreoRecep'] : false,
        ];
        $emails = [];
        foreach ($aux as $k => $e) {
            if (!empty($e) and !in_array($e, $emails)) {
                $emails[$k] = $e;
            }
        }
        return $emails ? $emails : false;
    }

    /**
     * Método que entrega las referencias que existen a este DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-12-27
     */
    public function getReferencias()
    {
        return $this->db->getTable('
            SELECT t.tipo AS documento_tipo, r.folio, d.fecha, rt.tipo AS referencia_tipo, r.razon, r.dte
            FROM dte_referencia AS r LEFT JOIN dte_referencia_tipo AS rt ON r.codigo = rt.codigo, dte_tipo AS t, dte_emitido AS d
            WHERE
                r.dte = t.codigo
                AND d.dte = r.dte
                AND d.folio = r.folio
                AND r.emisor = :rut
                AND r.certificacion = :certificacion
                AND r.referencia_dte = :dte
                AND r.referencia_folio = :folio
            ORDER BY fecha DESC, t.tipo ASC, r.folio DESC
        ', [':rut'=>$this->emisor, ':dte'=>$this->dte, ':folio'=>$this->folio, ':certificacion'=>(int)$this->certificacion]);
    }

    /**
     * Método que entrega del intercambio el objeto del Recibo del DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-12-23
     */
    public function getIntercambioRecibo()
    {
        $Recibo = new Model_DteIntercambioReciboDte(
            $this->emisor, $this->dte, $this->folio, $this->certificacion
        );
        return $Recibo->exists() ? $Recibo : false;
    }

    /**
     * Método que entrega del intercambio el objeto de la Recepcion del DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-12-23
     */
    public function getIntercambioRecepcion()
    {
        $Recepcion = new Model_DteIntercambioRecepcionDte(
            $this->emisor, $this->dte, $this->folio, $this->certificacion
        );
        return $Recepcion->exists() ? $Recepcion : false;
    }

    /**
     * Método que entrega del intercambio el objeto del Resultado del DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-12-23
     */
    public function getIntercambioResultado()
    {
        $Resultado = new Model_DteIntercambioResultadoDte(
            $this->emisor, $this->dte, $this->folio, $this->certificacion
        );
        return $Resultado->exists() ? $Resultado : false;
    }

    /**
     * Método que entrega el estado del envío del DTE al SII
     * @return R: si es RSC, RCT, RCH, =null otros casos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-03-17
     */
    public function getEstado()
    {
        $espacio = strpos($this->revision_estado, ' ');
        $estado = $espacio ? substr($this->revision_estado, 0, $espacio) : $this->revision_estado;
        return in_array($estado, ['RSC', 'RCT', 'RCH']) ? 'R' : null;
    }

    /**
     * Método que elimina el DTE, y si no hay DTE posterior del mismo tipo,
     * restaura el folio para que se volver a utilizar.
     * Sólo se pueden eliminar DTE que estén rechazados
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-03-17
     */
    public function delete()
    {
        if ($this->getEstado()!='R')
            return false;
        $this->db->beginTransaction(true);
        $DteFolio = new \website\Dte\Admin\Model_DteFolio($this->emisor, $this->dte, (int)$this->certificacion);
        if ($DteFolio->siguiente == ($this->folio+1)) {
            $DteFolio->siguiente--;
            $DteFolio->disponibles++;
            try {
                if (!$DteFolio->save(false)) {
                    $this->db->rollback();
                    return false;
                }
            } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
                $this->db->rollback();
                return false;
            }
        }
        debug('delete');
        if (!parent::delete()) {
            $this->db->rollback();
            return false;
        }
        $this->db->commit();
        return true;
    }

}
