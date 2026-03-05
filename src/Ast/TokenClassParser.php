<?php
declare(strict_types=1);

namespace SuperKernel\ComposerResolver\Ast;

use function count;
use function in_array;
use function is_array;
use function token_get_all;
use const T_ABSTRACT;
use const T_CLASS;
use const T_COMMENT;
use const T_DOC_COMMENT;
use const T_DOUBLE_COLON;
use const T_ENUM;
use const T_FINAL;
use const T_INTERFACE;
use const T_NAME_QUALIFIED;
use const T_NAMESPACE;
use const T_NS_SEPARATOR;
use const T_READONLY;
use const T_STRING;
use const T_TRAIT;
use const T_WHITESPACE;

final class TokenClassParser
{
	public static function getFullyQualifiedClassName(string $code): ?string
	{
		$tokens = token_get_all($code);
		$namespace = '';
		$count = count($tokens);
		$gettingNamespace = false;

		for ($i = 0; $i < $count; $i++) {
			$token = $tokens[$i];

			if (is_array($token)) {
				switch ($token[0]) {
					case T_NAMESPACE:
						$gettingNamespace = true;
						$namespace = '';
						break;

					case T_NAME_QUALIFIED:
					case T_STRING:
					case T_NS_SEPARATOR:
						if ($gettingNamespace) {
							$namespace .= $token[1];
						}
						break;

					case T_CLASS:
					case T_INTERFACE:
					case T_TRAIT:
					case T_ENUM:
						if (self::isClassConstant($tokens, $i)) {
							continue 2;
						}

						$gettingNamespace = false;
						$className = self::extractClassName($tokens, $i);
						if ($className) {
							return $namespace ? $namespace . '\\' . $className : $className;
						}
						break;
				}
			} elseif ($token === ';') {
				$gettingNamespace = false;
			}
		}

		return null;
	}

	private static function isClassConstant(array $tokens, int $index): bool
	{
		for ($i = $index - 1; $i >= 0; $i--) {
			$token = $tokens[$i];
			if (is_array($token)) {
				if (in_array($token[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT])) {
					continue;
				}
				return $token[0] === T_DOUBLE_COLON;
			}
			break;
		}
		return false;
	}

	private static function extractClassName(array $tokens, int $start): ?string
	{
		$count = count($tokens);
		for ($i = $start + 1; $i < $count; $i++) {
			$token = $tokens[$i];

			if (is_array($token)) {
				$id = $token[0];
				if (in_array($id, [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT, T_FINAL, T_ABSTRACT, T_READONLY])) {
					continue;
				}
				if ($id === T_STRING) {
					return $token[1];
				}
			}

			if ($token === '{' || $token === '(' || $token === ';') {
				break;
			}
		}
		return null;
	}
}