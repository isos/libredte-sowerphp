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

// namespace del modelo
namespace website\Dte;

/**
 * Clase para mapear la tabla contribuyente de la base de datos
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2015-12-27
 */
class Model_Contribuyente extends \Model_App
{

    // Datos para la conexión a la base de datos
    protected $_database = 'default'; ///< Base de datos del modelo
    protected $_table = 'contribuyente'; ///< Tabla del modelo

    // Atributos de la clase (columnas en la base de datos)
    public $rut; ///< integer(32) NOT NULL DEFAULT '' PK
    public $dv; ///< character(1) NOT NULL DEFAULT ''
    public $razon_social; ///< character varying(100) NOT NULL DEFAULT ''
    public $giro; ///< character varying(80) NOT NULL DEFAULT ''
    public $actividad_economica; ///< integer(32) NULL DEFAULT '' FK:actividad_economica.codigo
    public $telefono; ///< character varying(20) NULL DEFAULT ''
    public $email; ///< character varying(80) NULL DEFAULT ''
    public $web; ///< character varying(80) NULL DEFAULT ''
    public $direccion; ///< character varying(70) NOT NULL DEFAULT ''
    public $comuna; ///< character(5) NOT NULL DEFAULT '' FK:comuna.codigo
    public $sucursal_sii; ///< integer(32) NULL DEFAULT ''
    public $resolucion_fecha; ///< date() NULL DEFAULT ''
    public $resolucion_numero; ///< smallint(16) NULL DEFAULT ''
    public $usuario; ///< integer(32) NULL DEFAULT '' FK:usuario.id
    public $certificacion; ///< boolean() NULL DEFAULT ''
    public $certificacion_resolucion; ///< date() NULL DEFAULT ''
    public $sii_smtp; ///< character varying(50) NULL DEFAULT ''
    public $sii_imap; ///< character varying(100) NULL DEFAULT ''
    public $sii_user; ///< character varying(50) NULL DEFAULT ''
    public $sii_pass; ///< character varying(255) NULL DEFAULT ''
    public $intercambio_smtp; ///< character varying(50) NULL DEFAULT ''
    public $intercambio_imap; ///< character varying(100) NULL DEFAULT ''
    public $intercambio_user; ///< character varying(50) NULL DEFAULT ''
    public $intercambio_pass; ///< character varying(255) NULL DEFAULT ''
    public $modificado; ///< timestamp without time zone() NOT NULL DEFAULT 'now()'
    public $api_token; ///< character varying(255) NULL DEFAULT ''
    public $api_items; ///< character varying(100) NULL DEFAULT ''

