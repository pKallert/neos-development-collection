<?php

/*
 * This file is part of the Neos.ContentRepository package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

declare(strict_types=1);

namespace Neos\ContentRepository\Core;

use Neos\ContentRepository\Core\Projection\ContentGraph\ContentGraphInterface;
use Neos\ContentRepository\Core\SharedModel\Exception\ContentStreamDoesNotExistYet;
use Neos\ContentRepository\Core\SharedModel\Exception\WorkspaceDoesNotExist;
use Neos\ContentRepository\Core\SharedModel\Workspace\ContentStreamId;
use Neos\ContentRepository\Core\SharedModel\Workspace\WorkspaceName;

/**
 * A finder for ContentGraphInterface bound to contentStream/Workspace
 *
 * @see ContentGraphInterface
 * @api only for read access during write operations and in services
 */
final class ContentGraphFinder implements ContentGraphFinderInterface
{
    /**
     * @var array<string, ContentGraphInterface>
     */
    private array $contenGraphInstances = [];

    public function __construct(
        private readonly ContentGraphFactoryInterface $contentGraphFactory
    ) {
    }

    /**
     * @throws WorkspaceDoesNotExist if there is no workspace with the provided name
     * @throws ContentStreamDoesNotExistYet if the provided workspace does not resolve to an existing content stream
     */
    public function fromWorkspaceName(WorkspaceName $workspaceName): ContentGraphInterface
    {
        if (isset($this->contenGraphInstances[$workspaceName->value])) {
            return $this->contenGraphInstances[$workspaceName->value];
        }

        $this->contenGraphInstances[$workspaceName->value] = $this->contentGraphFactory->buildForWorkspace($workspaceName);
        return $this->contenGraphInstances[$workspaceName->value];
    }

    /**
     * @return ContentGraphInterface[]
     */
    public function getInstances(): array
    {
        return $this->contenGraphInstances;
    }

    public function reset(): void
    {
        $this->contenGraphInstances = [];
    }

    /**
     * @param WorkspaceName $workspaceName
     * @param ContentStreamId $contentStreamId
     * @return ContentGraphInterface
     * @internal
     */
    public function fromWorkspaceNameAndContentStreamId(WorkspaceName $workspaceName, ContentStreamId $contentStreamId): ContentGraphInterface
    {
        if (isset($this->contenGraphInstances[$workspaceName->value]) && $this->contenGraphInstances[$workspaceName->value]->getContentStreamId() === $contentStreamId) {
            return $this->contenGraphInstances[$workspaceName->value];
        }

        return $this->contentGraphFactory->buildForWorkspaceAndContentStream($workspaceName, $contentStreamId);
    }

    /**
     * Stateful (dirty) override of the chosen ContentStreamId for a given workspace, it applies within the given closure.
     * Implementations must ensure that requesting the contentStreamId for this workspace will resolve to the given
     * override ContentStreamId and vice versa resolving the WorkspaceName from this ContentStreamId should result in the
     * given WorkspaceName within the closure.
     *
     * @internal
     */
    public function overrideContentStreamId(WorkspaceName $workspaceName, ContentStreamId $contentStreamId, \Closure $fn): void
    {
        $contentGraph = $this->contentGraphFactory->buildForWorkspaceAndContentStream($workspaceName, $contentStreamId);
        $replacedAdapter = $this->contenGraphInstances[$workspaceName->value] ?? null;
        $this->contenGraphInstances[$workspaceName->value] = $contentGraph;

        try {
            $fn();
        } finally {
            unset($this->contenGraphInstances[$workspaceName->value]);
            if ($replacedAdapter) {
                $this->contenGraphInstances[$workspaceName->value] = $replacedAdapter;
            }
        }
    }
}
