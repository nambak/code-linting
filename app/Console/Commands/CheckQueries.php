<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CheckQueries extends Command
{
    protected $signature = 'check:queries';

    protected $description = 'Check for common query anti-patterns in code';

    public function handle(): int
    {
        $this->info('🔍 Checking for query anti-patterns...');
        $this->newLine();

        $issues = $this->scanForIssues();

        if (empty($issues)) {
            $this->info('✅ No query anti-patterns detected!');

            return 0;
        }

        $this->displayIssues($issues);

        $high = array_filter($issues, fn ($i) => $i['severity'] === 'high');

        return count($high) > 0 ? 1 : 0;
    }

    protected function getPatterns(): array
    {
        return [
            'all_without_limit' => [
                'pattern' => '/::all\(\)/',
                'message' => 'Using Model::all() without pagination - may load entire table',
                'severity' => 'high',
            ],
            'get_without_where' => [
                'pattern' => '/\bget\(\)/',
                'message' => 'Using get() without where clause - consider adding filters',
                'severity' => 'medium',
            ],
            'raw_query' => [
                'pattern' => '/DB::raw\(/',
                'message' => 'Using DB::raw() - verify SQL injection protection',
                'severity' => 'high',
            ],
            'select_star' => [
                'pattern' => '/->select\(\s*[\'\"]\*[\'\"]/',
                'message' => 'Using select(*) - specify needed columns for better performance',
                'severity' => 'low',
            ],
            'first_without_check' => [
                'pattern' => '/->first\(\)(?!.*?->|;)/',
                'message' => 'Using first() without null check - may cause errors',
                'severity' => 'medium',
            ],
        ];
    }

    protected function scanForIssues(): array
    {
        $issues = [];
        $patterns = $this->getPatterns();
        $files = File::allFiles(app_path());

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $content = file_get_contents($file->getPathname());
            $fileIssues = $this->scanFileForPatterns($file, $content, $patterns);
            $issues = array_merge($issues, $fileIssues);
        }

        return $issues;
    }

    protected function scanFileForPatterns($file, string $content, array $patterns): array
    {
        $issues = [];
        $contentWithoutStrings = $this->removeStringLiterals($content);

        foreach ($patterns as $type => $check) {
            if (! preg_match_all($check['pattern'], $contentWithoutStrings, $matches, PREG_OFFSET_CAPTURE)) {
                continue;
            }

            foreach ($matches[0] as $match) {
                $lineNum = substr_count(substr($contentWithoutStrings, 0, $match[1]), "\n") + 1;
                $originalCode = $this->getOriginalCode($content, $match[1], strlen($match[0]));

                $issues[] = [
                    'file' => $file->getRelativePathname(),
                    'line' => $lineNum,
                    'type' => $type,
                    'message' => $check['message'],
                    'severity' => $check['severity'],
                    'code' => trim($originalCode),
                ];
            }
        }

        return $issues;
    }

    protected function removeStringLiterals(string $content): string
    {
        // 작은따옴표 문자열 제거 (이스케이프 처리 고려)
        $content = preg_replace("/'(?:[^'\\\\]|\\\\.)*'/", "''", $content);

        // 큰따옴표 문자열 제거 (이스케이프 처리 고려)
        $content = preg_replace('/"(?:[^"\\\\]|\\\\.)*"/', '""', $content);

        // heredoc/nowdoc 제거
        $content = preg_replace('/<<<["\']?(\w+)["\']?.*?\n\1;/s', '""', $content);

        return $content;
    }

    protected function getOriginalCode(string $content, int $offset, int $length): string
    {
        return substr($content, $offset, $length);
    }

    protected function displayIssues(array $issues): void
    {
        $grouped = $this->groupBySeverity($issues);

        $this->error('⚠️  Query Anti-patterns Found: '.count($issues));
        $this->newLine();

        $this->displayHighSeverity($grouped['high']);
        $this->displayMediumSeverity($grouped['medium']);
        $this->displayLowSeverity($grouped['low']);
    }

    protected function groupBySeverity(array $issues): array
    {
        return [
            'high' => array_filter($issues, fn ($i) => $i['severity'] === 'high'),
            'medium' => array_filter($issues, fn ($i) => $i['severity'] === 'medium'),
            'low' => array_filter($issues, fn ($i) => $i['severity'] === 'low'),
        ];
    }

    protected function displayHighSeverity(array $issues): void
    {
        if (empty($issues)) {
            return;
        }

        $this->error('🔴 HIGH Severity ('.count($issues).')');
        foreach ($issues as $issue) {
            $this->line("  ❌ {$issue['file']}:{$issue['line']}");
            $this->line("     {$issue['message']}");
            $this->line("     Code: {$issue['code']}");
            $this->newLine();
        }
    }

    protected function displayMediumSeverity(array $issues): void
    {
        if (empty($issues)) {
            return;
        }

        $this->warn('🟡 MEDIUM Severity ('.count($issues).')');
        foreach ($issues as $issue) {
            $this->line("  ⚠️  {$issue['file']}:{$issue['line']}");
            $this->line("     {$issue['message']}");
            $this->newLine();
        }
    }

    protected function displayLowSeverity(array $issues): void
    {
        if (empty($issues)) {
            return;
        }

        $this->info('🟢 LOW Severity ('.count($issues).')');
        foreach ($issues as $issue) {
            $this->line("  ℹ️  {$issue['file']}:{$issue['line']}");
            $this->line("     {$issue['message']}");
            $this->newLine();
        }
    }
}
