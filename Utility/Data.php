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
 * Clase que permite encriptar/desencriptar datos que son almacenados en la base
 * de datos
 *
 * Requiere php5-mcrypt
 *
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2015-09-19
 */
class Utility_Data
{

    /**
     * Método que encripta un texto plano
     * @param plaintext Texto plano a encriptar
     * @param key Índice en la configuración para obtener la clave a usar
     * @return Texto encriptado en base64
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-01-02
     */
    public static function encrypt($plaintext, $key = 'dte.pkey')
    {
        $key = \sowerphp\core\Configure::read($key);
        if (!$key)
            return base64_encode($plaintext);
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $ciphertext = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $plaintext, MCRYPT_MODE_CBC, $iv);
        return base64_encode($iv.$ciphertext);
    }

    /**
     * Método que desencripta un texto encriptado
     * @param $ciphertext_base64 Texto encriptado en base64 a desencriptar
     * @param key Índice en la configuración para obtener la clave a usar
     * @return Texto plano
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-01-02
     */
    public static function decrypt($ciphertext_base64, $key = 'dte.pkey')
    {
        if (empty($ciphertext_base64))
            return $ciphertext_base64;
        $ciphertext_dec = base64_decode($ciphertext_base64);
        $key = \sowerphp\core\Configure::read($key);
        if (!$key)
            return $ciphertext_dec;
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
        $iv_dec = substr($ciphertext_dec, 0, $iv_size);
        $ciphertext_dec = substr($ciphertext_dec, $iv_size);
        $plaintext_dec = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $ciphertext_dec, MCRYPT_MODE_CBC, $iv_dec);
        return $plaintext_dec;
    }

}
