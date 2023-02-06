<?php
/*
 * Copyright (c) Jan Sohn
 * All rights reserved.
 * Only people with the explicit permission from Jan Sohn are allowed to modify, share or distribute this code.
 *
 * You are NOT allowed to do any kind of modification to this code.
 * You are NOT allowed to share this code with others without the explicit permission from Jan Sohn.
 * You are NOT allowed to run this code on your server without the explicit permission from Jan Sohn.
 */

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
 */
declare(strict_types=1);
require_once __DIR__ . '/../vendor/autoload.php';
function generateRegistryAnnotations(string $path): void{
	if (!file_exists($path)) return;
	if (is_dir($path)) {
		foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::CURRENT_AS_PATHNAME)) as $file) {
			if (!str_ends_with($file, ".php")) continue;
			processFile($file);
		}
	} else {
		processFile($path);
	}
}
function generateMethodAnnotations(string $namespaceName, array $members): string{
	$selfName = basename(__FILE__);
	$lines = [ "/**" ];
	$lines[] = " * This doc-block is generated automatically, do not modify it manually.";
	$lines[] = " * This must be regenerated whenever registry members are added, removed or changed.";
	$lines[] = " * @see building/$selfName";
	$lines[] = " * @generate-registry-docblock";
	$lines[] = " *";
	static $lineTmpl = " * @method static %2\$s %s()";
	$memberLines = [];
	foreach ($members as $name => $member) {
		$reflect = new \ReflectionClass($member);
		while ($reflect !== false && $reflect->isAnonymous()) {
			$reflect = $reflect->getParentClass();
		}
		if ($reflect === false) {
			$typehint = "object";
		}
		else if ($reflect->getNamespaceName() === $namespaceName) {
			$typehint = $reflect->getShortName();
		}
		else {
			$typehint = '\\' . $reflect->getName();
		}
		$accessor = mb_strtoupper($name);
		$memberLines[$accessor] = sprintf($lineTmpl, $accessor, $typehint);
	}
	ksort($memberLines, SORT_STRING);
	foreach ($memberLines as $line) {
		$lines[] = $line;
	}
	$lines[] = " */";
	return implode("\n", $lines);
}
function processFile(string $file): void{
	$contents = file_get_contents($file);
	if ($contents === false) throw new \RuntimeException("Failed to get contents of $file");
	if (preg_match("/(*ANYCRLF)^namespace (.+);$/m", $contents, $matches) !== 1 || preg_match('/(*ANYCRLF)^((final|abstract)\s+)?class /m', $contents) !== 1) {
		return;
	}
	$shortClassName = basename($file, ".php");
	$className = $matches[1] . "\\" . $shortClassName;
	if (!class_exists($className)) {
		return;
	}
	$reflect = new \ReflectionClass($className);
	$docComment = $reflect->getDocComment();
	if ($docComment === false || preg_match("/(*ANYCRLF)^\s*\*\s*@generate-registry-docblock$/m", $docComment) !== 1) {
		return;
	}
	$replacement = generateMethodAnnotations($matches[1], $className::getAll());
	$newContents = str_replace($docComment, $replacement, $contents);
	if ($newContents !== $contents) file_put_contents($file, $newContents);
}