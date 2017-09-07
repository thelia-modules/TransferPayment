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

use TransferPayment\Model\Base\TransferPaymentConfig as BaseTransferPaymentConfig;

/**
 * Class TransferPaymentConfig
 * @package TransferPayment\Model
 * @author Thelia <info@thelia.net>
 */
class TransferPaymentConfig extends BaseTransferPaymentConfig implements ConfigInterface
{
    protected $companyName;
    protected $iban;
    protected $bic;

    /**
     * @return array|mixed ObjectCollection
     */
    protected function getDbValues($keysflag=true)
    {
        $pks = $this->getThisVars();
        if ($keysflag) {
            $pks=array_keys($pks);
        }
        $query = TransferPaymentConfigQuery::create()
            ->findPks($pks);

        return $query;
    }

    /**
     * @return array
     */
    public static function read()
    {
        $pks = self::getSelfVars();

        return $pks;
    }

    /**
     * @return array
     */
    public static function getSelfVars()
    {
        $obj = new TransferPaymentConfig();
        $obj->pushValues();
        $this_class_vars = get_object_vars($obj);
        $base_class_vars = get_class_vars("\\TransferPayment\\Model\\Base\\TransferPaymentConfig");
        $pks = array_diff_key($this_class_vars, $base_class_vars);

        return $pks;
    }

    public function write()
    {
        $dbvals = $this->getDbValues();
        $isnew=array();
        foreach ($dbvals as $var) {
            /** @var TransferPaymentConfig $var */
            $isnew[$var->getName()] = true;
        }
        $this->pushValues();
        $vars=$this->getThisVars();
        foreach ($vars as $key=>$value) {
            $tmp = new TransferPaymentConfig();
            $tmp->setNew(!isset($isnew[$key]));
            $tmp->setName($key);
            $tmp->setValue($value);
            $tmp->save();
        }
    }

    /**
     * @return array
     */
    protected function getThisVars()
    {
        $this_class_vars = get_object_vars($this);
        $base_class_vars = get_class_vars("\\TransferPayment\\Model\\Base\\TransferPaymentConfig");
        $pks = array_diff_key($this_class_vars, $base_class_vars);

        return $pks;
    }

    public function pushValues()
    {
        $query = $this->getDbValues();
        foreach ($query as $var) {
            /** @var TransferPaymentConfig $var */
            $name = $var->getName();
            if ($this->$name === null) {
                $this->$name = $var->getValue();
            }
        }
    }

    /**
     * @param string $iban
     * @return $this
     */
    public function setIban($iban)
    {
        $this->iban = $iban;

        return $this;
    }

    /**
     * @return string
     */
    public function getIban()
    {
        return $this->iban;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setCompanyName($name)
    {
        $this->companyName = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $bic
     * @return $this
     */
    public function setBic($bic)
    {
        $this->bic = $bic;

        return $this;
    }

    /**
     * @return string
     */
    public function getBic()
    {
        return $this->bic;
    }
}
