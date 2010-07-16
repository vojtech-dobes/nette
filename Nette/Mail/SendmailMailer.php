<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Mail;

use Nette;



/**
 * Sends emails via the PHP internal mail() function.
 *
 * @author     David Grudl
 */
class SendmailMailer extends Nette\Object implements IMailer
{

	/**
	 * Sends email.
	 * @param  Message
	 * @return void
	 * @warnings
	 */
	public function send(Message $mail)
	{
		$tmp = clone $mail;
		$tmp->setHeader('Subject', NULL);
		$tmp->setHeader('To', NULL);

		$parts = explode(Message::EOL . Message::EOL, $tmp->generateMessage(), 2);

		try {
			if (!mail(
				str_replace(Message::EOL, PHP_EOL, $mail->getEncodedHeader('To')),
				str_replace(Message::EOL, PHP_EOL, $mail->getEncodedHeader('Subject')),
				str_replace(Message::EOL, PHP_EOL, $parts[1]),
				str_replace(Message::EOL, PHP_EOL, $parts[0])
					)) {
				throw new Nette\InvalidStateException('Unable to send email.');
			}
		} catch (\ErrorException $e) {
			throw new Nette\InvalidStateException($e->getMessage());
	}
	}

}
