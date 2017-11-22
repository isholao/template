<?php

namespace Isholao\Template;

/**
 *
 * @author Ishola O <ishola.tolu@outlook.com>
 */
interface ViewInterface
{

    public function setDirectories(array $dirs): ViewInterface;

    public function setDirectory(string $dir): ViewInterface;

    public function setData(string $key, $value): ViewInterface;

    public function populate(array $data): ViewInterface;

    public function setView(string $file): ViewInterface;

    public function render(): string;

    function getContent(): string;

    function hasParent(): bool;

    function getParent(): string;

    function setParent(string $parent): ViewInterface;

    function getBlocks(): array;

    public function hasBlock(string $name): bool;

    public function getBlock(string $name): string;

    public function beginBlock(string $name): ViewInterface;

    public function endBlock(): void;

    public function partial(string $file, string $dir = NULL, array $data = []): string;
}
