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

namespace website\Dte;

/**
 * Comando para actualizar la bandeja de intercambio de los contribuyentes
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2016-07-01
 */
class Shell_Command_DteEmitidos_Actualizar extends \Shell_App
{

    public function main($grupo = null, $certificacion = 0)
    {
        $this->db = \sowerphp\core\Model_Datasource_Database::get();
        $contribuyentes = $this->getContribuyentes($grupo, $certificacion);
        foreach ($contribuyentes as $rut) {
            $this->actualizarDocumentosEmitidos($rut);
        }
        $this->showStats();
        return 0;
    }

    private function actualizarDocumentosEmitidos($rut)
    {
        $Contribuyente = new Model_Contribuyente($rut);
        if ($this->verbose) {
            $this->out('Buscando documentos del contribuyente '.$Contribuyente->razon_social);
        }
        // actualizar estado de DTE enviados
        $sin_estado = $Contribuyente->getDteEmitidosSinEstado();
        foreach ($sin_estado as $d) {
            if ($this->verbose) {
                $this->out('  Actualizando estado T'.$d['dte'].'F'.$d['folio'].': ', 0);
            }
            $DteEmitido = new Model_DteEmitido($Contribuyente->rut, $d['dte'], $d['folio'], (int)$Contribuyente->config_ambiente_en_certificacion);
            try {
                $DteEmitido->actualizarEstado();
                if ($DteEmitido->getEstado()=='R') {
                    $msg = $DteEmitido->revision_estado."\n\n".$DteEmitido->revision_detalle;
                    print_r($Contribuyente->notificar('T'.$DteEmitido->dte.'F'.$DteEmitido->folio.' RECHAZADO!', $msg));
                }
                if ($this->verbose) {
                    $this->out($DteEmitido->revision_estado);
                }
            } catch (\Exception $e) {
                if ($this->verbose) {
                    $this->out($e->getMessage());
                }
            }
        }
        // enviar lo generado sin track id
        $sin_enviar = $Contribuyente->getDteEmitidosSinEnviar();
        foreach ($sin_enviar as $d) {
            if ($this->verbose) {
                $this->out('  Enviando al SII T'.$d['dte'].'F'.$d['folio'].': ', 0);
            }
            $DteEmitido = new Model_DteEmitido($Contribuyente->rut, $d['dte'], $d['folio'], (int)$Contribuyente->config_ambiente_en_certificacion);
            try {
                $DteEmitido->enviar();
                if ($this->verbose) {
                    $this->out($DteEmitido->track_id);
                }
            } catch (\Exception $e) {
                if ($this->verbose) {
                    $this->out($e->getMessage());
                }
            }
        }
    }

    private function getContribuyentes($grupo = null, $certificacion = 0)
    {
        if (is_numeric($grupo))
            return [$grupo];
        if ($grupo) {
            return $this->db->getCol('
                SELECT DISTINCT c.rut
                FROM
                    contribuyente AS c
                    JOIN usuario AS u ON c.usuario = u.id
                    JOIN usuario_grupo AS ug ON ug.usuario = u.id
                    JOIN grupo AS g ON ug.grupo = g.id
                    JOIN dte_emitido AS e ON c.rut = e.emisor
                WHERE
                    g.grupo = :grupo
                    AND e.dte NOT IN (39, 41)
                    AND e.certificacion = :certificacion
                    AND (e.track_id IS NULL OR e.revision_estado IS NULL)
            ', [':certificacion'=>(int)$certificacion, ':grupo' => $grupo]);
        } else {
            return $this->db->getCol('
                SELECT DISTINCT c.rut
                FROM
                    contribuyente AS c
                    JOIN dte_emitido AS e ON c.rut = e.emisor
                WHERE
                    c.usuario IS NOT NULL
                    AND e.dte NOT IN (39, 41)
                    AND e.certificacion = :certificacion
                    AND (e.track_id IS NULL OR e.revision_estado IS NULL)
            ', [':certificacion'=>(int)$certificacion]);
        }
    }

}
