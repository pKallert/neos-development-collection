<?php

/*
 * This file is part of the Neos.Neos package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

declare(strict_types=1);

namespace Neos\Neos\Fusion\Helper;

use Neos\ContentRepository\Core\NodeType\NodeTypeNames;
use Neos\ContentRepository\Core\Projection\ContentGraph\Node;
use Neos\ContentRepository\Core\Projection\ContentGraph\Nodes;
use Neos\ContentRepository\Core\Projection\Workspace\Workspace;
use Neos\ContentRepository\Core\SharedModel\Node\NodeAggregateId;
use Neos\ContentRepository\Core\SharedModel\Workspace\ContentStreamId;
use Neos\ContentRepositoryRegistry\ContentRepositoryRegistry;
use Neos\Eel\ProtectedContextAwareInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Neos\Fusion\Cache\NodeCacheEntryIdentifier;
use Neos\Neos\Fusion\Cache\CacheTag;
use Neos\Neos\Fusion\Cache\CacheTagSet;
use Neos\Neos\Fusion\Cache\CacheTagWorkspaceName;

/**
 * Caching helper to make cache tag generation easier.
 */
class CachingHelper implements ProtectedContextAwareInterface
{
    /**
     * @Flow\Inject
     * @var ContentRepositoryRegistry
     */
    protected $contentRepositoryRegistry;

    /**
     * @var array<string, ContentStreamId>
     */
    private array $workspaceNameToContentStreamIdMapping = [];

    /**
     * Generate a `@cache` entry tag for a single node, array of nodes or a FlowQuery result
     * A cache entry with this tag will be flushed whenever one of the
     * given nodes (for any variant) is updated.
     *
     * @param iterable<Node>|Node $nodes (A single Node or array or \Traversable of Nodes)
     * @return array<int,string>,
     */
    public function nodeTag(iterable|Node $nodes): array
    {
        if (!is_iterable($nodes)) {
            $nodes = [$nodes];
        } else {
            $nodes = iterator_to_array($nodes);
        }

        return array_merge(
            CacheTagSet::forNodeAggregatesFromNodes(Nodes::fromArray($nodes))->toStringArray(),
            CacheTagSet::forNodeAggregatesFromNodesWithoutWorkspace(Nodes::fromArray($nodes))->toStringArray(),
        );
    }

    /**
     * Generate a `@cache` entry identifier for a given node:
     *
     *     entryIdentifier {
     *       documentNode = ${Neos.Caching.entryIdentifierForNode(documentNode)}
     *     }
     *
     */
    public function entryIdentifierForNode(Node $node): NodeCacheEntryIdentifier
    {
        // Todo adjust content caching to work with workspaces as entry identifier than the content stream id
        $currentContentStreamId = $this->workspaceNameToContentStreamIdMapping[$node->contentRepositoryId->value . '@' . $node->workspaceName->value]
            ??= $this->contentRepositoryRegistry->get($node->contentRepositoryId)->getContentGraph($node->workspaceName)->getContentStreamId();

        return NodeCacheEntryIdentifier::fromNode($node, $currentContentStreamId);
    }

    /**
     * Generate a `@cache` entry tag for a single node identifier.
     *
     * @param string $identifier
     * @param Node $contextNode
     * @return string
     */
    public function nodeTagForIdentifier(string $identifier, Node $contextNode): string
    {
        return CacheTag::forNodeAggregate(
            $contextNode->contentRepositoryId,
            $contextNode->workspaceName,
            NodeAggregateId::fromString($identifier)
        )->value;
    }

    /**
     * Generate an `@cache` entry tag for a node type
     * A cache entry with this tag will be flushed whenever a node
     * (for any variant) that is of the given node type name(s)
     * (including inheritance) is updated.
     *
     * @param iterable<string>|string $nodeTypes
     * @return array<int,string>
     */
    public function nodeTypeTag(string|iterable $nodeTypes, Node $contextNode): array
    {
        if (!is_iterable($nodeTypes)) {
            $nodeTypes = [$nodeTypes];
        } else {
            $nodeTypes = iterator_to_array($nodeTypes);
        }

        return array_merge(
            CacheTagSet::forNodeTypeNames(
                $contextNode->contentRepositoryId,
                $contextNode->workspaceName,
                NodeTypeNames::fromStringArray($nodeTypes)
            )->toStringArray(),
            CacheTagSet::forNodeTypeNames(
                $contextNode->contentRepositoryId,
                CacheTagWorkspaceName::ANY,
                NodeTypeNames::fromStringArray($nodeTypes)
            )->toStringArray(),
        );
    }

    /**
     * Generate a `@cache` entry tag for descendants of a node, an array of nodes or a FlowQuery result
     * A cache entry with this tag will be flushed whenever a node
     * (for any variant) that is a descendant (child on any level) of one of
     * the given nodes is updated.
     *
     * @param iterable<Node>|Node $nodes (A single Node or array or \Traversable of Nodes)
     * @return array<int,string>
     */
    public function descendantOfTag(iterable|Node $nodes): array
    {
        if (!is_iterable($nodes)) {
            $nodes = [$nodes];
        } else {
            $nodes = iterator_to_array($nodes);
        }

        return array_merge(
            CacheTagSet::forDescendantOfNodesFromNodes(Nodes::fromArray($nodes))->toStringArray(),
            CacheTagSet::forDescendantOfNodesFromNodesWithoutWorkspace(Nodes::fromArray($nodes))->toStringArray(),
        );
    }

    /**
     * @param Node|null $node
     * @return array<string,Workspace>
     */
    public function getWorkspaceChain(?Node $node): array
    {
        if ($node === null) {
            return [];
        }

        $contentRepository = $this->contentRepositoryRegistry->get(
            $node->contentRepositoryId
        );

        $currentWorkspace = $contentRepository->getWorkspaceFinder()->findOneByName(
            $node->workspaceName
        );
        $workspaceChain = [];
        // TODO: Maybe write CTE here
        while ($currentWorkspace !== null) {
            $workspaceChain[$currentWorkspace->workspaceName->value] = $currentWorkspace;
            $currentWorkspace = $currentWorkspace->baseWorkspaceName
                ? $contentRepository->getWorkspaceFinder()->findOneByName($currentWorkspace->baseWorkspaceName)
                : null;
        }

        return $workspaceChain;
    }

    /**
     * All methods are considered safe
     *
     * @param string $methodName
     * @return boolean
     */
    public function allowsCallOfMethod($methodName)
    {
        return true;
    }
}
