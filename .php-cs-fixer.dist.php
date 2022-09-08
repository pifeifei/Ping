<?php

/**
 * @see: https://gitee.com/mirrors_FriendsOfPHP/PHP-CS-Fixer/blob/master/doc/
 *
 * @note ruleset: PhpCsFixer\RuleSet\Sets\*Set.php
 * @note rules: PhpCsFixer\Fixer\*\*Fixer.php
 */
declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->ignoreDotFiles(true)
    ->ignoreVCSIgnored(true)
    ->exclude('tests/Fixtures')
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
;

$config = new PhpCsFixer\Config();
$config
    ->setRiskyAllowed(true)
    ->setRules([
        '@PHP74Migration' => true,
        '@PHP74Migration:risky' => true,
        '@PHPUnit84Migration:risky' => true,
        '@PhpCsFixer' => true,
        '@PhpCsFixer:risky' => true,
        // 'general_phpdoc_annotation_remove' => ['annotations' => ['expectedDeprecation']], // one should use PHPUnit built-in method instead
        // 'header_comment' => ['header' => $header],
        'heredoc_indentation' => false,
        // 'modernize_strpos' => true, // needs PHP 8+ or polyfill
        'use_arrow_functions' => false,
        'concat_space' => ['spacing' => 'one'],
        'general_phpdoc_tag_rename' => ['replacements' => ['inheritDocs' => 'inheritDoc'], 'fix_annotation' => true],
        'is_null' => false,
        'phpdoc_align' => ['align' => 'left'],
        'phpdoc_order' => true,
        // 'phpdoc_summary' => true, // 中文注释使用中文句号
        'phpdoc_to_comment' => ['ignored_tags' => ['todo', 'var']],
        'php_unit_test_case_static_method_calls' => ['call_type' => 'this'],
    ])
    ->setFinder($finder)
;

return $config;
