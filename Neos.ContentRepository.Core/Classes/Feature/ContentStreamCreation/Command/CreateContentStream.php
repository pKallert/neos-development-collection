<?php

declare(strict_types=1);

namespace Neos\ContentRepository\Core\Feature\ContentStreamCreation\Command;

/*
 * This file is part of the Neos.ContentRepository package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\ContentRepository\Core\CommandHandler\CommandInterface;
use Neos\ContentRepository\Core\SharedModel\Workspace\ContentStreamId;

/**
 * CreateContentStream for creating the FIRST content stream.
 * All other content streams will be FORKED from this FIRST content stream.
 *
 * @internal implementation detail. You must not use this command directly.
 * Direct use may lead to hard to revert senseless state in your content repository.
 * Please use the higher level workspace commands instead.
 */
final readonly class CreateContentStream implements CommandInterface
{
    /**
     * @param ContentStreamId $contentStreamId The id of the content stream to create
     */
    private function __construct(
        public ContentStreamId $contentStreamId,
    ) {
    }

    /**
     * @param ContentStreamId $contentStreamId The id of the content stream to create
     */
    public static function create(ContentStreamId $contentStreamId): self
    {
        return new self($contentStreamId);
    }
}
