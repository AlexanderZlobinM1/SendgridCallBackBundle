<?php

declare(strict_types=1);

namespace MauticPlugin\SendgridCallbackBundle\Model;

use Doctrine\ORM\EntityManagerInterface;
use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Mautic\EmailBundle\Model\TransportCallback;
use Mautic\EmailBundle\MonitoredEmail\Search\ContactFinder;
use Mautic\LeadBundle\Entity\DoNotContact;

/** Serialize feedback per contact and use persisted DNC state, not a stale ORM collection. */
final class DncFeedback
{
    public function __construct(
        private TransportCallback $callback,
        private EntityManagerInterface $em,
        private ContactFinder $finder,
        private CoreParametersHelper $parameters,
    ) {
    }

    public function addFailureByAddress(string $address, string $comments, int $reason, ?int $emailId): void
    {
        $ids = [];
        foreach ($this->finder->findByAddress($address)->getContacts() as $contact) {
            $ids[] = $contact->getId();
        }
        sort($ids, SORT_NUMERIC);
        $db = $this->em->getConnection();
        $prefix = (string) $this->parameters->get('db_table_prefix');
        $leads = $db->quoteIdentifier($prefix.'leads');
        $dnc = $db->quoteIdentifier($prefix.'lead_donotcontact');
        $ownsTransaction = !$db->isTransactionActive();
        if ($ownsTransaction) {
            $db->beginTransaction();
        }
        try {
            foreach (array_unique($ids) as $id) {
                // MySQL/MariaDB are the supported Mautic runtime databases.
                if (false === $db->fetchOne('SELECT id FROM '.$leads.' WHERE id=? FOR UPDATE', [$id])) {
                    continue;
                }
                $existing = $db->fetchFirstColumn('SELECT reason FROM '.$dnc.' WHERE lead_id=? AND channel=?', [$id, 'email']);
                $existing = array_map('intval', $existing);
                if (in_array(DoNotContact::UNSUBSCRIBED, $existing, true) || in_array($reason, $existing, true)) {
                    continue;
                }
                // The core update path expects a string channel, not ['email' => id].
                // Preserve the original attribution when upgrading an existing DNC reason.
                $this->callback->addFailureByContactId($id, $comments, $reason, [] === $existing ? $emailId : null);
            }
            if ($ownsTransaction) {
                $db->commit();
            }
        } catch (\Throwable $e) {
            if ($ownsTransaction && $db->isTransactionActive()) {
                $db->rollBack();
            }
            throw $e;
        }
    }
}
