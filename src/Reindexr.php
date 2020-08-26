<?php
declare(strict_types=1);

namespace Basster\Reindexr;

use Basster\Reindexr\Command\ReindexCommand;
use Symfony\Component\Console\Application;

/**
 * Class Reindexr.
 */
final class Reindexr extends Application
{
    public function __construct()
    {
        parent::__construct('Reindexr', '0.0.1');
        $this->add(new ReindexCommand());
    }
}