    // Información de las columnas de la tabla en la base de datos
    public static $columnsInfo = array(
        'rut' => array(
            'name'      => 'Rut',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => true,
            'fk'        => null
        ),
        'dv' => array(
            'name'      => 'Dv',
            'comment'   => '',
            'type'      => 'character',
            'length'    => 1,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'razon_social' => array(
            'name'      => 'Razon Social',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 100,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'giro' => array(
            'name'      => 'Giro',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 80,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'actividad_economica' => array(
            'name'      => 'Actividad Economica',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => array('table' => 'actividad_economica', 'column' => 'codigo')
        ),
        'telefono' => array(
            'name'      => 'Telefono',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 20,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'email' => array(
            'name'      => 'Email',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 80,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'web' => array(
            'name'      => 'Web',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 80,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'direccion' => array(
            'name'      => 'Direccion',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 70,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'comuna' => array(
            'name'      => 'Comuna',
            'comment'   => '',
            'type'      => 'character',
            'length'    => 5,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => array('table' => 'comuna', 'column' => 'codigo')
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
        'resolucion_fecha' => array(
            'name'      => 'Resolucion Fecha',
            'comment'   => '',
            'type'      => 'date',
            'length'    => null,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'resolucion_numero' => array(
            'name'      => 'Resolucion Numero',
            'comment'   => '',
            'type'      => 'smallint',
            'length'    => 16,
            'null'      => true,
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
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => array('table' => 'usuario', 'column' => 'id')
        ),
        'certificacion' => array(
            'name'      => 'Certificacion',
            'comment'   => '',
            'type'      => 'boolean',
            'length'    => null,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'certificacion_resolucion' => array(
            'name'      => 'Certificacion Resolucion',
            'comment'   => '',
            'type'      => 'date',
            'length'    => null,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'sii_smtp' => array(
            'name'      => 'Sii Smtp',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 50,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'sii_imap' => array(
            'name'      => 'Sii Imap',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 100,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'sii_user' => array(
            'name'      => 'Sii User',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 50,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'sii_pass' => array(
            'name'      => 'Sii Pass',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 255,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'intercambio_smtp' => array(
            'name'      => 'Intercambio Smtp',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 50,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'intercambio_imap' => array(
            'name'      => 'Intercambio Imap',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 100,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'intercambio_user' => array(
            'name'      => 'Intercambio User',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 50,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'intercambio_pass' => array(
            'name'      => 'Intercambio Pass',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 255,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'modificado' => array(
            'name'      => 'Modificado',
            'comment'   => '',
            'type'      => 'timestamp without time zone',
            'length'    => null,
            'null'      => false,
            'default'   => 'now()',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'api_token' => array(
            'name'      => 'Api Token',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 255,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'api_items' => array(
            'name'      => 'Api Items',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 100,
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
        'Model_ActividadEconomica' => '\website\Sistema\General',
        'Model_Comuna' => '\sowerphp\app\Sistema\General\DivisionGeopolitica',
        'Model_Usuario' => '\sowerphp\app\Sistema\Usuarios'
    ); ///< Namespaces que utiliza esta clase

    /**
     * Constructor del contribuyente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-12-12
     */
    public function __construct($rut = null)
    {
        if (!is_numeric($rut))
            $rut = explode('-', str_replace('.', '', $rut))[0];
        if (is_numeric($rut)) {
            parent::__construct($rut);
            $this->contribuyente = &$this->razon_social;
        }
    }

    /**
     * Método que entrega el RUT formateado del contribuyente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-20
     */
    public function getRUT()
    {
        return num($this->rut).'-'.$this->dv;
    }

    /**
     * Método que entrega la glosa del ambiente en el que se encuentra el
     * contribuyente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-23
     */
    public function getAmbiente()
    {
        return $this->certificacion ? 'certificación' : 'producción';
    }

    /**
     * Método que asigna los usuarios autorizados a operar con el contribuyente
     * @param usuarios Arreglo con índice nombre de usuario y valores un arreglo con los permisos a asignar
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-20
     */
    public function setUsuarios(array $usuarios) {
        $this->db->beginTransaction();
        $this->db->query(
            'DELETE FROM contribuyente_usuario WHERE contribuyente = :rut',
            [':rut'=>$this->rut]
        );
        foreach ($usuarios as $usuario => $permisos) {
            $Usuario = new \sowerphp\app\Sistema\Usuarios\Model_Usuario($usuario);
            if (!$Usuario->exists()) {
                $this->db->rollback();
                throw new \Exception('Usuario '.$usuario.' no existe');
                return false;
            }
            foreach ($permisos as $permiso) {
                $ContribuyenteUsuario = new Model_ContribuyenteUsuario($this->rut, $Usuario->id, $permiso);
                $ContribuyenteUsuario->save();
            }
        }
        $this->db->commit();
        return true;
    }

    /**
     * Método que entrega el listado de usuarios autorizados y sus permisos
     * @return Tabla con los usuarios y sus permisos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-20
     */
    public function getUsuarios()
    {
        return $this->db->getTable('
            SELECT u.usuario, c.permiso
            FROM usuario AS u, contribuyente_usuario AS c
            WHERE u.id = c.usuario AND c.contribuyente = :rut
        ', [':rut'=>$this->rut]);
    }

    /**
     * Método que determina si el usuario está o no autorizado a trabajar con el
     * contribuyente
     * @param usuario ID del usuario que se quiere saber si está autorizado
     * @return =true si está autorizado
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-20
     */
    public function usuarioAutorizado($usuario)
    {
        if ($usuario == $this->usuario)
            return true;
        return (bool)$this->db->getValue('
            SELECT COUNT(*)
            FROM contribuyente_usuario
            WHERE contribuyente = :rut AND usuario = :usuario
        ', [':rut'=>$this->rut, ':usuario'=>$usuario]);
    }

    /**
     * Método que entrega los documentos que el contribuyente tiene autorizado
     * a emitir en la aplicación
     * @return Listado de documentos autorizados
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-21
     */
    public function getDocumentosAutorizados()
    {
        return $this->db->getTable('
            SELECT t.codigo, t.tipo
            FROM dte_tipo AS t, contribuyente_dte AS c
            WHERE t.codigo = c.dte AND c.contribuyente = :rut
        ', [':rut'=>$this->rut]);
    }

    /**
     * Método que determina si el documento puede o no ser emitido por el
     * contribuyente a través de la aplicación
     * @param dte Código del DTE que se quiere saber si está autorizado
     * @return =true si está autorizado
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-21
     */
    public function documentoAutorizado($dte)
    {
        return (bool)$this->db->getValue('
            SELECT COUNT(*)
            FROM contribuyente_dte
            WHERE contribuyente = :rut AND dte = :dte
        ', [':rut'=>$this->rut, ':dte'=>$dte]);
    }

    /**
     * Método que entrega el listado de folios que el Contribuyente dispone
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-25
     */
    public function getFolios()
    {
        return $this->db->getTable('
            SELECT f.dte, t.tipo, f.siguiente, f.disponibles, f.alerta
            FROM dte_folio AS f, dte_tipo AS t
            WHERE f.dte = t.codigo AND emisor = :rut AND f.certificacion = :certificacion
            ORDER BY f.dte
        ', [':rut'=>$this->rut, ':certificacion'=>(int)$this->certificacion]);
    }

    /**
     * Método que entrega el folio siguiente del tipo de documento solicitado
     * para el ambiente que la empresa está operando
     * @param dte Tipo de documento para el cual se quiere su folio
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-22
     */
    public function getFolio($dte)
    {
        if (!$this->db->beginTransaction(true))
            return false;
        $DteFolio = new \website\Dte\Admin\Model_DteFolio($this->rut, $dte, (int)$this->certificacion);
        if (!$DteFolio->exists() or !$DteFolio->disponibles) {
            $this->db->rollback();
            return false;
        }
        $folio = $DteFolio->siguiente;
        $DteFolio->siguiente++;
        $DteFolio->disponibles--;
        try {
            if (!$DteFolio->save(false)) {
                $this->db->rollback();
                return false;
            }
        } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
            $this->db->rollback();
            return false;
        }
        $Caf = $this->getCaf($dte, $folio);
        if (!$Caf) {
            $this->db->rollback();
            return false;
        }
        $this->db->commit();
        return (object)[
            'folio' => $folio,
            'Caf' => $Caf,
            'DteFolio' => $DteFolio,
        ];
    }

    /**
     * Método que entrega el CAF de un folio de cierto DTE
     * @param dte Tipo de documento para el cual se quiere su CAF
     * @param folio Folio del CAF del DTE que se busca
     * @return \sasco\LibreDTE\Sii\Folios
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-22
     */
    private function getCaf($dte, $folio)
    {
        $caf = $this->db->getValue('
            SELECT xml
            FROM dte_caf
            WHERE
                emisor = :rut
                AND dte = :dte
                AND certificacion = :certificacion
                AND :folio BETWEEN desde AND hasta
        ', [
            ':rut' => $this->rut,
            ':dte' => $dte,
            ':certificacion' => (int)$this->certificacion,
            ':folio' => $folio,
        ]);
        if (!$caf)
            return false;
        $caf = Utility_Data::decrypt($caf);
        if (!$caf)
            return false;
        $Caf = new \sasco\LibreDTE\Sii\Folios($caf);
        return $Caf->getTipo() ? $Caf : false;
    }

    /**
     * Método que entrega una tabla con los datos de las firmas electrónicas de
     * los usuarios que están autorizados a trabajar con el contribuyente
     * @param dte Tipo de documento para el cual se quiere su folio
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-22
     */
    public function getFirmas()
    {
        return $this->db->getTable('
            (
                SELECT f.run, f.nombre, f.email, f.desde, f.hasta, f.emisor, u.usuario, true AS administrador
                FROM firma_electronica AS f, usuario AS u, contribuyente AS c
                WHERE f.usuario = u.id AND f.usuario = c.usuario AND c.rut = :rut
            ) UNION (
                SELECT f.run, f.nombre, f.email, f.desde, f.hasta, f.emisor, u.usuario, false AS administrador
                FROM firma_electronica AS f, usuario AS u, contribuyente_usuario AS c
                WHERE f.usuario = u.id AND f.usuario = c.usuario AND c.contribuyente = :rut
            )
            ORDER BY administrador DESC, nombre ASC
        ', [':rut'=>$this->rut]);
    }

    /**
     * Método que entrega el objeto de la firma electronica asociada al usuario
     * que la está solicitando o bien aquella firma del usuario que es el
     * administrador del contribuyente.
     * @param user ID del usuario que desea obtener la firma
     * @return \sasco\LibreDTE\FirmaElectronica
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-25
     */
    public function getFirma($user = null)
    {
        if (!$user)
            $user = $this->usuario;
        // buscar firma del usuario que está haciendo la solicitud
        $datos = $this->db->getRow('
            SELECT archivo, contrasenia
            FROM firma_electronica
            WHERE usuario = :usuario
        ', [':usuario'=>$user]);
        // buscar firma del usuario administrador de la empresa
        if (empty($datos) and $user!=$this->usuario) {
            $datos = $this->db->getRow('
                SELECT f.archivo, f.contrasenia
                FROM firma_electronica AS f, contribuyente AS c
                WHERE f.usuario = c.usuario AND c.rut = :rut
            ', [':rut'=>$this->rut]);
        }
        if (empty($datos))
            return false;
        // si se obtuvo una firma se trata de usar
        $pass = Utility_Data::decrypt($datos['contrasenia']);
        if (!$pass)
            return false;
        try {
            $Firma = new \sasco\LibreDTE\FirmaElectronica([
                'data' => base64_decode($datos['archivo']),
                'pass' => $pass,
            ]);
            return $Firma;
        } catch (\sowerphp\core\Exception $e) {
            return false;
        }
        return false;
    }

    /**
     * Método que entrega el listado de documentos emitidos por el contribuyente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-12-23
     */
    public function getDocumentosEmitidos($filtros = [])
    {
        $where = ['d.emisor = :rut'];
        $vars = [':rut'=>$this->rut];
        if (isset($filtros['certificacion'])) {
            $where[] = 'd.certificacion = :certificacion';
            $vars[':certificacion'] = $filtros['certificacion'];
        }
        return $this->db->getTable('
            SELECT d.dte, t.tipo, d.folio, r.razon_social, d.fecha, d.total, d.revision_estado AS estado, i.glosa AS intercambio, u.usuario
            FROM
                dte_emitido AS d LEFT JOIN dte_intercambio_resultado_dte AS i
                    ON i.emisor = d.emisor AND i.dte = d.dte AND i.folio = d.folio AND i.certificacion = d.certificacion,
                dte_tipo AS t,
                contribuyente AS r,
                usuario AS u
            WHERE d.dte = t.codigo AND d.receptor = r.rut AND d.usuario = u.id AND '.implode(' AND ', $where).'
            ORDER BY d.fecha DESC, t.tipo, r.razon_social
        ', $vars);
    }

    /**
     * Método que crea el objeto email para enviar por SMTP y lo entrega
     * @param email Email que se quiere obteber: intercambio o sii
     * @return \sowerphp\core\Network_Email
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-25
     */
    public function getEmailSmtp($email = 'intercambio')
    {
        return new \sowerphp\core\Network_Email([
            'type' => 'smtp',
            'host' => $this->{$email.'_smtp'},
            'user' => $this->{$email.'_user'},
            'pass' => Utility_Data::decrypt($this->{$email.'_pass'}),
            'from' => ['email'=>$this->{$email.'_user'}, 'name'=>$this->razon_social],
        ]);
    }

    /**
     * Método que crea el objeto Imap para recibir correo por IMAP
     * @param email Email que se quiere obteber: intercambio o sii
     * @return \sowerphp\core\Network_Email_Imap
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-25
     */
    public function getEmailImap($email = 'intercambio')
    {
        $Imap = new \sowerphp\core\Network_Email_Imap([
            'mailbox' => $this->{$email.'_imap'},
            'user' => $this->{$email.'_user'},
            'pass' => Utility_Data::decrypt($this->{$email.'_pass'}),
        ]);
        return $Imap->isConnected() ? $Imap : false;
    }

    /**
     * Método que entrega el resumen de las ventas por períodos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-11-03
     */
    public function getResumenVentasPeriodos()
    {
        $periodo = $this->db->config['type']=='PostgreSQL' ? 'TO_CHAR(e.fecha, \'YYYYmm\')::INTEGER' : 'DATE_FORMAT(e.fecha, "%Y%m")';
        return $this->db->getTable('
            (
                SELECT '.$periodo.' AS periodo, COUNT(*) AS emitidos, v.documentos AS enviados, v.track_id, v.revision_estado
                FROM dte_tipo AS t, dte_emitido AS e LEFT JOIN dte_venta AS v ON e.emisor = v.emisor AND e.certificacion = v.certificacion AND '.$periodo.' = v.periodo
                WHERE t.codigo = e.dte AND t.venta = true AND e.emisor = :rut AND e.certificacion = :certificacion
                GROUP BY '.$periodo.', enviados, v.track_id, v.revision_estado
            ) UNION (
                SELECT periodo, documentos AS emitidos, documentos AS enviados, track_id, revision_estado
                FROM dte_venta
                WHERE emisor = :rut AND certificacion = :certificacion
            )
            ORDER BY periodo DESC
        ', [':rut'=>$this->rut, ':certificacion'=>(int)$this->certificacion]);
    }

    /**
     * Método que entrega el resumen de las ventas de un período
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-25
     */
    public function getVentas($periodo)
    {
        $periodo_col = $this->db->config['type']=='PostgreSQL' ? 'TO_CHAR(e.fecha, \'YYYYmm\')' : 'DATE_FORMAT(e.fecha, "%Y%m")';
        return $this->db->getTable('
            SELECT e.dte, e.folio, e.tasa, e.fecha, e.sucursal_sii, '.$this->db->concat('r.rut', '-', 'r.dv').' AS rut, r.razon_social, e.exento, e.neto, e.iva, \'\' AS impuesto_codigo, \'\' AS impuesto_tasa, \'\' AS impuesto_monto, e.total
            FROM dte_tipo AS t, dte_emitido AS e, contribuyente AS r
            WHERE t.codigo = e.dte AND t.venta = true AND e.receptor = r.rut AND e.emisor = :rut AND e.certificacion = :certificacion AND '.$periodo_col.' = :periodo
            ORDER BY e.fecha, e.dte, e.folio
        ', [':rut'=>$this->rut, ':certificacion'=>(int)$this->certificacion, ':periodo'=>$periodo]);
    }

    /**
     * Método que entrega el resumen de las ventas diarias de un período
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-26
     */
    public function getVentasDiarias($periodo)
    {
        $periodo_col = $this->db->config['type']=='PostgreSQL' ? 'TO_CHAR(e.fecha, \'YYYYmm\')' : 'DATE_FORMAT(e.fecha, "%Y%m")';
        return $this->db->getTable('
            SELECT e.fecha, COUNT(*) AS ventas
            FROM dte_tipo AS t, dte_emitido AS e
            WHERE t.codigo = e.dte AND t.venta = true AND e.emisor = :rut AND e.certificacion = :certificacion AND '.$periodo_col.' = :periodo
            GROUP BY e.fecha
        ', [':rut'=>$this->rut, ':certificacion'=>(int)$this->certificacion, ':periodo'=>$periodo]);
    }

    /**
     * Método que entrega el resumen de ventas por tipo de un período
     * @return Arreglo asociativo con las ventas
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-26
     */
    public function getVentasPorTipo($periodo)
    {
        $periodo_col = $this->db->config['type']=='PostgreSQL' ? 'TO_CHAR(e.fecha, \'YYYYmm\')' : 'DATE_FORMAT(e.fecha, "%Y%m")';
        return $this->db->getAssociativeArray('
            SELECT t.tipo, COUNT(*) AS ventas
            FROM dte_tipo AS t, dte_emitido AS e
            WHERE t.codigo = e.dte AND t.venta = true AND e.emisor = :rut AND e.certificacion = :certificacion AND '.$periodo_col.' = :periodo
            GROUP BY t.tipo
        ', [':rut'=>$this->rut, ':certificacion'=>(int)$this->certificacion, ':periodo'=>$periodo]);
    }

    /**
     * Método que entrega el resumen de las guías por períodos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-12-26
     */
    public function getResumenGuiasPeriodos()
    {
        $periodo = $this->db->config['type']=='PostgreSQL' ? 'TO_CHAR(e.fecha, \'YYYYmm\')::INTEGER' : 'DATE_FORMAT(e.fecha, "%Y%m")';
        return $this->db->getTable('
            (
                SELECT '.$periodo.' AS periodo, COUNT(*) AS emitidos, g.documentos AS enviados, g.track_id, g.revision_estado
                FROM dte_emitido AS e LEFT JOIN dte_guia AS g ON e.emisor = g.emisor AND e.certificacion = g.certificacion AND '.$periodo.' = g.periodo
                WHERE e.emisor = :rut AND e.certificacion = :certificacion AND e.dte = 52
                GROUP BY '.$periodo.', enviados, g.track_id, g.revision_estado
            ) UNION (
                SELECT periodo, documentos AS emitidos, documentos AS enviados, track_id, revision_estado
                FROM dte_guia
                WHERE emisor = :rut AND certificacion = :certificacion
            )
            ORDER BY periodo DESC
        ', [':rut'=>$this->rut, ':certificacion'=>(int)$this->certificacion]);
    }

    /**
     * Método que entrega el resumen de las guías de un período
     * @todo Extraer IndTraslado en MariaDB
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-12-27
     */
    public function getGuias($periodo)
    {
        if ($this->db->config['type']=='PostgreSQL') {
            $periodo_col = 'TO_CHAR(e.fecha, \'YYYYmm\')';
            $tipo_col = 'BTRIM(XPATH(\'/n:EnvioDTE/n:SetDTE/n:DTE/n:Documento/n:Encabezado/n:IdDoc/n:IndTraslado/text()\', CONVERT_FROM(decode(e.xml, \'base64\'), \'ISO8859-1\')::XML, \'{{n,http://www.sii.cl/SiiDte}}\')::TEXT, \'{}\')';
        } else {
            $periodo_col = 'DATE_FORMAT(e.fecha, "%Y%m")';
            //$tipo_col = 'ExtractValue(, \'\')';
            $tipo_col = '\'\'';
        }
        return $this->db->getTable('
            SELECT
                e.folio,
                NULL AS anulado,
                1 AS operacion,
                '.$tipo_col.' AS tipo,
                e.fecha,
                '.$this->db->concat('r.rut', '-', 'r.dv').' AS rut,
                r.razon_social,
                e.neto,
                e.tasa,
                e.iva,
                e.total,
                NULL AS modificado,
                ref.dte AS ref_dte,
                ref.folio AS ref_folio,
                re.fecha AS ref_fecha
            FROM
                dte_emitido AS e
                LEFT JOIN dte_referencia AS ref ON e.dte = ref.referencia_dte AND e.folio = ref.referencia_folio AND e.certificacion = ref.certificacion
                LEFT JOIN dte_emitido AS re ON re.dte = ref.dte AND re.folio = ref.folio AND re.certificacion = ref.certificacion,
                contribuyente AS r
            WHERE e.receptor = r.rut AND e.emisor = :rut AND e.certificacion = :certificacion AND '.$periodo_col.' = :periodo AND e.dte = 52
            ORDER BY e.fecha, e.folio
        ', [':rut'=>$this->rut, ':certificacion'=>(int)$this->certificacion, ':periodo'=>$periodo]);
    }

    /**
     * Método que entrega el resumen de las guías diarias de un período
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-12-27
     */
    public function getGuiasDiarias($periodo)
    {
        $periodo_col = $this->db->config['type']=='PostgreSQL' ? 'TO_CHAR(fecha, \'YYYYmm\')' : 'DATE_FORMAT(fecha, "%Y%m")';
        return $this->db->getTable('
            SELECT fecha, COUNT(*) AS guias
            FROM dte_emitido
            WHERE emisor = :rut AND certificacion = :certificacion AND '.$periodo_col.' = :periodo AND dte = 52
            GROUP BY fecha
        ', [':rut'=>$this->rut, ':certificacion'=>(int)$this->certificacion, ':periodo'=>$periodo]);
    }

    /**
     * Método que entrega la tabla con los casos de intercambio del contribuyente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-28
     */
    public function getIntercambios()
    {
        $intercambios = $this->db->getTable('
            SELECT i.codigo, i.fecha_hora_email, i.de, i.emisor, e.razon_social, i.documentos, i.estado, u.usuario
            FROM dte_intercambio AS i LEFT JOIN contribuyente AS e ON i.emisor = e.rut LEFT JOIN usuario AS u ON i.usuario = u.id
            WHERE i.receptor = :receptor AND i.certificacion = :certificacion
            ORDER BY i.fecha_hora_email DESC
        ', [':receptor'=>$this->rut, ':certificacion'=>(int)$this->certificacion]);
        foreach ($intercambios as &$i) {
            if (!empty($i['razon_social']))
                $i['emisor']= $i['razon_social'];
            if (isset($i['estado']))
                $i['estado'] = \sasco\LibreDTE\Sii\RespuestaEnvio::$estados['envio'][$i['estado']];
            unset($i['razon_social']);
        }
        return $intercambios;
    }

    /**
     * Método que entrega el listado de documentos recibidos por el contribuyente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-28
     */
    public function getDocumentosRecibidos($filtros = [])
    {
        $where = ['d.receptor = :rut'];
        $vars = [':rut'=>$this->rut];
        if (isset($filtros['certificacion'])) {
            $where[] = 'd.certificacion = :certificacion';
            $vars[':certificacion'] = $filtros['certificacion'];
        }
        return $this->db->getTable('
            SELECT d.dte, t.tipo, d.folio, e.razon_social, d.fecha, d.total, d.intercambio, u.usuario, d.emisor
            FROM dte_recibido AS d, dte_tipo AS t, contribuyente AS e, usuario AS u
            WHERE d.dte = t.codigo AND d.emisor = e.rut AND d.usuario = u.id AND '.implode(' AND ', $where).'
            ORDER BY d.fecha DESC, t.tipo, e.razon_social
        ', $vars);
    }

    /**
     * Método que entrega el resumen de las compras por períodos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-11-03
     */
    public function getResumenComprasPeriodos()
    {
        $periodo = $this->db->config['type']=='PostgreSQL' ? 'TO_CHAR(r.fecha, \'YYYYmm\')::INTEGER' : 'DATE_FORMAT(r.fecha, "%Y%m")';
        return $this->db->getTable('
            (
                SELECT '.$periodo.' AS periodo, COUNT(*) AS recibidos, c.documentos AS enviados, c.track_id, c.revision_estado
                FROM dte_tipo AS t, dte_recibido AS r LEFT JOIN dte_compra AS c ON r.receptor = c.receptor AND r.certificacion = c.certificacion AND '.$periodo.' = c.periodo
                WHERE t.codigo = r.dte AND t.compra = true AND r.receptor = :rut AND r.certificacion = :certificacion
                GROUP BY '.$periodo.', enviados, c.track_id, c.revision_estado
            ) UNION (
                SELECT periodo, documentos AS emitidos, documentos AS enviados, track_id, revision_estado
                FROM dte_compra
                WHERE receptor = :rut AND certificacion = :certificacion
            )
            ORDER BY periodo DESC
        ', [':rut'=>$this->rut, ':certificacion'=>(int)$this->certificacion]);
    }

    /**
     * Método que entrega el resumen de las compras de un período
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-29
     */
    public function getCompras($periodo)
    {
        $periodo_col = $this->db->config['type']=='PostgreSQL' ? 'TO_CHAR(r.fecha, \'YYYYmm\')' : 'DATE_FORMAT(r.fecha, "%Y%m")';
        $compras = $this->db->getTable('
            SELECT
                r.dte,
                r.folio,
                '.$this->db->concat('e.rut', '-', 'e.dv').' AS rut,
                r.tasa,
                e.razon_social,
                r.impuesto_tipo,
                r.fecha,
                r.anulado,
                r.exento,
                r.neto,
                r.iva,
                r.iva_no_recuperable,
                NULL AS iva_no_recuperable_monto,
                NULL AS iva_uso_comun_monto,
                r.iva_uso_comun,
                r.impuesto_adicional,
                r.impuesto_adicional_tasa,
                NULL AS impuesto_adicional_monto,
                r.total,
                r.impuesto_sin_credito,
                r.monto_activo_fijo,
                r.monto_iva_activo_fijo,
                r.iva_no_retenido,
                r.sucursal_sii
            FROM dte_tipo AS t, dte_recibido AS r, contribuyente AS e
            WHERE t.codigo = r.dte AND t.compra = true AND r.emisor = e.rut AND r.receptor = :rut AND r.certificacion = :certificacion AND '.$periodo_col.' = :periodo
            ORDER BY r.fecha, r.dte, r.folio
        ', [':rut'=>$this->rut, ':certificacion'=>(int)$this->certificacion, ':periodo'=>$periodo]);
        foreach ($compras as &$c) {
            // asignar IVA no recuperable
            if (!empty($c['iva_no_recuperable'])) {
                $c['iva_no_recuperable_monto'] = round((int)$c['neto']*($c['tasa']/100));
                $c['iva'] = 0;
            }
            // asignar IVA de uso comun
            if (!empty($c['iva_uso_comun'])) {
                $c['iva_uso_comun_monto'] = round((int)$c['neto']*($c['tasa']/100));
            }
            // asignar monto de impuesto adicionl
            if (!empty($c['impuesto_adicional'])) {
                $c['impuesto_adicional_monto'] = round((int)$c['neto']*($c['impuesto_adicional_tasa']/100));
            }
        }
        return $compras;
    }

    /**
     * Método que entrega el resumen de las compras diarias de un período
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-28
     */
    public function getComprasDiarias($periodo)
    {
        $periodo_col = $this->db->config['type']=='PostgreSQL' ? 'TO_CHAR(r.fecha, \'YYYYmm\')' : 'DATE_FORMAT(r.fecha, "%Y%m")';
        return $this->db->getTable('
            SELECT r.fecha, COUNT(*) AS compras
            FROM dte_tipo AS t, dte_recibido AS r
            WHERE t.codigo = r.dte AND t.compra = true AND r.receptor = :rut AND r.certificacion = :certificacion AND '.$periodo_col.' = :periodo
            GROUP BY r.fecha
        ', [':rut'=>$this->rut, ':certificacion'=>(int)$this->certificacion, ':periodo'=>$periodo]);
    }

    /**
     * Método que entrega el resumen de comras por tipo de un período
     * @return Arreglo asociativo con las compras
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-28
     */
    public function getComprasPorTipo($periodo)
    {
        $periodo_col = $this->db->config['type']=='PostgreSQL' ? 'TO_CHAR(r.fecha, \'YYYYmm\')' : 'DATE_FORMAT(r.fecha, "%Y%m")';
        return $this->db->getAssociativeArray('
            SELECT t.tipo, COUNT(*) AS compras
            FROM dte_tipo AS t, dte_recibido AS r
            WHERE t.codigo = r.dte AND t.compra = true AND r.receptor = :rut AND r.certificacion = :certificacion AND '.$periodo_col.' = :periodo
            GROUP BY t.tipo
        ', [':rut'=>$this->rut, ':certificacion'=>(int)$this->certificacion, ':periodo'=>$periodo]);
    }

}
