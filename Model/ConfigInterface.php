<?php
/*************************************************************************************/
/*                                                                                   */
/*      Thelia	                                                                     */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : info@thelia.net                                                      */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      This program is free software; you can redistribute it and/or modify         */
/*      it under the terms of the GNU General Public License as published by         */
/*      the Free Software Foundation; either version 3 of the License                */
/*                                                                                   */
/*      This program is distributed in the hope that it will be useful,              */
/*      but WITHOUT ANY WARRANTY; without even the implied warranty of               */
/*      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                */
/*      GNU General Public License for more details.                                 */
/*                                                                                   */
/*      You should have received a copy of the GNU General Public License            */
/*	    along with this program. If not, see <http://www.gnu.org/licenses/>.         */
/*                                                                                   */
/*************************************************************************************/

namespace TransferPayment\Model;

/**
 * Interface ConfigInterface
 * @package TransferPayment\Model
 * @author Thelia <info@thelia.net>
 */
interface ConfigInterface
{
    // Data access
    public function write();
    public static function read();

    /**
     * @param $name
     * @return string
     */
    public function setName(?string $name);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param $iban
     * @return string
     */
    public function setIban($iban);

    /**
     * @return string
     */
    public function getIban();

    /**
     * @param $swift
     * @return string
     */
    public function setBic($bic);

    /**
     * @return string
     */
    public function getBic();
}
