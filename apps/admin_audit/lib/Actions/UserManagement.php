<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2016 Lukas Reschke <lukas@statuscode.ch>
 *
 * @author Arthur Schiwon <blizzz@arthur-schiwon.de>
 * @author Bjoern Schiessle <bjoern@schiessle.org>
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 * @author Joas Schilling <coding@schilljs.com>
 * @author John Molakvoæ <skjnldsv@protonmail.com>
 * @author Lukas Reschke <lukas@statuscode.ch>
 * @author Roeland Jago Douma <roeland@famdouma.nl>
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
namespace OCA\AdminAudit\Actions;

use OCP\IUser;
use OCP\EventDispatcher\IEventListener;
use OCP\EventDispatcher\Event;
use OCP\User\Events\UserDeletedEvent;
use OCP\User\Events\UserCreatedEvent;
use OCP\User\Events\UserChangedEvent;
use OCP\User\Events\PasswordUpdatedEvent;

/**
 * Class UserManagement logs all user management related actions.
 *
 * @package OCA\AdminAudit\Actions
 */
class UserManagement extends Action implements IEventListener {
	public function handle(Event $event): void {
		if ($event instanceof UserDeletedEvent) {
			$this->delete($event);
		}

		if ($event instanceof UserCreatedEvent) {
			$this->create($event);
		}

		if ($event instanceof UserChangedEvent) {
			$this->change($event);
		}

		if ($event instanceof PasswordUpdatedEvent) {
			$this->setPassword($event->getUser());
		}
	}

	/**
	 * Log creation of users
	 *
	 * @param UserCreatedEvent $event
	 */
	public function create(UserCreatedEvent $event): void {
		$this->log(
			'User created: "%s"',
			[
				'uid' => $event->getUser()->getUID(),
			],
			['uid']
		);
	}

	/**
	 * Log assignments of users (typically user backends)
	 *
	 * @param string $uid
	 */
	public function assign(string $uid): void {
		$this->log(
		'UserID assigned: "%s"',
			[ 'uid' => $uid ],
			[ 'uid' ]
		);
	}

	/**
	 * Log deletion of users
	 *
	 * @param BeforeUserDeletedEvent $event
	 */
	public function delete(UserDeletedEvent $event): void {
		$this->log(
			'User deleted: "%s"',
			[
				'uid' => $event->getUser()->getUID(),
			],
			['uid']
		);
	}

	/**
	 * Log unassignments of users (typically user backends, no data removed)
	 *
	 * @param string $uid
	 */
	public function unassign(string $uid): void {
		$this->log(
			'UserID unassigned: "%s"',
			[ 'uid' => $uid ],
			[ 'uid' ]
		);
	}

	/**
	 * Log enabling of users
	 *
	 * @param UserChangedEvent $event
	 */
	public function change(UserChangedEvent $event): void {
		switch ($event->getFeature()) {
			case 'enabled':
				$this->log(
					$event->getValue() === true
						? 'User enabled: "%s"'
						: 'User disabled: "%s"',
					['user' => $event->getUser()->getUID()],
					[
						'user',
					]
				);
				break;
			case 'eMailAddress':
				$this->log(
					'Email address changed for user %s',
					['user' => $event->getUser()->getUID()],
					[
						'user',
					]
				);
				break;
		}
	}

	/**
	 * Logs changing of the user scope
	 *
	 * @param IUser $user
	 */
	public function setPassword(IUser $user): void {
		if ($user->getBackendClassName() === 'Database') {
			$this->log(
				'Password of user "%s" has been changed',
				[
					'user' => $user->getUID(),
				],
				[
					'user',
				]
			);
		}
	}
}
