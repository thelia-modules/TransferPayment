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

namespace TransferPayment\Listener;

use Thelia\Model\ModuleConfigQuery;
use TransferPayment\Model\TransferPaymentConfig;
use TransferPayment\TransferPayment;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Action\BaseAction;
use Thelia\Core\Event\Order\OrderEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Mailer\MailerFactory;
use Thelia\Core\Template\ParserInterface;
use Thelia\Model\ConfigQuery;
use Thelia\Model\MessageQuery;

/**
 * Class SendEMail
 * @package IciRelais\Listener
 * @author Thelia <info@thelia.net>
 */
class SendEMail extends BaseAction implements EventSubscriberInterface
{

    /**
     * @var MailerFactory
     */
    protected $mailer;
    /**
     * @var ParserInterface
     */
    protected $parser;

    public function __construct(ParserInterface $parser, MailerFactory $mailer)
    {
        $this->parser = $parser;
        $this->mailer = $mailer;
    }

    /*
     * @params OrderEvent $order
     * Checks if order delivery module is icirelais and if order new status is sent, send an email to the customer.
     */
    public function update_status(OrderEvent $event)
    {
        $send_email = ModuleConfigQuery::create()
            ->filterByModuleId(TransferPayment::getModuleId())
            ->filterByName('sendEmail')
            ->findOne();

        $send_email = $send_email->getValue();

        if ($send_email !== '1') {
            return;
        }

        if ($event->getOrder()->getPaymentModuleId() === TransferPayment::getModCode()) {

            if ($event->getOrder()->isPaid()) {
                $contact_email = ConfigQuery::read('store_email');

                if ($contact_email) {
                    $message = MessageQuery::create()
                        ->filterByName('order_confirmation_transferpayment')
                        ->findOne();

                    if (false === $message) {
                        throw new \Exception("Failed to load message 'order_confirmation_transferpayment'.");
                    }

                    $order = $event->getOrder();
                    $customer = $order->getCustomer();

                    $this->parser->assign('order_id', $order->getId());
                    $this->parser->assign('order_ref', $order->getRef());

                    $message
                        ->setLocale($order->getLang()->getLocale());

                    $this->mailer->sendEmailToCustomer($message->getName(), $customer);
                }
            }
        }
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        return array(
            TheliaEvents::ORDER_UPDATE_STATUS => array("update_status", 128)
        );
    }

}
