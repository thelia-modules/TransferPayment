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
 * @package TransferPayment\Listener
 * @author Thelia <info@thelia.net>
 */
class SendEMail extends BaseAction implements EventSubscriberInterface
{
    /** @var MailerFactory */
    protected $mailer;

    /** @var ParserInterface */
    protected $parser;

    /**
     * SendEMail constructor.
     * @param ParserInterface $parser
     * @param MailerFactory $mailer
     */
    public function __construct(ParserInterface $parser,MailerFactory $mailer)
    {
        $this->parser = $parser;
        $this->mailer = $mailer;
    }

    /**
     * @return MailerFactory
     */
    public function getMailer()
    {
        return $this->mailer;
    }

    /**
     * @param OrderEvent $event
     * @throws \Exception
     */
    public function update_status(OrderEvent $event)
    {
        if ($event->getOrder()->getPaymentModuleId() === TransferPayment::getModuleId()) {

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

                    $message->setLocale($order->getLang()->getLocale());

                    $instance = \Swift_Message::newInstance()
                        ->addTo($customer->getEmail(), $customer->getFirstname()." ".$customer->getLastname())
                        ->addFrom($contact_email, ConfigQuery::read('store_name'))
                    ;

                    // Build subject and body
                    $message->buildMessage($this->parser, $instance);

                    $this->getMailer()->send($instance);
                }
            }
        }
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            TheliaEvents::ORDER_UPDATE_STATUS => ["update_status", 128]
        ];
    }
}
