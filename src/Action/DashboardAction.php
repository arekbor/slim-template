<?php

declare(strict_types=1);

namespace App\Action;

use App\Action\AbstractAction;
use App\Repository\ContactRepository;
use Psr\Http\Message\ResponseInterface;

final class DashboardAction extends AbstractAction
{
    public function __construct(
        private readonly ContactRepository $contactRepository
    ) {}

    public function handleAction(): ResponseInterface
    {
        $contacts = $this->contactRepository->list();
        return $this->render("dashboard/index.html.twig", [
            'contacts' => $contacts
        ]);
    }
}
