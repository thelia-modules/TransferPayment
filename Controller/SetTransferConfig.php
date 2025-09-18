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

namespace TransferPayment\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Model\ModuleConfig;
use Thelia\Model\ModuleConfigQuery;
use TransferPayment\Form\ConfigureTransfer;
use TransferPayment\Model\TransferPaymentConfig;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Core\Security\AccessManager;
use Symfony\Component\Routing\Annotation\Route;
use TransferPayment\TransferPayment;

/**
 * Class SetTransferConfig
 * @Route("/admin/module/transferpayment/configure", name="transferpayment")
 * @package TransferPayment\Controller
 * @author Thelia <info@thelia.net>
 */
class SetTransferConfig extends BaseAdminController
{
    /**
     *
     * @Route("", name="_configure")
     */
    public function configure(): RedirectResponse
    {
        if (null !== $response = $this->checkAuth(array(AdminResources::MODULE), array('TransferPayment'), AccessManager::UPDATE)) {
            return $response;
        }
        $form = $this->createForm(ConfigureTransfer::getName());
        $config = new TransferPaymentConfig();
        $errmes = "";
        try {
            $vform= $this->validateForm($form);

            $name = $vform->get('name')->getData();
            $iban = $vform->get('iban')->getData();
            $bic = $vform->get('bic')->getData();
            $sendEmail = $vform->get('sendEmail')->getData();

                $config->setCompanyName($name)
                    ->setIban($iban)
                    ->setBic($bic)
                    ->write();

            ModuleConfigQuery::create()
                ->setConfigValue(TransferPayment::getModuleId(), 'sendEmail', $sendEmail);

        } catch (\Exception $e) {
            $errmes = $e->getMessage();
        }

        return $this->generateRedirectFromRoute(
            'admin.module.configure',
            [],
            [
                'module_code' => 'TransferPayment',
                '_controller' => 'Thelia\\Controller\\Admin\\ModuleController::configureAction',
                'errmes' => $errmes
            ]
        );
    }
}
