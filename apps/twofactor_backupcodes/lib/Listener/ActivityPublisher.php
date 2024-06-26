<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2016 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */
namespace OCA\TwoFactorBackupCodes\Listener;

use BadMethodCallException;
use OCA\TwoFactorBackupCodes\Event\CodesGenerated;
use OCP\Activity\IManager;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use Psr\Log\LoggerInterface;

/** @template-implements IEventListener<CodesGenerated> */
class ActivityPublisher implements IEventListener {
	public function __construct(
		private IManager $activityManager,
		private LoggerInterface $logger,
	) {
	}

	/**
	 * Push an event to the user's activity stream
	 */
	public function handle(Event $event): void {
		if ($event instanceof CodesGenerated) {
			$activity = $this->activityManager->generateEvent();
			$activity->setApp('twofactor_backupcodes')
				->setType('security')
				->setAuthor($event->getUser()->getUID())
				->setAffectedUser($event->getUser()->getUID())
				->setSubject('codes_generated');
			try {
				$this->activityManager->publish($activity);
			} catch (BadMethodCallException $e) {
				$this->logger->error('Could not publish backup code creation activity', ['exception' => $e]);
			}
		}
	}
}
