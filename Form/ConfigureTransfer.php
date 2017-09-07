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

namespace TransferPayment\Form;

use Symfony\Component\Validator\Constraints\Iban;
use Symfony\Component\Validator\Constraints\NotBlank;
use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;
use TransferPayment\Constraints\BIC;
use TransferPayment\Model\TransferPaymentConfig;

/**
 * Class ConfigureTransfer
 * @package LocalPickup\Form
 * @author Thelia <info@thelia.net>
 */
class ConfigureTransfer extends BaseForm
{
    /**
     *
     * in this function you add all the fields you need for your Form.
     * Form this you have to call add method on $this->formBuilder attribute :
     *
     * $this->formBuilder->add("name", "text")
     *   ->add("email", "email", array(
     *           "attr" => array(
     *               "class" => "field"
     *           ),
     *           "label" => "email",
     *           "constraints" => array(
     *               new \Symfony\Component\Validator\Constraints\NotBlank()
     *           )
     *       )
     *   )
     *   ->add('age', 'integer');
     *
     * @return null
     */
    protected function buildForm()
    {
        $config = TransferPaymentConfig::read();

        $this->formBuilder
            ->add("name","text", array(
                "label"=>Translator::getInstance()->trans("company name"),
                "label_attr"=>array(
                    "for"=>"namefield"
                ),
                "constraints"=>array(
                    new NotBlank()
                ),
                "data"=>!empty($config['companyName']) && $config['companyName'] !== null ? $config['companyName']:"",
            ))
            ->add("iban","text", array(
                "label"=>Translator::getInstance()->trans("IBAN"),
                "label_attr"=>array(
                    "for"=>"ibanfield"
                ),
                "constraints"=>array(
                    new NotBlank(),
                    new Iban()
                ),
                "data"=>!empty($config['iban']) && $config['iban'] !== null ? $config['iban']:"",
            ))
            ->add("bic","text", array(
                "label"=>Translator::getInstance()->trans("BIC"),
                "label_attr"=>array(
                    "for"=>"bicfield"
                ),
                "constraints"=>array(
                    new NotBlank(),
                    new BIC()
                ),
                "data"=>!empty($config['bic']) && $config['bic'] !== null ? $config['bic']:"",
            ))
        ;
    }

    /**
     * @return string the name of you form. This name must be unique
     */
    public function getName()
    {
        return "configuretransferpayment";
    }
}
