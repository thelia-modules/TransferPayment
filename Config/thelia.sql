SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- transfer_payment_config
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `transfer_payment_config`;

CREATE TABLE `transfer_payment_config`
(
    `name` VARCHAR(255) NOT NULL,
    `value` VARCHAR(255),
    `placement` INTEGER NOT NULL,
    PRIMARY KEY (`name`)
) ENGINE=InnoDB;

INSERT INTO `transfer_payment_config`(`name`, `placement`) VALUES
  ("companyName",1),("iban",2),("bic",3);

-- ---------------------------------------------------------------------
-- Mail templates for transferpayment
-- ---------------------------------------------------------------------
-- First, delete existing entries
SET @var := 0;
SELECT @var := `id` FROM `message` WHERE name="order_confirmation_transferpayment";
DELETE FROM `message` WHERE `id`=@var;
-- Try if ON DELETE constraint isn't set
DELETE FROM `message_i18n` WHERE `id`=@var;

-- Then add new entries
SELECT @max := MAX(`id`) FROM `message`;
SET @max := @max+1;
-- insert message
INSERT INTO `message` (`id`, `name`, `secured`) VALUES
  (@max,
   'order_confirmation_transferpayment',
   '0'
  );
-- and template fr_FR
INSERT INTO `message_i18n` (`id`, `locale`, `title`, `subject`, `text_message`, `html_message`) VALUES
  (@max,
   'fr_FR',
   'order confirmation_transferpayment',
   'Paiement de la commande : {{ order_ref }}',
   'Le paiement de votre commande : {{ order_ref }} a bien été reçu',
   'Le paiement de votre commande : {{ order_ref }} a bien été reçu'
   );
-- and en_US
INSERT INTO `message_i18n` (`id`, `locale`, `title`, `subject`, `text_message`, `html_message`) VALUES
  (@max,
   'en_US',
   'order confirmation_transferpayment',
   'Paiement de la commande : {{ order_ref }}',
   'Le paiement de votre commande : {{ order_ref }} a bien été reçu',
   'Le paiement de votre commande : {{ order_ref }} a bien été reçu'
  );


SET FOREIGN_KEY_CHECKS = 1;
