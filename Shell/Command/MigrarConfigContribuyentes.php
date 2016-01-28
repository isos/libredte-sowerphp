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

namespace website\Dte;

/**
 * Comando para migrar las configuraciones de los contribuyentes a nuevo esquema
 * que utiliza la tabla: contribuyente_config
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2016-01-28
 */
class Shell_Command_MigrarConfigContribuyentes extends \Shell_App
{

    private $columnas = [
        'web' => 'extra_web',
        'resolucion_fecha' => 'ambiente_produccion_fecha',
        'resolucion_numero' => 'ambiente_produccion_numero',
        'certificacion' => 'ambiente_en_certificacion',
        'certificacion_resolucion' => 'ambiente_certificacion_fecha',
        'sii_smtp' => 'email_sii_smtp',
        'sii_imap' => 'email_sii_imap',
        'sii_user' => 'email_sii_user',
        'sii_pass' => 'email_sii_pass',
        'intercambio_smtp' => 'email_intercambio_smtp',
        'intercambio_imap' => 'email_intercambio_imap',
        'intercambio_user' => 'email_intercambio_user',
        'intercambio_pass' => 'email_intercambio_pass',
        'api_token' => 'api_auth_token',
        'api_items' => 'api_url_items',
    ]; ///< Columnas que se deberán migrar

    /**
     * Método principal del comando
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-01-27
     */
    public function main()
    {
        $rc = 0;
        $this->db = &\sowerphp\core\Model_Datasource_Database::get();
        $this->db->beginTransaction();
        if ($this->crearTabla()) {
            if ($this->migrarDatos()) {
                if ($this->limpiarTabla()) {
                    $this->db->commit();
                } else {
                    $rc = 1;
                    $this->db->rollback();
                    $this->out('<error>No fue posible limpiar la tabla de contribuyentes</error>');
                }
            } else {
                $rc = 2;
                $this->db->rollback();
                $this->out('<error>No fue posible migrar todos los datos de los contribuyentes</error>');
            }
        } else {
            $rc = 3;
            $this->db->rollback();
            $this->out('<error>No fue posible crear la tabla de configuraciones de contribuyentes</error>');
        }
        $this->showStats();
        return $rc;
    }

    private function crearTabla()
    {
        try {
            $this->db->query('DROP TABLE IF EXISTS contribuyente_config CASCADE');
            $this->db->query('
                CREATE TABLE contribuyente_config (
                    contribuyente INTEGER NOT NULL,
                    configuracion VARCHAR(32) NOT NULL,
                    variable VARCHAR(64) NOT NULL,
                    valor TEXT,
                    json BOOLEAN NOT NULL DEFAULT false,
                    CONSTRAINT contribuyente_config_pkey PRIMARY KEY (contribuyente, configuracion, variable),
                    CONSTRAINT contribuyente_config_contribuyente_fk FOREIGN KEY (contribuyente)
                        REFERENCES contribuyente (rut) MATCH FULL ON UPDATE CASCADE ON DELETE CASCADE
                )
            ');
            return true;
        } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
            return false;
        }
    }

    private function migrarDatos()
    {
        try {
            $contribuyentes = $this->db->getTableGenerator('
                SELECT rut, '.implode(', ', array_keys($this->columnas)).'
                FROM contribuyente
            ');
        } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
            return false;
        }
        $procesados = 0;
        foreach ($contribuyentes as $contribuyente) {
            $procesados++;
            if ($this->verbose) {
                $this->out('Procesando contribuyente número '.num($procesados).': '.$contribuyente['rut']);
            }
            foreach ($this->columnas as $origen => $destino) {
                $value = $contribuyente[$origen];
                $value = ($value===false or $value===0) ? '0' : (string)$value;
                if (isset($value[0])) {
                    $configuracion = substr($destino, 0, strpos($destino, '_'));
                    $variable = substr($destino, strpos($destino, '_')+1);
                    if ($this->verbose>=2) {
                        $this->out('  - Se encontró: '.$origen.' copiando a '.$configuracion.'_'.$variable);
                    }
                    try {
                        $this->db->query('
                            INSERT INTO contribuyente_config (contribuyente, configuracion, variable, valor)
                            VALUES (
                                :contribuyente,
                                :configuracion,
                                :variable,
                                :valor
                            )
                        ', [
                            ':contribuyente' => $contribuyente['rut'],
                            ':configuracion' => $configuracion,
                            ':variable' => $variable,
                            ':valor' => $value,
                        ]);
                    } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    private function limpiarTabla()
    {
        try {
            $this->db->query('
                ALTER TABLE contribuyente
                    DROP web,
                    DROP sucursal_sii,
                    DROP resolucion_fecha,
                    DROP resolucion_numero,
                    DROP certificacion,
                    DROP certificacion_resolucion,
                    DROP sii_smtp,
                    DROP sii_imap,
                    DROP sii_user,
                    DROP sii_pass,
                    DROP intercambio_smtp,
                    DROP intercambio_imap,
                    DROP intercambio_user,
                    DROP intercambio_pass,
                    DROP api_token,
                    DROP api_items
                ;
            ');
            return true;
        } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
            return false;
        }
    }

}
