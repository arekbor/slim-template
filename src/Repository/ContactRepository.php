<?php

declare(strict_types=1);

namespace App\Repository;

final class ContactRepository extends AbstractDatabase
{
    public function getContactById(int $id): ?array
    {
        $contact = $this->sql("
            SELECT * FROM contacts WHERE id = (:id)
        ", [
            'id' => $id
        ]);

        return $contact->fetchOne();
    }

    public function createContact(array $contact): bool
    {
        $this->sql("
            INSERT INTO contacts (firstname, lastname, email, userId) 
            VALUES 
            ((:firstname), (:lastname), (:email), (:userId))
        ", $contact);

        return $this->isAffected();
    }

    public function updateContact(array $contact): bool
    {
        $this->sql("
            UPDATE contacts
            SET firstname = :firstname,
                lastname  = :lastname,
                email     = :email
            WHERE id = :id
        ", $contact);

        return $this->isAffected();
    }

    public function list(): array
    {
        $contacts = $this->sql("
            SELECT c.id, firstname, lastname, c.email, u.username as creator FROM contacts AS c
            INNER JOIN users AS u on c.userId = u.id
        ");

        return $contacts->fetchAll();
    }
}
