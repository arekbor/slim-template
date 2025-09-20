<?php

declare(strict_types=1);

namespace App\Action;

use App\Action\AbstractAction;
use Psr\Http\Message\ResponseInterface;

final class HomeAction extends AbstractAction
{
    public function handleAction(): ResponseInterface
    {
        return $this->render("home/index.html.twig");
    }
}
